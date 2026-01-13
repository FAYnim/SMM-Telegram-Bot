<?php
	$update_result = updateUserPosition($chat_id, 'settings', '');
	
	if(!$update_result) {
		$bot->sendMessage($chat_id, '❌ Terjadi kesalahan sistem');
		return;
	}
	
	$reply = "⚙️ <b>Panel Pengaturan Admin</b>\n\n";
	$reply .= "Silakan pilih kategori pengaturan yang ingin diubah:";
	
	$keyboard = $bot->buildInlineKeyboard([
		[
			['text' => '💳 Topup', 'callback_data' => 'settings_payment'],
			['text' => '💸 Withdraw', 'callback_data' => 'settings_withdraw']
		],
		[
			['text' => '📋 Tugas', 'callback_data' => 'settings_task'],
//			['text' => '📢 Pengaturan Campaign', 'callback_data' => 'settings_campaign']
		],
		[
			['text' => '🔙 Kembali', 'callback_data' => '/start']
		]
	]);
	
	if ($cb_data) {
		$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, 'HTML', $keyboard);
	} else {
		$bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
	}
?>
