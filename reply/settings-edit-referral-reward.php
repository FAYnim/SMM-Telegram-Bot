<?php

$chat_id = $bot->getChatId();

if (!hasPermission($chat_id, 'settings_referral')) {
	$bot->sendMessage($chat_id, "❌ Anda tidak memiliki akses ke menu ini");
	return;
}

$update_result = updateUserPosition($chat_id, 'settings_edit_referral_reward', '');

if(!$update_result) {
	$bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
	return;
}

$reward_setting = db_read('smm_settings', [
	'category' => 'referral',
	'setting_key' => 'reward_amount'
]);

$current_reward = $reward_setting[0]['setting_value'] ?? '5000';
$current_reward_formatted = number_format($current_reward, 0, ',', '.');

$reply = "💰 <b>Ubah Reward Referral</b>\n\n";
$reply .= "Nilai saat ini: <b>" . $current_reward_formatted . "</b>\n\n";
$reply .= "Silakan masukkan nominal reward baru (dalam Rupiah):\n\n";
$reply .= "<i>Contoh: 10000</i>\n\n";
$reply .= "<i>Nilai harus berupa angka bulat positif tanpa titik atau koma</i>\n";

$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '🔙 Batal', 'callback_data' => 'settings_referral']
	]
]);

$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, 'HTML', $keyboard);
?>
