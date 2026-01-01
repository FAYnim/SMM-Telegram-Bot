<?php

$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error 1!");
    return;
}

$bot->deleteMessage($chat_id, $msg_id);

// Cek apakah user adalah admin
$admin = db_read('smm_admins', ['chatid' => $chat_id]);
if (empty($admin)) {
	$bot->sendMessage($chat_id, "❌ Akses ditolak! Anda bukan admin.");
	return;
}

$admin_msg_id = $bot->getCallbackMessageId();
if(!$admin_msg_id) {
    $bot->sendMessage($chat_id, "❌ Something Error 2!");
    return;
}

if($admin_msg_id != $msg_id) {
	// Update msg_id column with $admin_msg_id in table users and admins
	db_update('smm_users', ['chatid' => $chat_id], ['msg_id' => $admin_msg_id]);
	db_update('smm_admins', ['chatid' => $chat_id], ['msg_id' => $admin_msg_id]);
}

// Get user_chat_id
$parts = explode('_', $cb_data);
$user_chat_id = end($parts);

if(strpos($cb_data, "approve") !== false) {

	$update_result = updateUserPosition($chat_id, 'main', 'topup_approve_'.$user_chat_id);
	if (!$update_result) {
		$bot->sendMessage($chat_id, "❌ Something Error!");
		return;
	}

	$bot->editMessage($chat_id, $admin_msg_id,  "✅ Berapa nominal topup yang disetujui?");

} elseif (strpos($cb_data, "reject")) {

	$update_result = updateUserPosition($chat_id, 'main', 'topup_reject_'.$user_chat_id);
	if (!$update_result) {
		$bot->sendMessage($chat_id, "❌ Something Error!");
		return;
	}

	$bot->editMessage($chat_id, $admin_msg_id, "❌ Kenapa topup ini ditolak?");

} else {
	$bot->sendMessage($chat_id, "System Error!");
}
?>
