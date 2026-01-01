<?php

$update_result = updateUserPosition($chat_id, 'topup');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

$reply = "💎 <b>Isi Ulang Saldo</b>\n\n";
$reply .= "Silakan pilih metode pembayaran yang tersedia di bawah ini untuk melakukan pengisian saldo.";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '💵 DANA', 'callback_data' => '/topup_dana'],
        ['text' => '💵 ShopeePay', 'callback_data' => '/topup_shopeepay']
    ],
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/start']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
