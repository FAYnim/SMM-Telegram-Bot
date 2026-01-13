<?php
$update_result = updateUserPosition($chat_id, 'settings_payment', '');

if(!$update_result) {
	$bot->sendMessage($chat_id, '❌ Terjadi kesalahan sistem');
	return;
}

$reply = "💳 <b>Pengaturan Pembayaran</b>\n\n";

$settings = db_read('smm_settings', ['category' => 'payment']);

$payment_data = [];
if(!empty($settings)) {
	foreach($settings as $setting) {
		$payment_data[$setting['setting_key']] = $setting['setting_value'];
	}
}

$reply .= "<b>📱 DANA</b>\n";
$reply .= "📞 Nomor: <code>" . ($payment_data['dana_number'] ?? 'Belum diatur') . "</code>\n";
$reply .= "👤 A/N: " . ($payment_data['dana_name'] ?? 'Belum diatur') . "\n\n";

$reply .= "<b>🛍️ ShopeePay</b>\n";
$reply .= "📞 Nomor: <code>" . ($payment_data['shopeepay_number'] ?? 'Belum diatur') . "</code>\n";
$reply .= "👤 A/N: " . ($payment_data['shopeepay_name'] ?? 'Belum diatur') . "\n\n";

$reply .= "<i>Pilih metode pembayaran yang ingin diubah:</i>";

$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '📱 Ubah DANA', 'callback_data' => 'settings_edit_dana'],
		['text' => '🛍️ Ubah ShopeePay', 'callback_data' => 'settings_edit_shopeepay']
	],
	[
		['text' => '🔙 Kembali', 'callback_data' => 'settings']
	]
]);

if ($cb_data) {
	$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, 'HTML', $keyboard);
} else {
	$bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
}
?>
