<?php

if ($cb_data == "/tambah_medsos") {
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
}

if ($cb_data == "/add_instagram") {
    $update_result = updateUserPosition($chat_id, 'add_instagram');

    if (!$update_result) {
        $bot->sendMessage($chat_id, "❌ Something Error!");
        return;
    }

    $reply = "📷 <b>Tambah Instagram</b>\n\nSilakan masukkan username Instagram yang ingin ditambahkan:";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/tambah_medsos']
        ]
    ]);

    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
}

if ($cb_data == "/add_tiktok") {
    $update_result = updateUserPosition($chat_id, 'add_tiktok');

    if (!$update_result) {
        $bot->sendMessage($chat_id, "❌ Something Error!");
        return;
    }

    $reply = "🎵 <b>Tambah TikTok</b>\n\nSilakan masukkan username TikTok yang ingin ditambahkan:";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/tambah_medsos']
        ]
    ]);

    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
}

?>