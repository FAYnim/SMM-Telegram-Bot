<?php

$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Gagal memperbarui status admin (Err: 1).");
    return;
}

//$bot->deleteMessage($chat_id, $msg_id);

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

// Get deposit_id from callback data
$parts = explode('_', $cb_data);
$deposit_id = end($parts);

// Query deposit data berdasarkan deposit_id
$deposit = db_read('smm_deposits', ['id' => $deposit_id]);

if (!$deposit) {
    $bot->sendMessage($chat_id, "❌ Data deposit tidak ditemukan.");
    return;
}

$deposit_data = $deposit[0];
$user_id = $deposit_data['user_id'];

// Query user data untuk mendapatkan chat_id
$user = db_read('smm_users', ['id' => $user_id]);

if (!$user) {
    $bot->sendMessage($chat_id, "❌ User tidak ditemukan.");
    return;
}

$user_chat_id = $user[0]['chatid'];

if(strpos($cb_data, "approve") !== false) {

	$update_result = updateUserPosition($chat_id, 'main', 'topup_approve_'.$deposit_id);
	if (!$update_result) {
		$bot->sendMessage($chat_id, "❌ Gagal memperbarui posisi admin.");
		return;
	}

    $reply = "✅ <b>Setujui Topup</b>\n\n";
    $reply .= "Masukkan <b>nominal saldo</b> (angka) yang akan ditambahkan untuk User ID: <code>$user_chat_id</code>\n\n";
    $reply .= "<i>Contoh: 50000</i>";

	$bot->editMessage($chat_id, $admin_msg_id, $reply, 'HTML');

} elseif (strpos($cb_data, "reject")) {

	$update_result = updateUserPosition($chat_id, 'main', 'topup_reject_'.$deposit_id);
	if (!$update_result) {
		$bot->sendMessage($chat_id, "❌ Gagal memperbarui posisi admin.");
		return;
	}

    $reply = "❌ <b>Tolak Topup</b>\n\n";
    $reply .= "Masukkan <b>alasan penolakan</b> untuk User ID: <code>$user_chat_id</code>\n\n";
    $reply .= "<i>Alasan ini akan dikirimkan kepada user.</i>";

	$bot->editMessage($chat_id, $admin_msg_id, $reply, 'HTML');

} else {
	$bot->sendMessage($chat_id, "❌ Perintah tidak dikenali.");
}
?>