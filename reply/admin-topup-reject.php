<?php

// Extract User ID
$parts = explode('_', $submenu);
$user_chat_id = $parts[2];

// Validasi Alasan Penolakan
$reason = trim($message);

if (empty($reason)) {
    $reply = "❌ Masukkan alasan penolakan!";
    $keyboard = [];

    $bot->editMessage($chat_id, $msg_id, $reply);
    return;
}

// Dapatkan User ID
$user = db_read('smm_users', ['chatid' => $user_chat_id]);
if (!$user) {
    $reply = "❌ User tidak ditemukan!";
    $bot->editMessage($chat_id, $msg_id, $reply);
    return;
}

$actual_user_id = $user[0]['id'];

// Update Deposit Status
$deposit_update = [
    'admin_id' => $user_id,
    'admin_notes' => $reason,
    'status' => 'rejected',
    'processed_at' => date('Y-m-d H:i:s')
];
db_update('smm_deposits', $deposit_update, ['user_id' => $actual_user_id, 'status' => 'pending']);

// Update Posisi Admin
$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

// Hapus Message Admin
$bot->deleteMessage($chat_id, $msg_id);

// Notifikasi User
$user_reply = "❌ Top-up Anda telah ditolak.\n\nAlasan: " . $reason . "\n\nSilakan hubungi admin untuk informasi lebih lanjut.";
$bot->sendMessage($user_chat_id, $user_reply);

// Konfirmasi ke Admin
$reply = "✅ Top-up telah ditolak!\n\nAlasan: " . $reason . "\n\nPesan penolakan sudah dikirim ke user!";
$keyboard = [];

$message_result = $bot->sendMessage($chat_id, $reply);

// Update Message ID
if ($message_result && isset($message_result['message_id'])) {
    $new_msg_id = $message_result['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}

?>