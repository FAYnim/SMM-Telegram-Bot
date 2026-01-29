<?php

// Validate input integer only
$cleaned_value = preg_replace('/[^0-9]/', '', $message) ?? '';

if($cleaned_value != $message) {
	$reply = "❌ <b>Format Salah</b>\n\n";
	$reply .= "Nilai harus berupa angka bulat tanpa titik atau koma.\n\n";
	$reply .= "<i>Contoh yang benar:</i> <code>10000</code>";

	$keyboard = $bot->buildInlineKeyboard([
		[
			['text' => '🔙 Kembali', 'callback_data' => 'settings_referral']
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

// Validation: Range 1.000 - 1.000.000
/*if($value < 1000 || $value > 1000000) {
	$reply = "❌ <b>Nilai Tidak Valid</b>\n\n";
	$reply .= "Reward harus antara <b>Rp 1.000</b> dan <b>Rp 1.000.000</b>.\n\n";
	$reply .= "Nilai yang Anda masukkan: <b>Rp " . number_format($value, 0, ',', '.') . "</b>";

	$keyboard = $bot->buildInlineKeyboard([
		[
			['text' => '🔙 Kembali', 'callback_data' => 'settings_referral']
		]
	]);

	$sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
	if($sent_message && isset($sent_message['result']['message_id'])) {
		$msg_id = $sent_message['result']['message_id'];
		db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
	}
	return;
}*/

// Read old value for logging
$old_setting = db_read('smm_settings', [
	'category' => 'referral',
	'setting_key' => 'reward_amount'
]);
$old_value = $old_setting[0]['setting_value'] ?? '0';

// Update database
$update = db_update('smm_settings', 
	['setting_value' => $value], 
	[
		'category' => 'referral',
		'setting_key' => 'reward_amount'
	]
);

logMessage('settings_update', [
	'chat_id' => $chat_id,
	'setting' => 'referral.reward_amount',
	'old_value' => $old_value,
	'new_value' => $value
], 'info');

$value_formatted = number_format($value, 0, ',', '.');
$old_formatted = number_format($old_value, 0, ',', '.');

$reply = "✅ <b>Berhasil Mengubah Pengaturan</b>\n\n";
$reply .= "💰 <b>Reward Referral</b> telah diupdate:\n\n";
$reply .= "Nilai Lama: <b>" . $old_formatted . "</b>\n";
$reply .= "Nilai Baru: <b>" . $value_formatted . "</b>\n\n";
$reply .= "━━━━━━━━━━━━━━━━━━━━\n\n";
$reply .= "<i>Perubahan berlaku untuk referral yang terjadi setelah ini.</i>";

$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '🔙 Kembali ke Pengaturan Referral', 'callback_data' => 'settings_referral']
	]
]);

$sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
if($sent_message && isset($sent_message['result']['message_id'])) {
	$msg_id = $sent_message['result']['message_id'];
	db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
}
?>