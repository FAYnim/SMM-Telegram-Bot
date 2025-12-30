<?php

$update_result = updateUserPosition($chat_id, 'tambah_medsos');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

$reply = "Pilih Medsos yang ingin ditambahkan!";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '📷 Instagram', 'callback_data' => '/add_instagram'],
        ['text' => '🎵 TikTok', 'callback_data' => '/add_tiktok']
    ],
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/social']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>