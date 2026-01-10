<?php

// Extract User ID from submenu
$parts = explode('_', $submenu);
$user_chat_id = $parts[2];

// Validasi Alasan Penolakan
$reason = trim($message);

if (empty($reason)) {
    $reply = "❌ <b>Alasan Wajib Diisi</b>\nMohon berikan alasan penolakan agar user mengerti mengapa topup ditolak.";
    $bot->sendMessage($chat_id, $reply, 'HTML');
    return;
}

// Cari data user
$user = db_read('smm_users', ['chatid' => $user_chat_id]);
if (!$user) {
    $bot->sendMessage($chat_id, "❌ User tidak ditemukan di database.");
    return;
}

$actual_user_id = $user[0]['id'];

// Update Status di Tabel Deposits
// Mengubah status SEMUA pending deposit user ini menjadi rejected
$deposit_update = [
    'admin_id' => $user_id,
    'admin_notes' => $reason,
    'status' => 'rejected',
    'processed_at' => date('Y-m-d H:i:s')
];
db_update('smm_deposits', $deposit_update, ['user_id' => $actual_user_id, 'status' => 'pending']);

// Reset Posisi Admin
updateUserPosition($chat_id, 'main', '');

// Hapus pesan prompt input admin
$bot->deleteMessage($chat_id, $msg_id);

// --- NOTIFIKASI KE USER ---
$user_reply = "❌ <b>Topup Ditolak</b>\n\n";
$user_reply .= "Mohon maaf, permintaan topup Anda tidak dapat kami proses saat ini.\n\n";
$user_reply .= "📝 <b>Alasan:</b> " . htmlspecialchars($reason) . "\n\n";
$user_reply .= "Silakan perbaiki data bukti pembayaran atau hubungi Admin jika ada kesalahan.";

// Keyboard untuk tutup notifikasi
$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '✖️ Tutup Notifikasi', 'callback_data' => 'close_notif']
        ]
    ]
];

$bot->sendMessageWithKeyboard($user_chat_id, $user_reply, $keyboard, null, 'HTML');

// --- KONFIRMASI KE ADMIN ---
$admin_reply = "✅ <b>Topup Ditolak</b>\n\n";
$admin_reply .= "👤 User ID: <code>$user_chat_id</code>\n";
$admin_reply .= "📝 Alasan: " . htmlspecialchars($reason) . "\n";
$admin_reply .= "📢 Status: User telah dinotifikasi.";

$message_result = $bot->sendMessage($chat_id, $admin_reply, 'HTML');

// Update last msg_id admin
if ($message_result && isset($message_result['result']['message_id'])) {
    $new_msg_id = $message_result['result']['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}

?>