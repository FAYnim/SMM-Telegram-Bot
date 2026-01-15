<?php
if($cb_data && strpos($cb_data, '/resume_campaign_') === 0) {
    $campaign_id = str_replace('/resume_campaign_', '', $cb_data);

    // Ambil data campaign
    $campaign = db_query("SELECT id, campaign_title, status, completed_count, campaign_balance, target_total, price_per_task "
        ."FROM smm_campaigns "
        ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

    if (empty($campaign)) {
        // Campaign tidak ditemukan
        $error_reply = "❌ Campaign tidak ditemukan atau tidak valid.";

        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $error_reply);

        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

            sleep(3);

            // Bangun ulang daftar campaign
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

                // Tombol kembali
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
        return;
    }

    if (!empty($campaign)) {
        $campaign_data = $campaign[0];

        // Cek apakah campaign bisa diresume (saldo cukup minimal 1 task)
        $can_create_tasks = floor($campaign_data['campaign_balance'] / $campaign_data['price_per_task']);

        if ($can_create_tasks <= $campaign_data['completed_count']) {
            $bot->editMessage($chat_id, $msg_id, "❌ Saldo campaign tidak mencukupi untuk resume.\n\n💰 Saldo: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n💰 Harga/task: Rp " . number_format($campaign_data['price_per_task'], 0, ',', '.') . "\n\nSilakan tambah saldo campaign terlebih dahulu.", 'HTML');
            return;
        }

        // Generate task jika diperlukan sebelum aktivasi
        $existing_tasks_count = db_query("SELECT COUNT(*) as count FROM smm_tasks WHERE campaign_id = ?", [$campaign_id]);
        $existing_tasks_count = $existing_tasks_count[0]['count'];

        $can_create_tasks = floor($campaign_data['campaign_balance'] / $campaign_data['price_per_task']);
        $new_tasks_needed = max(0, $can_create_tasks - $existing_tasks_count);

        $tasks_generated = 0;
        if ($new_tasks_needed > 0) {
            for ($i = 0; $i < $new_tasks_needed; $i++) {
                $task_data = [
                    'campaign_id' => $campaign_id,
                    'status' => 'available'
                ];
                $task_id = db_create('smm_tasks', $task_data);
                if ($task_id) {
                    $tasks_generated++;
                }
            }

            // Update target_total jika ada task baru
            if ($tasks_generated > 0) {
                $new_target_total = max($campaign_data['target_total'], $existing_tasks_count + $tasks_generated);
                db_update('smm_campaigns', ['target_total' => $new_target_total], ['id' => $campaign_id]);
                $campaign_data['target_total'] = $new_target_total;
            }
        }

        // Update status campaign ke active
        $update_result = db_update('smm_campaigns', ['status' => 'active'], ['id' => $campaign_id, 'client_id' => $user_id]);

        if (!$update_result) {
            $bot->sendMessage($chat_id, "❌ Gagal meresume campaign!");
            return;
        }

        // Ambil data campaign yang sudah diupdate
        $updated_campaign = db_query("SELECT id, campaign_title, status, completed_count, campaign_balance, target_total, price_per_task "
            ."FROM smm_campaigns "
            ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

        if (!empty($updated_campaign)) {
            $campaign_data = $updated_campaign[0];

            // Icon status
            $status_icons = [
                'creating' => '📝',
                'active' => '✅',
                'paused' => '⏸️',
                'completed' => '✅',
                'deleted' => '🗑️'
            ];

            $status_icon = $status_icons[$campaign_data['status']] ?? '❓';

            // Tampilkan detail campaign yang sudah diupdate
            $reply = "✏️ <b>Edit Campaign</b>\n\n" .
                "📝 <b>" . $campaign_data['campaign_title'] . "</b>\n" .
                "ID: <code>" . $campaign_data['id'] . "</code>\n" .
                "✅ Selesai: " . number_format($campaign_data['completed_count']) . "/" . number_format($campaign_data['target_total']) . " tugas\n" .
                "💰 Harga/Tugas: Rp " . number_format($campaign_data['price_per_task'], 0, ',', '.') . "\n" .
                "💸 Saldo Campaign: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n" .
                $status_icon . " Status: " . ucfirst($campaign_data['status']) . "\n\n" .
                "✅ Campaign berhasil di-resume!";

            if ($tasks_generated > 0) {
                $reply .= "\n📊 Tasks baru dibuat: " . $tasks_generated;
            }

            $reply .= "\n\nPilih yang ingin Anda ubah:";

            $keyboard_buttons = [];

            // Opsi edit
            $keyboard_buttons[] = [
                ['text' => '📝 Edit Judul', 'callback_data' => '/edit_campaign_title_' . $campaign_id],
                ['text' => '🎯 Edit Target', 'callback_data' => '/edit_campaign_target_' . $campaign_id]
            ];

            $keyboard_buttons[] = [
                ['text' => '💰 Tambah Saldo', 'callback_data' => '/add_campaign_balance_' . $campaign_id]
            ];

            $keyboard_buttons[] = [
                ['text' => '⏸️ Pause Campaign', 'callback_data' => '/pause_campaign_' . $campaign_id]
            ];

            $keyboard_buttons[] = [
                ['text' => '🔙 Kembali', 'callback_data' => '/select_campaign_' . $campaign_id]
            ];

            $keyboard = $bot->buildInlineKeyboard($keyboard_buttons);

            $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
        }
    }
}
?>
