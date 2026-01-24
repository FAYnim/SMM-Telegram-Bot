<?php

// Extract deposit_id from submenu (format: topup_approve_{deposit_id})
$parts = explode('_', $submenu);
$deposit_id = $parts[2];

// Query deposit data
$deposit = db_read('smm_deposits', ['id' => $deposit_id]);
if (!$deposit) {
    $bot->sendMessage($chat_id, "❌ Data deposit tidak ditemukan.");
    return;
}

$deposit_data = $deposit[0];
$user_id = $deposit_data['user_id'];

// Query user data untuk mendapatkan chat_id
$user = db_read('smm_users', ['id' => $user_id]);
if (!$user) {
    $bot->sendMessage($chat_id, "❌ User tidak ditemukan di database.");
    return;
}

$user_chat_id = $user[0]['chatid'];
$actual_user_id = $user[0]['id'];

// Validasi Input Nominal
$nominal = trim($message);

if (!is_numeric($nominal) || $nominal <= 0) {
    $reply = "❌ <b>Nominal Tidak Valid</b>\nMohon masukkan angka nominal yang benar (contoh: 50000).";
    $bot->sendMessage($chat_id, $reply, 'HTML');
    return;
}


$wallet = db_read('smm_wallets', ['user_id' => $actual_user_id]);

// Create wallet if not exists (fail-safe)
if (!$wallet) {
    db_create('smm_wallets', ['user_id' => $actual_user_id, 'balance' => 0]);
    $balance_before = 0;
} else {
    $balance_before = $wallet[0]['balance'];
}

$balance_after = $balance_before + $nominal;

// Update Saldo Wallet
$update_wallet = db_update('smm_wallets', ['balance' => $balance_after], ['user_id' => $actual_user_id]);

if (!$update_wallet) {
    $bot->sendMessage($chat_id, "❌ Gagal mengupdate saldo database.");
    return;
}

// Log Transaksi Wallet
$transaction_data = [
    'wallet_id' => $wallet ? $wallet[0]['id'] : db_read('smm_wallets', ['user_id' => $actual_user_id])[0]['id'],
    'type' => 'deposit',
    'amount' => $nominal,
    'balance_before' => $balance_before,
    'balance_after' => $balance_after,
    'description' => 'Top-up manual oleh Admin',
    'status' => 'approved'
];
db_create('smm_wallet_transactions', $transaction_data);

// Update Status di Tabel Deposits berdasarkan deposit_id
$deposit_update = [
    'amount' => $nominal,
    'admin_id' => $user_id,
    'status' => 'approved',
    'processed_at' => date('Y-m-d H:i:s')
];
db_update('smm_deposits', $deposit_update, ['id' => $deposit_id]);

// Reset Posisi Admin
$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
    return;
}

// Hapus pesan prompt input admin sebelumnya
$bot->deleteMessage($chat_id, $msg_id);

// --- NOTIFIKASI KE USER ---
$user_reply = "✅ <b>Topup Berhasil!</b>\n\n";
$user_reply .= "Saldo sebesar <b>Rp " . number_format($nominal, 0, ',', '.') . "</b> telah ditambahkan ke akun Anda.\n";
$user_reply .= "Terima kasih telah melakukan pengisian saldo.";

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
$admin_reply = "✅ <b>Topup Disetujui</b>\n\n";
$admin_reply .= "👤 User ID: <code>$user_chat_id</code>\n";
$admin_reply .= "💰 Nominal: <b>Rp " . number_format($nominal, 0, ',', '.') . "</b>\n";
$admin_reply .= "📢 Status: User telah dinotifikasi.";

$message_result = $bot->sendMessage($chat_id, $admin_reply, 'HTML');

// Update last msg_id admin
if ($message_result && isset($message_result['result']['message_id'])) {
    $new_msg_id = $message_result['result']['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}

?>
