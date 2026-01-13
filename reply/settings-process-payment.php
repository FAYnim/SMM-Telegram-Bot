<?php
if($menu == 'settings_edit_dana') {
	$payment_method = 'dana';
	$method_label = 'DANA';
} elseif($menu == 'settings_edit_shopeepay') {
	$payment_method = 'shopeepay';
	$method_label = 'ShopeePay';
} else {
	return;
}

// cek pemisah
if(strpos($message, '/') === false) {
	$reply = "❌ <b>Format Salah</b>\n\n";
	$reply .= "Format harus menggunakan tanda / (slash)\n\n";
	$reply .= "<i>Contoh yang benar:</i> <code>0812-3456-7890/Ahmad Faisal</code>";

	$keyboard = $bot->buildInlineKeyboard([
		[
			['text' => '🔙 Kembali', 'callback_data' => 'settings_payment']
		]
	]);

	$sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
	if($sent_message && isset($sent_message['result']['message_id'])) {
		$msg_id = $sent_message['result']['message_id'];
		db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
	}
	return;
}

$parts = explode('/', $message);
$number = trim($parts[0]);
$name = trim($parts[1] ?? '');

if(empty($number) || empty($name)) {
	$reply = "❌ <b>Data Tidak Lengkap</b>\n\n";
	$reply .= "Nomor dan nama tidak boleh kosong.";

	$keyboard = $bot->buildInlineKeyboard([
		[
			['text' => '🔙 Kembali', 'callback_data' => 'settings_payment']
		]
	]);

	$sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
	if($sent_message && isset($sent_message['result']['message_id'])) {
		$msg_id = $sent_message['result']['message_id'];
		db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
	}
	return;
}

$number_key = $payment_method . '_number';
$name_key = $payment_method . '_name';

$update1 = db_update('smm_settings', ['setting_value' => $number], ['category' => 'payment', 'setting_key' => $number_key]);
$update2 = db_update('smm_settings', ['setting_value' => $name], ['category' => 'payment', 'setting_key' => $name_key]);

if(!$update1 || !$update2) {
	$reply = "❌ <b>Gagal Mengupdate Data</b>\n\n";
	$reply .= "Terjadi kesalahan saat menyimpan pengaturan.";

	$keyboard = $bot->buildInlineKeyboard([
		[
			['text' => '🔙 Kembali', 'callback_data' => 'settings_payment']
		]
	]);

	$sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
	if($sent_message && isset($sent_message['result']['message_id'])) {
		$msg_id = $sent_message['result']['message_id'];
		db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
	}
	return;
}

updateUserPosition($chat_id, 'settings_payment', '');

$reply = "✅ <b>Berhasil Mengubah Pengaturan</b>\n\n";
$reply .= "<b>$method_label</b> telah diupdate:\n\n";
$reply .= "📞 Nomor: <code>$number</code>\n";
$reply .= "👤 A/N: <code>$name</code>";

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
