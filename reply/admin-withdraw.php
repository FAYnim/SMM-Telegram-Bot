<?php

$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
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
    $bot->sendMessage($chat_id, "❌ Gagal mengambil ID pesan (Err: 2).");
    return;
}

if($admin_msg_id != $msg_id) {
	// Update msg_id column with $admin_msg_id in table users and admins
	db_update('smm_users', ['msg_id' => $admin_msg_id], ['chatid' => $chat_id]);
	db_update('smm_admins',  ['msg_id' => $admin_msg_id], ['chatid' => $chat_id]);
}

// Get withdraw_id from callback data
$parts = explode('_', $cb_data);
$withdraw_id = end($parts);

if(strpos($cb_data, "approve") !== false) {

	$update_result = updateUserPosition($chat_id, 'main', 'withdraw_approve_'.$withdraw_id);
	if (!$update_result) {
		$bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!

Ketik /start untuk memulai ulang bot.");
		return;
	}

    $reply = "✅ <b>Setujui Withdraw</b>\n\n";
    $reply .= "Ketik <b>SETUJU</b> untuk mengkonfirmasi bahwa Anda sudah <b>mentransfer dana</b> ke user untuk Withdraw ID: <code>$withdraw_id</code>\n\n";
    $reply .= "<i>Pastikan Anda sudah melakukan transfer sebelum menyetujui.</i>";

	$bot->editMessage($chat_id, $admin_msg_id, $reply, 'HTML');

} elseif (strpos($cb_data, "reject")) {

	$update_result = updateUserPosition($chat_id, 'main', 'withdraw_reject_'.$withdraw_id);
	if (!$update_result) {
		$bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!

Ketik /start untuk memulai ulang bot.");
		return;
	}

    $reply = "❌ <b>Tolak Withdraw</b>\n\n";
    $reply .= "Masukkan <b>alasan penolakan</b> untuk Withdraw ID: <code>$withdraw_id</code>\n\n";
    $reply .= "<i>Alasan ini akan dikirimkan kepada user.</i>";

	$bot->editMessage($chat_id, $admin_msg_id, $reply, 'HTML');

} else {
	$bot->sendMessage($chat_id, "❌ Perintah tidak dikenali.");
}
?>
