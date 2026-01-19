<?php
$update_result = updateUserPosition($chat_id, 'settings_withdraw', '');

if(!$update_result) {
	$bot->sendMessage($chat_id, '❌ Terjadi kesalahan sistem');
	return;
}

$reply = "💸 <b>Pengaturan Withdraw</b>\n\n";

$settings = db_read('smm_settings', ['category' => 'withdraw']);

$withdraw_data = [];
if(!empty($settings)) {
	foreach($settings as $setting) {
		$withdraw_data[$setting['setting_key']] = $setting['setting_value'];
	}
}

$reply .= "<b>📊 Minimum Withdrawal</b>\n";
$reply .= "Nilai: Rp " . number_format(($withdraw_data['min_withdraw'] ?? 0), 0, ',', '.') . "\n\n";

$reply .= "<b>💰 Biaya Admin</b>\n";
$admin_fee_type = $withdraw_data['admin_fee_type'] ?? 'flat';
$admin_fee_value = $withdraw_data['admin_fee'] ?? 0;

if($admin_fee_type == 'percentage') {
	$reply .= "Tipe: Persentase | Nilai: " . $admin_fee_value . "%\n\n";
} else {
	$reply .= "Tipe: Flat | Nilai: Rp " . number_format($admin_fee_value, 0, ',', '.') . "\n\n";
}

$reply .= "<i>Pilih pengaturan yang ingin diubah:</i>";

$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '📊 Ubah Minimum Withdrawal', 'callback_data' => 'settings_edit_min_withdraw'],
		['text' => '💰 Ubah Biaya Admin', 'callback_data' => 'settings_edit_admin_fee']
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
