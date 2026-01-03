<?php

$update_result = updateUserPosition($chat_id, 'task');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

$reply = "<b>Campaign</b>\n\n";


$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/start']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
