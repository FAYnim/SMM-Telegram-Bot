<?php
// Handle confirm delete campaign callback
if($cb_data && strpos($cb_data, '/delete_campaign_confirm_') === 0) {
	file_put_contents('log/delete-campaign.log', "confirm delete campaign included\n", FILE_APPEND);
    $campaign_id = str_replace('/delete_campaign_confirm_', '', $cb_data);

    // Get campaign data for audit log
    $campaign = db_query("SELECT id, campaign_title, campaign_balance, client_id, status, completed_count "
        ."FROM smm_campaigns "
        ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

    if (!empty($campaign)) {
		file_put_contents('log/delete-campaign.log', "campaign found 2\n", FILE_APPEND);
        $campaign_data = $campaign[0];

        // Update position
        $update_result = updateUserPosition($chat_id, 'delete_campaign_confirm');

        if (!$update_result) {
            $bot->sendMessage($chat_id, "❌ Something Error!");
            return;
        }

        // Update campaign status to deleted
        $update_result = db_update('smm_campaigns',
            ['status' => 'deleted'],
            ['id' => $campaign_id, 'client_id' => $user_id]
        );

        if ($update_result) {
			file_put_contents('log/delete-campaign.log', "campaign status updated to deleted\n", FILE_APPEND);

            // Refund remaining balance to user's wallet
            if ($campaign_data['campaign_balance'] > 0) {
                // Get user's wallet
                $wallet = db_read('smm_wallets', ['user_id' => $user_id]);

                if (!empty($wallet)) {
					file_put_contents('log/delete-campaign.log', "balance user found\n", FILE_APPEND);
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

			file_put_contents('log/delete-campaign.log', "create audit log\n", FILE_APPEND);
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
                            "💰 Saldo dikembalikan: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.');

            $keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '🔙 Kembali ke List', 'callback_data' => '/edit_campaign']
                ]
            ]);

            $bot->editMessage($chat_id, $msg_id, $success_reply, 'HTML', $keyboard);
        } else {
			file_put_contents('log/delete-campaign.log', "campaign not deleted\n", FILE_APPEND);
            $error_reply = "❌ Gagal menghapus campaign. Silakan coba lagi.";

            $keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '🔙 Kembali', 'callback_data' => '/select_campaign_' . $campaign_id]
                ]
            ]);

            $bot->editMessage($chat_id, $msg_id, $error_reply, 'HTML', $keyboard);
        }
    } else {
		file_put_contents('log/delete-campaign.log', "campaign not found 2\n", FILE_APPEND);
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
