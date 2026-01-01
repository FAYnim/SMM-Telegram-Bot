<?php

// Validasi nominal angka
$nominal = trim($message);

// Cek apakah input adalah angka dan lebih dari 0
if (!is_numeric($nominal) || $nominal <= 0) {
    $reply = "❌ Masukkan nominal yang valid (angka lebih dari 0)";
    $keyboard = [];

    $bot->editMessage($chat_id, $msg_id, $reply);
    return;
}

$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

$bot->deleteMessage($chat_id, $msg_id);

$reply = "✅ Top-up sebesar Rp " . number_format($nominal, 0, ',', '.') . " telah disetujui!\n\nPesan approve sudah dikirim ke user!";
$keyboard = [];

$message_result = $bot->sendMessage($chat_id, $reply);

if ($message_result && isset($message_result['message_id'])) {
    $new_msg_id = $message_result['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}

?>
