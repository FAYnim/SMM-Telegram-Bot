<?php
// Handle edit campaign target callback
if($cb_data && strpos($cb_data, '/edit_campaign_target_') === 0) {
    // get campaign id
    $campaign_id = str_replace('/edit_campaign_target_', '', $cb_data);

    // Update position to edit target mode
    $update_result = updateUserPosition($chat_id, 'edit_campaign_target', $campaign_id);

    // Get campaign data from db
    $campaign = db_query("SELECT id, campaign_title, status, completed_count, campaign_balance, target_total, price_per_task "
        ."FROM smm_campaigns "
        ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

    if (!empty($campaign)) {
        $campaign_data = $campaign[0];

        // Check if campaign is active - must be paused to edit
        if ($campaign_data['status'] == 'active') {
            $error_reply = "❌ <b>Campaign Sedang Aktif</b>\n\n" .
                          "Campaign harus di-pause terlebih dahulu sebelum bisa diedit.\n\n" .
                          "📝 <b>" . $campaign_data['campaign_title'] . "</b>\n" .
                          "ID: <code>" . $campaign_data['id'] . "</code>\n" .
                          "Status: ✅ Active\n\n" .
                          "Silakan pause campaign terlebih dahulu.";
            
            $keyboard = $bot->buildInlineKeyboard([
                // [
                //     ['text' => '⏸️ Pause Campaign', 'callback_data' => '/pause_campaign_' . $campaign_id]
                // ],
                [
                    ['text' => '🔙 Kembali', 'callback_data' => '/edit_campaign_detail_' . $campaign_id]
                ]
            ]);
            
            $bot->editMessage($chat_id, $msg_id, $error_reply, 'HTML', $keyboard);
            return;
        }

        if (!$update_result) {
            $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
            return;
        }

        // Show campaign info and ask for new target
        $reply = "🎯 <b>Edit Target Campaign</b>\n\n" .
            "📝 <b>" . $campaign_data['campaign_title'] . "</b>\n" .
            "ID: <code>" . $campaign_data['id'] . "</code>\n" .
            "✅ Selesai: " . number_format($campaign_data['completed_count']) . "/" . number_format($campaign_data['target_total']) . " tugas\n" .
            "💰 Harga/Tugas: " . number_format($campaign_data['price_per_task'], 0, ',', '.') . "\n" .
            "💸 Saldo Campaign: " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n\n" .
            "🎯 <b>Masukkan target total baru:</b>\n\n" .
            "Target saat ini: " . number_format($campaign_data['target_total']) . " tugas\n" .
            "Sudah selesai: " . number_format($campaign_data['completed_count']) . " tugas\n\n" .
            "Contoh: <code>1000</code>\n" .
            "Minimal: " . number_format($campaign_data['completed_count'] + 1) . " (lebih dari yang sudah selesai)\n" .
            "Maksimal: 100.000\n\n";

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '🔙 Kembali', 'callback_data' => '/edit_campaign_detail_' . $campaign_id]
            ]
        ]);

        $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
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
