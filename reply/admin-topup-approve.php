<?php

// Extract User ID
$parts = explode('_', $submenu);
$user_chat_id = $parts[2];

// Validasi Input
$nominal = trim($message);

if (!is_numeric($nominal) || $nominal <= 0) {
    $reply = "❌ Masukkan nominal yang valid (angka lebih dari 0)";
    $keyboard = [];

    $bot->editMessage($chat_id, $msg_id, $reply);
    return;
}

// Dapatkan Balance User
// Cari user_id dari smm_users berdasarkan chatid
$user = db_read('smm_users', ['chatid' => $user_chat_id]);
if (!$user) {
    $reply = "❌ User tidak ditemukan!";
    $bot->editMessage($chat_id, $msg_id, $reply);
    return;
}

$actual_user_id = $user[0]['id'];
$wallet = db_read('smm_wallets', ['user_id' => $actual_user_id]);
if (!$wallet) {
    $reply = "❌ Wallet user tidak ditemukan!";
    $bot->editMessage($chat_id, $msg_id, $reply);
    return;
}

$balance_before = $wallet[0]['balance'];
$balance_after = $balance_before + $nominal;

// Update Saldo Wallet
$update_wallet = db_update('smm_wallets', ['balance' => $balance_after], ['user_id' => $actual_user_id]);
if (!$update_wallet) {
    $reply = "❌ Gagal update saldo!";
    $bot->editMessage($chat_id, $msg_id, $reply);
    return;
}

// Log Transaksi Wallet
$transaction_data = [
    'wallet_id' => $wallet[0]['id'],
    'type' => 'deposit',
    'amount' => $nominal,
    'balance_before' => $balance_before,
    'balance_after' => $balance_after,
    'description' => 'Top-up manual oleh admin'
];
db_create('smm_wallet_transactions', $transaction_data);

// Update Deposit Status
$deposit_update = [
    'amount' => $nominal,
    'admin_id' => $user_id,
    'status' => 'approved',
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
$user_reply = "✅ Top-up Anda sebesar Rp " . number_format($nominal, 0, ',', '.') . " telah disetujui!\n\nSaldo akan segera ditambahkan ke akun Anda.";
$bot->sendMessage($user_chat_id, $user_reply);

// Konfirmasi ke Admin
$reply = "✅ Top-up sebesar Rp " . number_format($nominal, 0, ',', '.') . " telah disetujui!\n\nPesan approve sudah dikirim ke user!";
$keyboard = [];

$message_result = $bot->sendMessage($chat_id, $reply);

// Update Message ID
if ($message_result && isset($message_result['message_id'])) {
    $new_msg_id = $message_result['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}

?>
