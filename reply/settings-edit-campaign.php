<?php
if($cb_data == "settings_edit_min_price_per_task") {
	$setting_type = 'min_price_per_task';
	$update_result = updateUserPosition($chat_id, 'settings_campaign', 'edit_min_price_per_task');
} else {
	$bot->sendMessage($chat_id, '❌ Perintah tidak dikenali');
	return;
}

if(!$update_result) {
	$bot->sendMessage($chat_id, '❌ Terjadi kesalahan sistem');
	return;
}

$reply = "💰 <b>Ubah Minimum Harga Per Task</b>\n\n";
$reply .= "Silakan masukkan nilai minimum harga per task baru (dalam Rupiah):\n\n";
$reply .= "<i>Contoh: 100</i>\n\n";
$reply .= "<i>Nilai harus berupa angka tanpa titik atau koma</i>";

$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '🔙 Batal', 'callback_data' => 'settings_campaign']
	]
]);

$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, 'HTML', $keyboard);

updateUserPosition($chat_id, 'settings_edit_' . $setting_type);
?>
