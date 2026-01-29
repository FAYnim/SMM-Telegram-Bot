<?php

$chat_id = $bot->getChatId();

// Check admin permission
if (!hasPermission($chat_id, 'settings_referral')) {
	$bot->answerCallbackQuery($bot->getCallbackQueryId(), "❌ Anda tidak memiliki akses ke menu ini");
	return;
}

$update_result = updateUserPosition($chat_id, 'settings_referral', '');

if(!$update_result) {
	$bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
	return;
}

// Read current settings
$mandatory_setting = db_read('smm_settings', [
	'category' => 'referral',
	'setting_key' => 'mandatory'
]);

$reward_setting = db_read('smm_settings', [
	'category' => 'referral',
	'setting_key' => 'reward_amount'
]);

$mandatory = $mandatory_setting[0]['setting_value'] ?? 'no';
$reward_amount = $reward_setting[0]['setting_value'] ?? '5000';

// Format display
$mandatory_text = ($mandatory == 'yes') ? '✅ Ya' : '❌ Tidak';
$reward_formatted = number_format($reward_amount, 0, ',', '.');

// Build message
$reply = "🎁 <b>Pengaturan Referral</b>\n\n";
$reply .= "📋 <b>Konfigurasi Saat Ini:</b>\n\n";
$reply .= "🔐 <b>Wajib Kode Referral:</b> " . $mandatory_text . "\n";
$reply .= "<i>User baru harus menggunakan kode referral untuk mendaftar</i>\n\n";
$reply .= "💰 <b>Reward Referral:</b> " . $reward_formatted . "\n";
$reply .= "<i>Reward yang diberikan kepada referrer per user baru</i>\n\n";
$reply .= "━━━━━━━━━━━━━━━━━━━━\n\n";
$reply .= "<i>Pilih pengaturan yang ingin diubah:</i>";

// Build keyboard
$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '🔐 Wajib Referral: ' . ($mandatory == 'yes' ? '✅' : '❌'), 'callback_data' => 'settings_edit_referral_mandatory']
	],
	[
		['text' => '💰 Ubah Reward', 'callback_data' => 'settings_edit_referral_reward']
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
