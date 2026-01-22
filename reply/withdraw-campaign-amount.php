<?php
$amount = trim($message);

// Validasi
if (!is_numeric($amount) || $amount <= 0) {
    $reply = "❌ <b>Input Tidak Valid</b>\n\n"
        . "Nominal harus berupa angka positif.\n\n"
        . "💡 <i>Contoh: 50000</i>";

    $bot->deleteMessage($chat_id, $msg_id);
    $bot->sendMessage($chat_id, $reply);
    return;
}

$amount = (int) $amount;

// Cek minimal transfer (Rp 1.000)
$min_transfer = 1000;
if ($amount < $min_transfer) {
    $reply = "❌ <b>Nominal Terlalu Kecil</b>\n\n"
        . "Nominal: " . number_format($amount, 0, ',', '.') . "\n"
        . "Minimal transfer: " . number_format($min_transfer, 0, ',', '.') . "\n\n"
        . "Silakan masukkan nominal minimal " . number_format($min_transfer, 0, ',', '.') . " atau lebih.";

    $bot->deleteMessage($chat_id, $msg_id);
    $bot->sendMessage($chat_id, $reply);
    return;
}

// Cek saldo user
$wallet_result = db_read('smm_wallets', ['user_id' => $user_id]);
if (empty($wallet_result)) {
    $bot->sendMessage($chat_id, "❌ Wallet tidak ditemukan!");
    return;
}

$current_profit = $wallet_result[0]['profit'];
$wallet_id = $wallet_result[0]['id'];

// Cek apakah saldo cukup
if ($amount > $current_profit) {
    $reply = "❌ <b>Saldo Tidak Mencukupi</b>\n\n"
        . "Saldo Penghasilan: " . number_format($current_profit, 0, ',', '.') . "\n"
        . "Nominal transfer: " . number_format($amount, 0, ',', '.') . "\n"
        . "Kekurangan: " . number_format($amount - $current_profit, 0, ',', '.') . "\n\n"
        . "Silakan masukkan nominal yang tidak melebihi saldo Anda.";

    $bot->deleteMessage($chat_id, $msg_id);
    $bot->sendMessage($chat_id, $reply);
    return;
}

// Hitung balance profit
$profit_before = $current_profit;
$profit_after = $current_profit - $amount;

// Kurangi profit
$update_result = db_update('smm_wallets', ['profit' => $profit_after], ['id' => $wallet_id]);
if (!$update_result) {
    $bot->deleteMessage($chat_id, $msg_id);
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan saat mengurangi profit.");
    return;
}

// Tambahkan ke balance (campaign balance)
$current_balance = $wallet_result[0]['balance'];
$balance_before = $current_balance;
$balance_after = $current_balance + $amount;

$update_result = db_update('smm_wallets', ['balance' => $balance_after], ['id' => $wallet_id]);
if (!$update_result) {
    $bot->deleteMessage($chat_id, $msg_id);
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan saat menambah saldo campaign.");
    return;
}

// Catat transaksi pengurangan profit
$transaction_data_profit = [
    'wallet_id' => $wallet_id,
    'type' => 'adjustment',
    'amount' => $amount,
    'balance_before' => $profit_before,
    'balance_after' => $profit_after,
    'description' => 'Transfer dari Saldo Penghasilan ke Saldo Campaign',
    'status' => 'approved'
];
db_create('smm_wallet_transactions', $transaction_data_profit);

// Catat transaksi penambahan balance (campaign)
$transaction_data_balance = [
    'wallet_id' => $wallet_id,
    'type' => 'adjustment',
    'amount' => $amount,
    'balance_before' => $balance_before,
    'balance_after' => $balance_after,
    'description' => 'Transfer dari Saldo Penghasilan',
    'status' => 'approved'
];
db_create('smm_wallet_transactions', $transaction_data_balance);

// Reset posisi user
updateUserPosition($chat_id, 'main', '');

// Hapus pesan lama
$bot->deleteMessage($chat_id, $msg_id);

// Tampilkan pesan sukses
$reply = "✅ <b>Transfer Berhasil!</b>\n\n"
    . "💰 Nominal: " . number_format($amount, 0, ',', '.') . "\n"
    . "Dari: Saldo Penghasilan\n"
    . "Ke: Saldo Campaign\n\n"
    . "Saldo Penghasilan: " . number_format($profit_after, 0, ',', '.') . "\n"
    . "Saldo Campaign: " . number_format($balance_after, 0, ',', '.');

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali ke Menu Utama', 'callback_data' => '/start']
    ]
]);

$send_result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);

// Simpan msg_id baru
if ($send_result && isset($send_result['result']['message_id'])) {
    $new_msg_id = $send_result['result']['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}
?>
