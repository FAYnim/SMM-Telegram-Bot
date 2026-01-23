<?php
// Handle confirm delete campaign callback
if($cb_data && strpos($cb_data, '/delete_campaign_confirm_') === 0) {
    $campaign_id = str_replace('/delete_campaign_confirm_', '', $cb_data);

        // Update position
        $update_result = updateUserPosition($chat_id, 'delete_campaign_confirm');


    // Get campaign data for audit log
    $campaign = db_query("SELECT id, campaign_title, campaign_balance, client_id, status, completed_count "
        ."FROM smm_campaigns "
        ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

    if (!empty($campaign)) {
        $campaign_data = $campaign[0];

        // Check if campaign is active - must be paused to delete
        if ($campaign_data['status'] == 'active') {
            $error_reply = "❌ <b>Campaign Sedang Aktif</b>\n\n" .
                          "Campaign harus di-pause terlebih dahulu sebelum bisa dihapus.\n\n" .
                          "📝 <b>" . $campaign_data['campaign_title'] . "</b>\n" .
                          "ID: <code>" . $campaign_data['id'] . "</code>\n" .
                          "Status: ✅ Active\n\n" .
                          "Silakan pause campaign terlebih dahulu.";
            
            $keyboard = $bot->buildInlineKeyboard([
                // [
                //     ['text' => '⏸️ Pause Campaign', 'callback_data' => '/pause_campaign_' . $campaign_id]
                // ],
                [
                    ['text' => '🔙 Kembali', 'callback_data' => '/select_campaign_' . $campaign_id]
                ]
            ]);
            
            $bot->editMessage($chat_id, $msg_id, $error_reply, 'HTML', $keyboard);
            return;
        }

        if (!$update_result) {
            $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
            return;
        }

        // Update campaign status to deleted
        $update_result = db_update('smm_campaigns',
            ['status' => 'deleted'],
            ['id' => $campaign_id, 'client_id' => $user_id]
        );

        if ($update_result) {

            // Refund remaining balance to user's wallet
            if ($campaign_data['campaign_balance'] > 0) {
                // Get user's wallet
                $wallet = db_read('smm_wallets', ['user_id' => $user_id]);

                if (!empty($wallet)) {
                    $wallet_data = $wallet[0];
                    $new_balance = $wallet_data['balance'] + $campaign_data['campaign_balance'];

                    // Update wallet balance
                    db_update('smm_wallets',
                        ['balance' => $new_balance],
                        ['id' => $wallet_data['id']]
                    );

                    // Create wallet transaction record
                    db_create('smm_wallet_transactions', [
                        'wallet_id' => $wallet_data['id'],
                        'type' => 'adjustment',
                        'amount' => $campaign_data['campaign_balance'],
                        'balance_before' => $wallet_data['balance'],
                        'balance_after' => $new_balance,
                        'description' => 'Refund dari campaign yang dihapus: ' . $campaign_data['campaign_title'],
                        'reference_id' => $campaign_id,
                        'status' => 'approved'
                    ]);
                }
            }

            // Create audit log
            db_create('smm_audit_logs', [
                'admin_id' => $user_id,
                'action' => 'delete_campaign',
                'table_name' => 'smm_campaigns',
                'record_id' => $campaign_id,
                'old_data' => json_encode($campaign_data),
                'new_data' => json_encode(['status' => 'deleted']),
                'description' => 'Campaign dihapus oleh client: ' . $campaign_data['campaign_title']
            ]);

            $success_reply = "✅ <b>Campaign Berhasil Dihapus</b>\n\n" .
                            "📝 " . $campaign_data['campaign_title'] . "\n" .
                            "💰 Saldo dikembalikan: " . number_format($campaign_data['campaign_balance'], 0, ',', '.');

            $keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '🔙 Kembali ke List', 'callback_data' => '/edit_campaign']
                ]
            ]);

            $bot->editMessage($chat_id, $msg_id, $success_reply, 'HTML', $keyboard);
        } else {
            $error_reply = "❌ Gagal menghapus campaign. Silakan coba lagi.";

            $keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '🔙 Kembali', 'callback_data' => '/select_campaign_' . $campaign_id]
                ]
            ]);

            $bot->editMessage($chat_id, $msg_id, $error_reply, 'HTML', $keyboard);
        }
    } else {
        // Campaign not found
        $error_reply = "❌ Campaign tidak ditemukan atau tidak valid.";

        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $error_reply);

        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
        }
    }
}

?>
