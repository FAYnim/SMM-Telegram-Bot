<?php

$chat_id = $bot->getChatId();

// Check admin permission
if (!hasPermission($chat_id, 'settings_referral')) {
	$bot->sendMessage($chat_id, "❌ Anda tidak memiliki akses ke menu ini");
	return;
}

// Get current settings
$mandatory_setting = db_read('smm_settings', [
	'category' => 'referral',
	'setting_key' => 'mandatory'
]);

$current_value = $mandatory_setting[0]['setting_value'] ?? 'no';

$new_value = ($current_value == 'yes') ? 'no' : 'yes';

$update_result = db_update('smm_settings', 
	['setting_value' => $new_value], 
	[
		'category' => 'referral',
		'setting_key' => 'mandatory'
	]
);

if ($update_result === false || is_string($update_result)) {
	$bot->sendMessage($chat_id, "❌ Gagal mengubah pengaturan!\n\nSilakan coba lagi.");
	
	logMessage('settings_error', [
		'chat_id' => $chat_id,
		'action' => 'toggle_referral_mandatory',
		'error' => $update_result
	], 'info');
	
	return;
}

logMessage('settings_update', [
	'chat_id' => $chat_id,
	'setting' => 'referral.mandatory',
	'old_value' => $current_value,
	'new_value' => $new_value
], 'info');

// Get reward setting
$reward_setting = db_read('smm_settings', [
	'category' => 'referral',
	'setting_key' => 'reward_amount'
]);

$reward_amount = $reward_setting[0]['setting_value'] ?? '5000';

// Format display
$mandatory_text = ($new_value == 'yes') ? '✅ Ya' : '❌ Tidak';
$reward_formatted = number_format($reward_amount, 0, ',', '.');

// Build message (same as settings-referral.php)
$reply = "🎁 <b>Pengaturan Referral</b>\n\n";
$reply .= "📋 <b>Konfigurasi Saat Ini:</b>\n\n";
$reply .= "🔐 <b>Wajib Kode Referral:</b> " . $mandatory_text . "\n";
$reply .= "<i>User baru harus menggunakan kode referral untuk mendaftar</i>\n\n";
$reply .= "💰 <b>Reward Referral:</b> " . $reward_formatted . "\n";
$reply .= "<i>Reward yang diberikan kepada referrer per user baru</i>\n\n";
$reply .= "━━━━━━━━━━━━━━━━━━━━\n\n";
$reply .= "<i>Pilih pengaturan yang ingin diubah:</i>";

// Build keyboard (same as settings-referral.php)
$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '🔐 Wajib Referral: ' . ($new_value == 'yes' ? '✅' : '❌'), 'callback_data' => 'settings_edit_referral_mandatory']
	],
	[
		['text' => '💰 Ubah Reward', 'callback_data' => 'settings_edit_referral_reward']
	],
	[
		['text' => '🔙 Kembali', 'callback_data' => 'settings']
	]
]);

$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, 'HTML', $keyboard);
?>
