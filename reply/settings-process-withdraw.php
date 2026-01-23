<?php
if($menu == 'settings_edit_min_withdraw') {
	$setting_type = 'min_withdraw';
	$setting_label = 'Minimum Withdrawal';
	$fee_type_mode = '';
} elseif($menu == 'settings_edit_admin_fee') {
	$setting_type = 'admin_fee';
	$setting_label = 'Biaya Admin';
	$fee_type_mode = '';
} elseif($menu == 'settings_input_admin_fee_flat') {
	$setting_type = 'admin_fee';
	$setting_label = 'Biaya Admin (Flat)';
	$fee_type_mode = 'flat';
} elseif($menu == 'settings_input_admin_fee_percentage') {
	$setting_type = 'admin_fee';
	$setting_label = 'Biaya Admin (Persentase)';
	$fee_type_mode = 'percentage';
} else {
	return;
}

// Handler untuk percentage
if($fee_type_mode == 'percentage') {
	// Validate input for percentage (allow decimal with dot)
	$cleaned_value = preg_replace('/[^0-9.]/', '', $message) ?? '';
	
	if($cleaned_value != $message || substr_count($cleaned_value, '.') > 1) {
		$reply = "❌ <b>Format Salah</b>\n\n";
		$reply .= "Nilai harus berupa angka desimal dengan titik (.).\n\n";
		$reply .= "<i>Contoh yang benar:</i> <code>2.5</code> atau <code>5</code>";

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
	
	$value = floatval($cleaned_value);
	
	if($value < 0.1 || $value > 100) {
		$reply = "❌ <b>Nilai Tidak Valid</b>\n\n";
		$reply .= "Persentase harus antara 0.1% - 100%.";

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
	
	// Update both admin_fee and admin_fee_type
	$update = db_update('smm_settings', ['setting_value' => $value], ['category' => 'withdraw', 'setting_key' => 'admin_fee']);
	$update_type = db_update('smm_settings', ['setting_value' => 'percentage'], ['category' => 'withdraw', 'setting_key' => 'admin_fee_type']);
	
	if(!$update || !$update_type) {
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
	$reply .= "Nilai: " . $value . "%";
	
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
	return;
}

// Handler untuk flat dan min_withdraw (integer only)
// Validate input
$cleaned_value = preg_replace('/[^0-9]/', '', $message) ?? '';

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

// Update admin_fee_type if this is flat admin fee
if($fee_type_mode == 'flat') {
	$update_type = db_update('smm_settings', ['setting_value' => 'flat'], ['category' => 'withdraw', 'setting_key' => 'admin_fee_type']);
	if(!$update || !$update_type) {
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
} else {
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
}

updateUserPosition($chat_id, 'settings_withdraw', '');

$reply = "✅ <b>Berhasil Mengubah Pengaturan</b>\n\n";
$reply .= "<b>$setting_label</b> telah diupdate:\n\n";
$reply .= "Nilai: " . number_format($value, 0, ',', '.');

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
