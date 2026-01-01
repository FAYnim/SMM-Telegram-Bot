<?php

$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

$bot->deleteMessage($chat_id, $msg_id);

// Cek apakah user adalah admin
$admin = db_read('smm_admins', ['chatid' => $chat_id]);
if (empty($admin)) {
	$bot->sendMessage($chat_id, "❌ Akses ditolak! Anda bukan admin.");
	return;
}

if(strpos($cb_data, "approve") !== false) {
	$bot->sendMessage($chat_id, "Approve");
} elseif (strpos($cb_data, "reject")) {
	$bot->sendMessage($chat_id, "Reject");
} else {
	$bot->sendMessage($chat_id, "Systek Error!");
}
?>
