<?php

$update_result = updateUserPosition($chat_id, 'topup_proof', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

$bot->deleteMessage($chat_id, $msg_id);

$admins = db_read("smm_admins");

if($admins) {
	if(!isset($file_id)) {
	    $bot->sendMessage($chat_id, "❌ Something Error!");
	    return;
	}
	foreach($admins as $admin):
		$admin_id = $admin["chatid"];
		$caption = "User {$chat_id} melakukan topup";

		$keyboard = $bot->buildInlineKeyboard([
		    [
		        ['text' => '✅ Terima', 'callback_data' => 'admin_approve_topup_' . $chat_id],
		        ['text' => '❌ Tolak', 'callback_data' => 'admin_reject_topup_' . $chat_id]
		    ]
		]);

		$bot->sendPhoto($admin_id, $file_id, $caption);
		sleep(1);

		$reply = "Pilih aksi untuk topup User {$chat_id}:";
		$bot->sendMessageWithKeyboard($admin_id, $reply, $keyboard);
		sleep(1);
	endforeach;
}

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
