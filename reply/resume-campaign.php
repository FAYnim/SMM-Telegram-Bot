<?php
require_once __DIR__ . '/../helpers/error-handler.php';

if($cb_data && strpos($cb_data, '/resume_campaign_') === 0) {
    $campaign_id = str_replace('/resume_campaign_', '', $cb_data);

    // Ambil data campaign
    $campaign = db_query("SELECT id, campaign_title, status, completed_count, campaign_balance, target_total, price_per_task "
        ."FROM smm_campaigns "
        ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

    if (empty($campaign)) {
        // Campaign tidak ditemukan
        sendErrorWithBackButton(
            $bot,
            $chat_id,
            $msg_id,
            "❌ <b>Campaign Tidak Ditemukan</b>\n\nCampaign tidak ditemukan atau tidak valid.\n\n<i>Silakan pilih campaign lain dari daftar.</i>",
            '/edit_campaign',
            '🔙 Kembali ke Daftar Campaign'
        );
        return;
    }

    if (!empty($campaign)) {
        $campaign_data = $campaign[0];

        // Cek apakah campaign bisa diresume (saldo cukup minimal 1 task)
        $can_create_tasks = floor($campaign_data['campaign_balance'] / $campaign_data['price_per_task']);

        if ($can_create_tasks <= $campaign_data['completed_count']) {
            // Saldo tidak cukup - tambahkan tombol shortcut untuk tambah saldo
            $error_message = "❌ <b>Saldo Campaign Tidak Mencukupi</b>\n\n" .
                "💰 Saldo Campaign: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n" .
                "💵 Harga per Task: Rp " . number_format($campaign_data['price_per_task'], 0, ',', '.') . "\n\n" .
                "<i>Silakan tambah saldo campaign terlebih dahulu untuk melanjutkan.</i>";
            
            $buttons = [
                [
                    ['text' => '💰 Tambah Saldo', 'callback_data' => '/add_campaign_balance_' . $campaign_id]
                ],
                [
                    ['text' => '🔙 Kembali', 'callback_data' => '/select_campaign_' . $campaign_id]
                ]
            ];
            
            editErrorWithCustomButtons($bot, $chat_id, $msg_id, $error_message, $buttons);
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
            sendErrorWithBackButton(
                $bot,
                $chat_id,
                null,
                "❌ <b>Gagal Resume Campaign</b>\n\nTerjadi kesalahan saat meresume campaign. Silakan coba lagi.",
                '/select_campaign_' . $campaign_id,
                '🔙 Kembali'
            );
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
