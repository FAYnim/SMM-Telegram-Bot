<?php

if ($message == "/social" || $cb_data == "/social") {
    $reply = "Media sosialmu:\n\n"
        . "Pilih menu di bawah:";
        
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);
    
    $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);
}

?>
