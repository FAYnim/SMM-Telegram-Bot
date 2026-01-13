<?php
if($menu == 'settings_edit_min_price_per_task') {
	$setting_type = 'min_price_per_task';
	$setting_label = 'Minimum Harga Per Task';
} else {
	return;
}

$cleaned_value = preg_replace('/[^0-9]/', '', $message);

if($cleaned_value != $message) {
	$reply = "❌ <b>Format Salah</b>\n\n";
	$reply .= "Nilai harus berupa angka tanpa titik atau koma.\n\n";
	$reply .= "<i>Contoh yang benar:</i> <code>100</code>";

	$keyboard = $bot->buildInlineKeyboard([
		[
			['text' => '🔙 Kembali', 'callback_data' => 'settings_campaign']
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
			['text' => '🔙 Kembali', 'callback_data' => 'settings_campaign']
		]
	]);

	$sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
	if($sent_message && isset($sent_message['result']['message_id'])) {
		$msg_id = $sent_message['result']['message_id'];
		db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
	}
	return;
}

$update = db_update('smm_settings', ['setting_value' => $value], ['category' => 'campaign', 'setting_key' => $setting_type]);

if(!$update) {
	$reply = "❌ <b>Gagal Mengupdate Data</b>\n\n";
	$reply .= "Terjadi kesalahan saat menyimpan pengaturan.";

	$keyboard = $bot->buildInlineKeyboard([
		[
			['text' => '🔙 Kembali', 'callback_data' => 'settings_campaign']
		]
	]);

	$sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
	if($sent_message && isset($sent_message['result']['message_id'])) {
		$msg_id = $sent_message['result']['message_id'];
		db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
	}
	return;
}

updateUserPosition($chat_id, 'settings_campaign', '');

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
