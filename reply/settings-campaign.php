<?php
$update_result = updateUserPosition($chat_id, 'settings_campaign', '');

if(!$update_result) {
	$bot->sendMessage($chat_id, '❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.');
	return;
}

$reply = "📢 <b>Pengaturan Campaign</b>\n\n";

$settings = db_read('smm_settings', ['category' => 'campaign']);

$campaign_data = [];
if(!empty($settings)) {
	foreach($settings as $setting) {
		$campaign_data[$setting['setting_key']] = $setting['setting_value'];
	}
}

$reply .= "<b>💰 Minimum Harga Per Task</b>\n";
$reply .= "Nilai: " . number_format(($campaign_data['min_price_per_task'] ?? 0), 0, ',', '.') . "\n\n";

$reply .= "<i>Pilih pengaturan yang ingin diubah:</i>";

$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '💰 Ubah Minimum Harga/Task', 'callback_data' => 'settings_edit_min_price_per_task']
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
