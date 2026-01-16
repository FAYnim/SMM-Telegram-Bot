<?php

    // Update posisi user ke main
	if($menu != "main") {
		$update_result = updateUserPosition($chat_id, 'main');
		
		if (!$update_result) {
			$bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
			return;
		}
	}
    
    $full_name = trim($first_name . ' ' . $last_name);
    $reply = "👋 Halo <b>" . $full_name . "</b>!\n\n";

    if ($role == 'user') {
        $reply .= "Selamat datang di <b>SMM Bot Marketplace</b>.\n"
            . "Platform penghubung Advertiser dan Worker untuk boosting media sosial.\n\n"
            . "👇 <b>Menu Utama:</b>";
            
        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '📢 Campaignku', 'callback_data' => '/cek_campaign'],
                ['text' => '💼 Cari Cuan', 'callback_data' => '/task']
            ],
            [
                ['text' => '💰 Saldo Campaign', 'callback_data' => '/cek_saldo'],
                ['text' => '💸 Tarik Dana', 'callback_data' => '/withdraw']
            ],
            [
                ['text' => '👤 Akun Medsos', 'callback_data' => '/social'],
                ['text' => 'ℹ️ Bantuan', 'callback_data' => '/help']
            ]
        ]);
} elseif ($role == 'admin') {
        $reply .= "⚙️ <b>Panel Admin</b>\n\n"
            . "Silakan pilih menu manajemen di bawah ini:";
            
        $keyboard = $bot->buildInlineKeyboard(getAdminMenu($chat_id));
    }
    
    // Check if this is callback or message
if ($cb_data) {
    // Callback: edit existing message
    $bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, 'HTML', $keyboard);
} else {
    // Message: send new message
    $sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);
    
    if ($sent_message && isset($sent_message['result']['message_id'])) {
        $msg_id = $sent_message['result']['message_id'];
        db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
    }
}

?>
