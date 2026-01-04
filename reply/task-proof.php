<?php

/*$update_result = updateUserPosition($chat_id, 'upload_proof', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem.");
    return;
}*/

// Hapus pesan instruksi upload sebelumnya agar chat bersih
$bot->deleteMessage($chat_id, $msg_id);

// Kirim pesan waiting
$waiting_result = $bot->sendMessage($chat_id, "⏳ Sedang mengirim bukti ke Admin...");
$waiting_msg_id = $waiting_result['result']['message_id'];

if (isset($waiting_msg_id)) {
    // Simpan msg_id untuk update nanti
    db_update('smm_users', ['msg_id' => $waiting_msg_id], ['chatid' => $chat_id]);

    // Ambil task ID dari submenu (disimpan saat take task)
    $task_id = $submenu;

    if (!$task_id) {
        $bot->sendMessage($chat_id, "❌ Task tidak ditemukan.");
        return;
    }

    // Ambil detail task untuk notifikasi admin
    $task_detail = db_query("SELECT t.*, c.campaign_title, c.type, c.price_per_task "
        ."FROM smm_tasks t "
        ."JOIN smm_campaigns c ON t.campaign_id = c.id "
        ."WHERE t.id = ? AND t.worker_id = ? AND t.status = 'taken' "
        ."LIMIT 1", [$task_id, $user_id]);

    if (empty($task_detail)) {
        $bot->sendMessage($chat_id, "❌ Task tidak valid atau sudah tidak tersedia.");
        return;
    }

    $task = $task_detail[0];

    // Kirim notifikasi ke Admin
    $admins = db_read("smm_admins");

    if ($admins) {
        if (!isset($file_id)) {
            $bot->sendMessage($chat_id, "❌ Gagal mendeteksi gambar.");
            return;
        }

        foreach ($admins as $admin) {
            $admin_id = $admin["chatid"];

            // Siapkan data user untuk display
            $sender_name = $username ? "@$username" : $first_name;
            $caption_plain = "🔔 TASK BARU!\n"
            	."User: $sender_name (ID: $chat_id)\n"
            	."Task: " . htmlspecialchars($task['campaign_title']) . "\n"
            	."Reward: Rp " . number_format($task['price_per_task'], 0, ',', '.') . "\n"
            	."Waktu: " . date('d M Y, H:i');

            $keyboard_admin = $bot->buildInlineKeyboard([
                [
                    ['text' => '✅ Approve', 'callback_data' => 'admin_approve_task_' . $task_id],
                    ['text' => '❌ Reject', 'callback_data' => 'admin_reject_task_' . $task_id]
                ]
            ]);

            // Kirim foto bukti ke admin
            $bot->sendPhoto($admin_id, $file_id, $caption_plain);
            sleep(1);

            // Kirim menu aksi
            $reply_admin = "👇 <b>Tindakan Admin</b>\nSilakan cek bukti di atas dari User ID: <code>$chat_id</code>\nTask ID: <code>$task_id</code>";
            $bot->sendMessageWithKeyboard($admin_id, $reply_admin, $keyboard_admin);
            sleep(1);
        }
    }

    // Simpan bukti task ke database
    db_create('smm_task_proofs', [
        'task_id' => $task_id,
        'proof_image_path' => $file_id
    ]);

    // Update task status ke pending_review
    db_update('smm_tasks', [
        'status' => 'pending_review',
        'completed_at' => date('Y-m-d H:i:s')
    ], ['id' => $task_id]);

    // Update pesan user jadi Final
    $reply_user = "✅ <b>Bukti Terkirim</b>\n\n";
    $reply_user .= "Terima kasih! Bukti task Anda telah kami terima.\n\n";
    $reply_user .= "📋 <b>Detail Task:</b>\n";
    $reply_user .= "• Campaign: " . htmlspecialchars($task['campaign_title']) . "\n";
    $reply_user .= "• Reward: Rp " . number_format($task['price_per_task'], 0, ',', '.') . "\n\n";
    $reply_user .= "⏳ <b>Status: Menunggu Verifikasi</b>\n";
    $reply_user .= "Admin kami akan mengecek bukti task Anda. Reward akan otomatis ditambahkan jika disetujui (Estimasi 5-10 menit).";

    $keyboard_user = $bot->buildInlineKeyboard([
        [
            ['text' => '📋 Ambil Task Lagi', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali ke Menu Utama', 'callback_data' => '/start']
        ]
    ]);

    $bot->editMessage($chat_id, $waiting_msg_id, $reply_user, 'HTML', $keyboard_user);

} else {
    $bot->sendMessage($chat_id, "❌ Gagal memproses permintaan.");
    return;
}
?>
