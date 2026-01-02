<?php
// Handle delete campaign callback
if($cb_data && strpos($cb_data, '/delete_campaign_') === 0) {
	file_put_contents('log/delete-campaign.log', "delete capaign file included\n", FILE_APPEND);
	// get campaign id
    $campaign_id = str_replace('/delete_campaign_', '', $cb_data);

    // Get campaign data from db
    $campaign = db_query("SELECT id, campaign_title, status, completed_count, campaign_balance "
        ."FROM smm_campaigns "
        ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

    if (!empty($campaign)) {
		file_put_contents('log/delete-campaign.log', "campaign found 1\n", FILE_APPEND);
        $campaign_data = $campaign[0];

        // Update position
        $update_result = updateUserPosition($chat_id, 'delete_campaign');

        if (!$update_result) {
            $bot->sendMessage($chat_id, "❌ Something Error!");
            return;
        }

        // Show confirmation dialog
        $reply = "🗑️ <b>Hapus Campaign</b>\n\n" .
            "Anda yakin ingin menghapus campaign ini?\n\n" .
            "📝 <b>" . $campaign_data['campaign_title'] . "</b>\n" .
            "ID: <code>" . $campaign_data['id'] . "</code>\n" .
            "✅ Selesai: " . number_format($campaign_data['completed_count']) . " tugas\n" .
            "💰 Saldo: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n\n" .
            "⚠️ <i>Tindakan ini tidak dapat dibatalkan!</i>";

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '🗑️ Ya, Hapus', 'callback_data' => '/delete_campaign_confirm_' . $campaign_id],
                ['text' => '❌ Batal', 'callback_data' => '/select_campaign_' . $campaign_id]
            ]
        ]);

        $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    } else {
		file_put_contents('log/delete-campaign.log', "campaign not found 1\n", FILE_APPEND);
        // Campaign not found
        $error_reply = "❌ Campaign tidak ditemukan atau tidak valid.";

        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $error_reply);

        // Save msg_id and return to campaign list
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

            sleep(3);

            // Rebuild campaign list
            $campaigns = db_query("SELECT id, campaign_title, status "
                ."FROM smm_campaigns "
                ."WHERE client_id = ? AND status NOT IN ('deleted', 'creating') "
                ."ORDER BY created_at DESC LIMIT 0,5", [$user_id]);

            $list_reply = "📋 <b>Kelola Campaign</b>\n\nSilakan pilih campaign yang ingin Anda ubah:";

            if (count($campaigns) > 0) {
                $keyboard_buttons = [];
                foreach ($campaigns as $campaign) {
                    $display_text = "ID: " . $campaign['id'] . " - " . $campaign['campaign_title'];
                    $callback_data = '/select_campaign_' . $campaign['id'];

                    $keyboard_buttons[] = [$display_text, $callback_data];
                }

                // back button
                $keyboard_buttons[] = ['🔙 Kembali', '/edit_campaign'];

                $list_keyboard = [];
                foreach ($keyboard_buttons as $button) {
                    $list_keyboard[] = [
                        ['text' => $button[0], 'callback_data' => $button[1]]
                    ];
                }
                $list_keyboard = $bot->buildInlineKeyboard($list_keyboard);
            } else {
                $list_reply = "⚠️ <b>Tidak ada campaign.</b>\n\nAnda belum membuat campaign apapun.";

                $list_keyboard = $bot->buildInlineKeyboard([
                    [
                        ['text' => '🔙 Kembali', 'callback_data' => '/edit_campaign']
                    ]
                ]);
            }

            $bot->editMessage($chat_id, $new_msg_id, $list_reply, 'HTML', $list_keyboard);
        }
    }
}
?>
