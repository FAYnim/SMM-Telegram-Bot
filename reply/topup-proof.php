<?php

$update_result = updateUserPosition($chat_id, 'topup_proof', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

$bot->deleteMessage($chat_id, $msg_id);

$reply = "Bukti topup sudah dikirim ke Admin. Mohon menunggu";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/start']
    ]
]);

$message_result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);

if ($message_result && isset($message_result['result']['message_id'])) {
    $new_msg_id = $message_result['result']['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}


?>
