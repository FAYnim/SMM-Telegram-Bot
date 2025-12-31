<?php

$platform = str_replace('/topup_', '', $cb_data);

$update_result = updateUserPosition($chat_id, "opsi_topup", $platform);

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

$reply = "Kirim ke alamat 08xxxxxx";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '✅ Konfirmasi', 'callback_data' => '/konfirmasi_topup'],
        ['text' => '🔙 Kembali', 'callback_data' => '/topup']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
