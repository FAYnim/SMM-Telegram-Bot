<?php

$update_result = updateUserPosition($chat_id, 'topup_proof', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem.");
    return;
}

// Hapus pesan instruksi upload sebelumnya agar chat bersih
$bot->deleteMessage($chat_id, $msg_id);

// Kirim pesan waiting
$waiting_result = $bot->sendMessage($chat_id, "⏳ Sedang mengirim bukti ke Admin...");
$waiting_msg_id = $waiting_result['result']['message_id'];

if (isset($waiting_msg_id)) {
    // Simpan msg_id untuk update nanti
    db_update('smm_users', ['msg_id' => $waiting_msg_id], ['chatid' => $chat_id]);

    // Simpan data deposit ke database dan dapatkan deposit_id
    $deposit_id = db_create('smm_deposits', [
        'user_id' => $user_id,
        'proof_image_id' => $file_id,
        'amount' => 0,
        'status' => 'pending'
    ]);

    if (!isset($file_id)) {
        $bot->sendMessage($chat_id, "❌ Gagal mendeteksi gambar.");
        return;
    }

    // Siapkan data user untuk display
    $sender_name = $username ? "@$username" : $first_name;
    $caption_plain = "🔔 TOPUP BARU!\nUser: $sender_name (ID: $chat_id)\nWaktu: " . date('d M Y, H:i');

    $keyboard_admin = $bot->buildInlineKeyboard([
        [
            ['text' => '✅ Terima', 'callback_data' => 'admin_approve_topup_' . $deposit_id],
            ['text' => '❌ Tolak', 'callback_data' => 'admin_reject_topup_' . $deposit_id]
        ]
    ]);

    // Kirim notifikasi ke admin dengan permission deposit_verify
    $admin_chat_ids = getAdminChatIdsByPermission('deposit_verify');

    if ($admin_chat_ids) {
        foreach ($admin_chat_ids as $admin_id) {
            $bot->sendPhoto($admin_id, $file_id, $caption_plain);
            
            $reply_admin = "👇 <b>Tindakan Admin</b>\nSilakan cek bukti di atas dari User ID: <code>$chat_id</code>";
            $bot->sendMessageWithKeyboard($admin_id, $reply_admin, $keyboard_admin);
            sleep(1);
        }
    }

    // Update pesan user jadi Final
    $reply_user = "✅ <b>Bukti Terkirim</b>\n\n";
    $reply_user .= "Terima kasih! Bukti pembayaran Anda telah kami terima.\n\n";
    $reply_user .= "⏳ <b>Status: Menunggu Verifikasi</b>\n";
    $reply_user .= "Admin kami akan mengecek bukti transfer Anda. Saldo akan otomatis bertambah jika disetujui (Estimasi 5-10 menit).";

    $keyboard_user = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali ke Menu Utama', 'callback_data' => '/start']
        ]
    ]);

    $bot->editMessage($chat_id, $waiting_msg_id, $reply_user, 'HTML', $keyboard_user);

} else {
    $bot->sendMessage($chat_id, "❌ Gagal memproses permintaan.");
    return;
}
?>
