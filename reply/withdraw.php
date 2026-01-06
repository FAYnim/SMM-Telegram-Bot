<?php
// Cek apakah user punya pending withdraw
$query = "SELECT * FROM smm_withdrawals "
          ."WHERE user_id = ? AND status = 'pending' "
          ."ORDER BY created_at DESC LIMIT 1";
$pending_result = db_query($query, [$user_id]);

if (!empty($pending_result)) {
    $pending_withdraw = $pending_result[0];
    $reply = "⏳ <b>Withdraw Sedang Diproses</b>\n\n"
        . "Anda memiliki permintaan withdraw yang masih pending:\n"
        . "💰 Nominal: Rp " . number_format($pending_withdraw['amount'], 0, ',', '.') . "\n"
        . "📅 Tanggal: " . date('d M Y H:i', strtotime($pending_withdraw['created_at'])) . "\n\n"
        . "Silakan tunggu hingga permintaan sebelumnya diproses.";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);

    $bot->sendMessage($chat_id, $reply, $keyboard);
    return;
}

// Cek saldo user
$wallet_result = db_read('smm_wallets', ['user_id' => $user_id]);
if (empty($wallet_result)) {
    $bot->sendMessage($chat_id, "❌ Wallet tidak ditemukan!");
    return;
}

$current_profit = $wallet_result[0]['profit'];

// Cek minimal withdraw (Rp 50.000)
$min_withdraw = 50000;

if ($current_profit < $min_withdraw) {
    $reply = "❌ <b>Saldo Tidak Mencukupi</b>\n\n"
        . "Saldo Anda: Rp " . number_format($current_profit, 0, ',', '.') . "\n"
        . "Minimal withdraw: Rp " . number_format($min_withdraw, 0, ',', '.') . "\n\n"
        . "Silakan kerjakan lebih banyak tugas untuk menambah saldo.";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);

    $bot->sendMessage($chat_id, $reply, $keyboard);
    return;
}

// Tampilkan form withdraw
$reply = "💸 <b>Form Withdraw</b>\n\n"
    . "Saldo Anda: Rp " . number_format($current_profit, 0, ',', '.') . "\n"
    . "Minimal withdraw: Rp " . number_format($min_withdraw, 0, ',', '.') . "\n\n"
    . "Silakan masukkan nominal withdraw yang Anda inginkan:\n\n"
    . "💡 <i>Ketik nominal dalam angka (contoh: 50000)</i>";

$bot->sendMessage($chat_id, $reply);

// Update posisi user untuk menunggu input nominal
updateUserPosition($chat_id, 'withdraw_amount');
?>
