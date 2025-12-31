<?php

$update_result = updateUserPosition($chat_id, 'cek-saldo');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

// Check if user has wallet data
$wallet_check = db_read("smm_wallets", ["user_id" => $user_id]);

if (!$wallet_check) {
    // Insert new wallet with default balance 0
    $wallet_data = [
        "user_id" => $user_id,
        "balance" => 0.00
    ];
    db_create("smm_wallets", $wallet_data);
    $balance = 0.00;
} else {
    // Get existing balance
    $balance = $wallet_check[0]["balance"];
}

$reply = "Saldoku: " . number_format($balance, 2, ',', '.');

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '💰 Topup', 'callback_data' => '/topup']
    ],
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/start']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>