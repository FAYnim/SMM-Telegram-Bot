<?php

// Extract Withdraw ID from submenu
$parts = explode('_', $submenu);
$withdraw_id = $parts[2];

// Validasi Alasan Penolakan
$reason = trim($message);

if (empty($reason)) {
    $reply = "❌ <b>Alasan Wajib Diisi</b>\nMohon berikan alasan penolakan agar user mengerti mengapa withdraw ditolak.";
    $bot->sendMessage($chat_id, $reply);
    return;
}

// Cari data withdraw
$withdraw = db_read('smm_withdrawals', ['id' => $withdraw_id]);
if (!$withdraw) {
    $bot->sendMessage($chat_id, "❌ Data withdraw tidak ditemukan di database.");
    return;
}

$withdraw_data = $withdraw[0];

// Cek apakah sudah diproses
if ($withdraw_data['status'] !== 'pending') {
    $bot->sendMessage($chat_id, "❌ Withdraw ini sudah diproses sebelumnya. Status: " . $withdraw_data['status']);
    return;
}

$actual_user_id = $withdraw_data['user_id'];
$amount = $withdraw_data['amount'];
$destination_account = $withdraw_data['destination_account'];

// Cari data user
$user = db_read('smm_users', ['id' => $actual_user_id]);
if (!$user) {
    $bot->sendMessage($chat_id, "❌ User tidak ditemukan di database.");
    return;
}

$user_chat_id = $user[0]['chatid'];

// Update Status di Tabel Withdrawals
$withdraw_update = [
    'admin_id' => $user_id,
    'admin_notes' => $reason,
    'status' => 'rejected',
    'processed_at' => date('Y-m-d H:i:s')
];
db_update('smm_withdrawals', $withdraw_update, ['id' => $withdraw_id]);

// Reset Posisi Admin
updateUserPosition($chat_id, 'main', '');

// Hapus pesan prompt input admin
$bot->deleteMessage($chat_id, $msg_id);

// --- NOTIFIKASI KE USER ---
$user_reply = "❌ <b>Withdraw Ditolak</b>\n\n";
$user_reply .= "Mohon maaf, permintaan withdraw Anda tidak dapat kami proses saat ini.\n\n";
$user_reply .= "💰 Nominal: Rp " . number_format($amount, 0, ',', '.') . "\n";
$user_reply .= "💳 Tujuan: " . $destination_account . "\n\n";
$user_reply .= "📝 <b>Alasan:</b> " . htmlspecialchars($reason) . "\n\n";
$user_reply .= "Saldo Anda tidak dikurangi. Silakan coba lagi atau hubungi Admin jika ada kesalahan.";

$bot->sendMessage($user_chat_id, $user_reply);

// --- KONFIRMASI KE ADMIN ---
$admin_reply = "✅ <b>Withdraw Ditolak</b>\n\n";
$admin_reply .= "👤 User ID: <code>$user_chat_id</code>\n";
$admin_reply .= "💰 Nominal: Rp " . number_format($amount, 0, ',', '.') . "\n";
$admin_reply .= "📝 Alasan: " . htmlspecialchars($reason) . "\n";
$admin_reply .= "📢 Status: User telah dinotifikasi.";

$message_result = $bot->sendMessage($chat_id, $admin_reply);

// Update last msg_id admin
if ($message_result && isset($message_result['result']['message_id'])) {
    $new_msg_id = $message_result['result']['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}

?>
