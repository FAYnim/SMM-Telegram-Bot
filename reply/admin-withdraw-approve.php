<?php

// Extract Withdraw ID from submenu
$parts = explode('_', $submenu);
$withdraw_id = $parts[2];

// Validasi
$confirmation = strtoupper(trim($message));

if ($confirmation !== 'SETUJU') {
    $reply = "❌ <b>Konfirmasi Tidak Valid</b>\nKetik <b>SETUJU</b> untuk mengkonfirmasi bahwa Anda sudah transfer dana ke user.";
    $bot->sendMessage($chat_id, $reply);
    return;
}

// Find data withdraw
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
$fee = $withdraw_data['fee'];
$destination_account = $withdraw_data['destination_account'];

// Total yang akan dipotong dari saldo = amount + fee
$total_deduction = $amount + $fee;

// Find data user
$user = db_read('smm_users', ['id' => $actual_user_id]);
if (!$user) {
    $bot->sendMessage($chat_id, "❌ User tidak ditemukan di database.");
    return;
}

$user_chat_id = $user[0]['chatid'];

// Find data wallet
$wallet = db_read('smm_wallets', ['user_id' => $actual_user_id]);
if (!$wallet) {
    $bot->sendMessage($chat_id, "❌ Wallet user tidak ditemukan.");
    return;
}

$profit_before = $wallet[0]['profit'];
$profit_after = $profit_before - $total_deduction;

// Validasi saldo cukup
if ($profit_before < $total_deduction) {
    $bot->sendMessage($chat_id, "❌ Saldo user tidak mencukupi. Saldo: Rp " . number_format($profit_before, 0, ',', '.') . ", Total Withdraw (Amount + Fee): Rp " . number_format($total_deduction, 0, ',', '.'));
    return;
}

// Update Saldo Wallet (profit)
$update_wallet = db_update('smm_wallets', ['profit' => $profit_after], ['user_id' => $actual_user_id]);

if (!$update_wallet) {
    $bot->sendMessage($chat_id, "❌ Gagal mengupdate saldo database.");
    return;
}

// Log Transaksi Wallet
$transaction_data = [
    'wallet_id' => $wallet[0]['id'],
    'type' => 'withdraw',
    'amount' => -$total_deduction,
    'balance_before' => $profit_before,
    'balance_after' => $profit_after,
    'description' => 'Withdraw disetujui oleh Admin (Amount: Rp ' . number_format($amount, 0, ',', '.') . ', Fee: Rp ' . number_format($fee, 0, ',', '.') . ')',
    'reference_id' => $withdraw_id,
    'status' => 'approved'
];
db_create('smm_wallet_transactions', $transaction_data);

// Update Status di Tabel Withdrawals
$withdraw_update = [
    'admin_id' => $user_id,
    'status' => 'approved',
    'processed_at' => date('Y-m-d H:i:s')
];
db_update('smm_withdrawals', $withdraw_update, ['id' => $withdraw_id]);

// Reset Posisi Admin
updateUserPosition($chat_id, 'main', '');

// Hapus pesan prompt input admin sebelumnya
$bot->deleteMessage($chat_id, $msg_id);

// --- NOTIFIKASI KE USER ---
$user_reply = "✅ <b>Withdraw Berhasil!</b>\n\n";
$user_reply .= "Dana sebesar <b>Rp " . number_format($amount, 0, ',', '.') . "</b> telah ditransfer ke nomor <b>" . $destination_account . "</b>.\n\n";

// Tampilkan fee jika ada
if($fee > 0) {
    $user_reply .= "💵 Biaya Admin: Rp " . number_format($fee, 0, ',', '.') . "\n";
    $user_reply .= "📊 Total Dipotong: Rp " . number_format($total_deduction, 0, ',', '.') . "\n\n";
}

$user_reply .= "💰 Saldo Anda sekarang: Rp " . number_format($profit_after, 0, ',', '.') . "\n\n";
$user_reply .= "Terima kasih telah menggunakan layanan kami!";

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
$admin_reply = "✅ <b>Withdraw Disetujui</b>\n\n";
$admin_reply .= "👤 User ID: <code>$user_chat_id</code>\n";
$admin_reply .= "💰 Nominal: <b>Rp " . number_format($amount, 0, ',', '.') . "</b>\n";

// Tampilkan fee jika ada
if($fee > 0) {
    $admin_reply .= "💵 Biaya Admin: Rp " . number_format($fee, 0, ',', '.') . "\n";
    $admin_reply .= "📊 Total Dipotong: Rp " . number_format($total_deduction, 0, ',', '.') . "\n";
}

$admin_reply .= "💳 Tujuan: " . $destination_account . "\n";
$admin_reply .= "📊 Saldo User: Rp " . number_format($profit_before, 0, ',', '.') . " → Rp " . number_format($profit_after, 0, ',', '.') . "\n";
$admin_reply .= "📢 Status: User telah dinotifikasi.";

$message_result = $bot->sendMessage($chat_id, $admin_reply);

// Update last msg_id admin
if ($message_result && isset($message_result['result']['message_id'])) {
    $new_msg_id = $message_result['result']['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}

?>
