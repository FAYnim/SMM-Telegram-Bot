<?php
// Cek saldo user
$wallet_result = db_read('smm_wallets', ['user_id' => $user_id]);
if (empty($wallet_result)) {
    $bot->editMessage($chat_id, $msg_id, "❌ Wallet tidak ditemukan!", 'HTML');
    return;
}

$current_profit = $wallet_result[0]['profit'];

// Cek minimal transfer (Rp 1.000)
$min_transfer = 1000;

if ($current_profit < $min_transfer) {
    $reply = "❌ <b>Saldo Tidak Mencukupi</b>\n\n"
        . "Saldo Penghasilan: Rp " . number_format($current_profit, 0, ',', '.') . "\n"
        . "Minimal transfer: Rp " . number_format($min_transfer, 0, ',', '.') . "\n\n"
        . "Silakan kerjakan lebih banyak tugas untuk menambah saldo.";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/withdraw']
        ]
    ]);

    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    return;
}

// Tampilkan form transfer ke Saldo Campaign
$reply = "💰 <b>Transfer ke Saldo Campaign</b>\n\n"
    . "Saldo Penghasilan: Rp " . number_format($current_profit, 0, ',', '.') . "\n"
    . "Minimal transfer: Rp " . number_format($min_transfer, 0, ',', '.') . "\n\n"
    . "Silakan masukkan nominal yang ingin ditransfer ke Saldo Campaign:\n\n"
    . "💡 <i>Ketik nominal dalam angka (contoh: 50000)</i>";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Batal', 'callback_data' => '/withdraw']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

// Update posisi user untuk menunggu input nominal
updateUserPosition($chat_id, 'withdraw_campaign_amount');
?>
