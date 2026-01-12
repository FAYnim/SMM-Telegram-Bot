<?php
$setting_type = '';

if($cb_data == "settings_edit_min_withdraw") {
	$setting_type = 'min_withdraw';
	$update_result = updateUserPosition($chat_id, 'settings_withdraw', 'edit_min_withdraw');
} elseif($cb_data == "settings_edit_admin_fee") {
	$setting_type = 'admin_fee';
	$update_result = updateUserPosition($chat_id, 'settings_withdraw', 'edit_admin_fee');
} else {
	$bot->sendMessage($chat_id, '❌ Perintah tidak dikenali');
	return;
}

if(!$update_result) {
	$bot->sendMessage($chat_id, '❌ Terjadi kesalahan sistem');
	return;
}

if($setting_type == 'min_withdraw') {
	$reply = "📊 <b>Ubah Minimum Withdrawal</b>\n\n";
	$reply .= "Silakan masukkan nilai minimum withdrawal baru (dalam Rupiah):\n\n";
	$reply .= "<i>Contoh: 50000</i>\n\n";
	$reply .= "<i>Nilai harus berupa angka tanpa titik atau koma</i>";
} else {
	$reply = "💰 <b>Ubah Biaya Admin</b>\n\n";
	$reply .= "Silakan masukkan biaya admin withdrawal baru (dalam Rupiah):\n\n";
	$reply .= "<i>Contoh: 5000</i>\n\n";
	$reply .= "<i>Nilai harus berupa angka tanpa titik atau koma</i>";
}

$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '🔙 Batal', 'callback_data' => 'settings_withdraw']
	]
]);

$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, 'HTML', $keyboard);

updateUserPosition($chat_id, 'settings_edit_' . $setting_type);
?>
