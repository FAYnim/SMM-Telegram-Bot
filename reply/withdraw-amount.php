<?php
// Handler untuk input nominal withdraw
$amount = trim($message);

// Validasi input harus angka
if (!is_numeric($amount) || $amount <= 0) {
    $reply = "❌ <b>Input Tidak Valid</b>\n\n"
        . "Nominal harus berupa angka positif.\n\n"
        . "💡 <i>Contoh: 50000</i>";

    $bot->deleteMessage($chat_id, $msg_id);
    $bot->sendMessage($chat_id, $reply);
    return;
}

$amount = (int) $amount;

// Cek minimal withdraw (Rp 50.000)
$min_withdraw = 50000;
if ($amount < $min_withdraw) {
    $reply = "❌ <b>Nominal Terlalu Kecil</b>\n\n"
        . "Nominal: Rp " . number_format($amount, 0, ',', '.') . "\n"
        . "Minimal withdraw: Rp " . number_format($min_withdraw, 0, ',', '.') . "\n\n"
        . "Silakan masukkan nominal minimal Rp " . number_format($min_withdraw, 0, ',', '.') . " atau lebih.";

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

$current_balance = $wallet_result[0]['profit'];

// Cek apakah saldo cukup
if ($amount > $current_balance) {
    $reply = "❌ <b>Saldo Tidak Mencukupi</b>\n\n"
        . "Saldo Anda: Rp " . number_format($current_balance, 0, ',', '.') . "\n"
        . "Nominal withdraw: Rp " . number_format($amount, 0, ',', '.') . "\n"
        . "Kekurangan: Rp " . number_format($amount - $current_balance, 0, ',', '.') . "\n\n"
        . "Silakan masukkan nominal yang tidak melebihi saldo Anda.";

    $bot->deleteMessage($chat_id, $msg_id);
    $bot->sendMessage($chat_id, $reply);
    return;
}

// Simpan nominal di submenu untuk digunakan di step selanjutnya
$update_result = updateUserPosition($chat_id, 'withdraw_destination', $amount);

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem.");
    return;
}

// Minta nomor tujuan (DANA)
$reply = "💳 <b>Nomor Tujuan Withdraw</b>\n\n"
    . "Nominal withdraw: Rp " . number_format($amount, 0, ',', '.') . "\n\n"
    . "Silakan masukkan nomor tujuan withdraw Anda:\n\n"
    . "💡 <i>Contoh: 081234567890 (DANA/OVO/GoPay)</i>";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Batal', 'callback_data' => '/start']
    ]
]);

$bot->deleteMessage($chat_id, $msg_id);
$send_result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);

// Simpan msg_id baru
if ($send_result && isset($send_result['result']['message_id'])) {
    $new_msg_id = $send_result['result']['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}
?>
