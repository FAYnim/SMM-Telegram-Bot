<?php

$update_result = updateUserPosition($chat_id, 'topup_proof', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

$bot->deleteMessage($chat_id, $msg_id);

$waiting_result = $bot->sendMessage($chat_id, "Bukti sedang dikirim ke Admin, mohon menunggu...");
$waiting_msg_id = $waiting_result['result']['message_id'];
if (isset($waiting_msg_id)) {
    db_update('smm_users', ['msg_id' => $waiting_msg_id], ['chatid' => $chat_id]);

	// Send notif to admin
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

	// Insert topup ke deposits table
	db_create('smm_deposits', [
	    'user_id' => $user_id,
	    'proof_image_id' => $file_id
	]);

	// Edit pesan waiting menjadi final
	$reply = "Bukti topup sudah dikirim ke Admin. Mohon menunggu";
	$keyboard = $bot->buildInlineKeyboard([
	    [
	        ['text' => '🔙 Kembali', 'callback_data' => '/start']
	    ]
	]);
	$bot->editMessage($chat_id, $waiting_msg_id, $reply, 'HTML', $keyboard);
} else {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}
?>
