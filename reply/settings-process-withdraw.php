<?php
if($menu == 'settings_edit_min_withdraw') {
	$setting_type = 'min_withdraw';
	$setting_label = 'Minimum Withdrawal';
} elseif($menu == 'settings_edit_admin_fee') {
	$setting_type = 'admin_fee';
	$setting_label = 'Biaya Admin';
} else {
	return;
}

// Validate input
$cleaned_value = preg_replace('/[^0-9]/', '', $message);

if($cleaned_value != $message) {
	$reply = "❌ <b>Format Salah</b>\n\n";
	$reply .= "Nilai harus berupa angka tanpa titik atau koma.\n\n";
	$reply .= "<i>Contoh yang benar:</i> <code>50000</code>";

	$keyboard = $bot->buildInlineKeyboard([
		[
			['text' => '🔙 Kembali', 'callback_data' => 'settings_withdraw']
		]
	]);

	$sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
	if($sent_message && isset($sent_message['result']['message_id'])) {
		$msg_id = $sent_message['result']['message_id'];
		db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
	}
	return;
}

$value = intval($cleaned_value);

if($value <= 0) {
	$reply = "❌ <b>Nilai Tidak Valid</b>\n\n";
	$reply .= "Nilai harus lebih dari 0.";

	$keyboard = $bot->buildInlineKeyboard([
		[
			['text' => '🔙 Kembali', 'callback_data' => 'settings_withdraw']
		]
	]);

	$sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
	if($sent_message && isset($sent_message['result']['message_id'])) {
		$msg_id = $sent_message['result']['message_id'];
		db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
	}
	return;
}

$update = db_update('smm_settings', ['setting_value' => $value], ['category' => 'withdraw', 'setting_key' => $setting_type]);

if(!$update) {
	$reply = "❌ <b>Gagal Mengupdate Data</b>\n\n";
	$reply .= "Terjadi kesalahan saat menyimpan pengaturan.";

	$keyboard = $bot->buildInlineKeyboard([
		[
			['text' => '🔙 Kembali', 'callback_data' => 'settings_withdraw']
		]
	]);

	$sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
	if($sent_message && isset($sent_message['result']['message_id'])) {
		$msg_id = $sent_message['result']['message_id'];
		db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
	}
	return;
}

updateUserPosition($chat_id, 'settings_withdraw', '');

$reply = "✅ <b>Berhasil Mengubah Pengaturan</b>\n\n";
$reply .= "<b>$setting_label</b> telah diupdate:\n\n";
$reply .= "Nilai: Rp " . number_format($value, 0, ',', '.');

$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '🔙 Kembali', 'callback_data' => '/start']
	]
]);

$sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
if($sent_message && isset($sent_message['result']['message_id'])) {
	$msg_id = $sent_message['result']['message_id'];
	db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
}
?>
