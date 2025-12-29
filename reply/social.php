<?php

$update_result = updateUserPosition($chat_id, 'social');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

$reply = "Media sosialmu:\n\n"
    . "Pilih menu di bawah:";
    
$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
        ['text' => '🔙 Kembali', 'callback_data' => '/start']
    ]
]);

$bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);

?>
