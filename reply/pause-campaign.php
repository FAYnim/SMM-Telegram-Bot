<?php
// Handle pause campaign callback
if($cb_data && strpos($cb_data, '/pause_campaign_') === 0) {
    // get campaign id
    $campaign_id = str_replace('/pause_campaign_', '', $cb_data);

    // Get campaign data from db
    $campaign = db_query("SELECT id, campaign_title, status, completed_count, campaign_balance, target_total, price_per_task "
        ."FROM smm_campaigns "
        ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

    if (!empty($campaign)) {
        $campaign_data = $campaign[0];

        // Update campaign status to paused
        $update_result = db_update('smm_campaigns', ['status' => 'paused'], ['id' => $campaign_id, 'client_id' => $user_id]);

        if (!$update_result) {
            $bot->sendMessage($chat_id, "❌ Gagal mengpause campaign!");
            return;
        }

        // Update position
/*        $position_result = updateUserPosition($chat_id, 'edit_campaign_detail');

        if (!$position_result) {
            $bot->sendMessage($chat_id, "❌ Something Error!");
            return;
        }*/

        // Get updated campaign data
        $updated_campaign = db_query("SELECT id, campaign_title, status, completed_count, campaign_balance, target_total, price_per_task "
            ."FROM smm_campaigns "
            ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

        if (!empty($updated_campaign)) {
            $campaign_data = $updated_campaign[0];

            // Status icons
            $status_icons = [
                'creating' => '📝',
                'active' => '✅',
                'paused' => '⏸️',
                'completed' => '✅',
                'deleted' => '🗑️'
            ];

            $status_icon = $status_icons[$campaign_data['status']] ?? '❓';

            // Show updated campaign details
            $reply = "✏️ <b>Edit Campaign</b>\n\n" .
                "📝 <b>" . $campaign_data['campaign_title'] . "</b>\n" .
                "ID: <code>" . $campaign_data['id'] . "</code>\n" .
                "✅ Selesai: " . number_format($campaign_data['completed_count']) . "/" . number_format($campaign_data['target_total']) . " tugas\n" .
                "💰 Harga/Tugas: Rp " . number_format($campaign_data['price_per_task'], 0, ',', '.') . "\n" .
                "💸 Saldo Campaign: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n" .
                $status_icon . " Status: " . ucfirst($campaign_data['status']) . "\n\n" .
                "✅ Campaign berhasil di-pause!\n\n" .
                "Pilih yang ingin Anda ubah:";

            // Build keyboard (show Resume option since now paused)
            $keyboard_buttons = [];

            // Edit options
            $keyboard_buttons[] = [
                ['text' => '📝 Edit Judul', 'callback_data' => '/edit_campaign_title_' . $campaign_id],
                ['text' => '🎯 Edit Target', 'callback_data' => '/edit_campaign_target_' . $campaign_id]
            ];

            $keyboard_buttons[] = [
                ['text' => '💰 Tambah Saldo', 'callback_data' => '/add_campaign_balance_' . $campaign_id]
            ];

            // Show Resume option since campaign is now paused
            $keyboard_buttons[] = [
                ['text' => '▶️ Resume Campaign', 'callback_data' => '/resume_campaign_' . $campaign_id]
            ];

            // Back button
            $keyboard_buttons[] = [
                ['text' => '🔙 Kembali', 'callback_data' => '/select_campaign_' . $campaign_id]
            ];

            $keyboard = $bot->buildInlineKeyboard($keyboard_buttons);

            $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
        }
    } else {
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
