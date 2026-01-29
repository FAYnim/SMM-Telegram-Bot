<?php

    // Check if user needs activation (unregistered + mandatory=no)
    if ($user[0]['status'] == 'unregistered') {
        $referral_setting = db_read('smm_settings', [
            'category' => 'referral',
            'setting_key' => 'mandatory'
        ]);
        
        $mandatory = $referral_setting[0]['setting_value'] ?? 'no';
        
        if ($mandatory == 'no') {
            // Auto-activate user karena tidak wajib referral
            $update_result = db_update('smm_users', 
                ['status' => 'active'], 
                ['chatid' => $chat_id]
            );
            
            if (!$update_result) {
                $bot->sendMessage($chat_id, "❌ Terjadi kesalahan saat aktivasi akun!\n\nKetik /start untuk mencoba lagi.");
                logMessage('user_activation_error', [
                    'chat_id' => $chat_id,
                    'username' => $user[0]['username'],
                    'error' => $update_result
                ], 'info');
                return;
            }
            
            logMessage('user_activation', [
                'chat_id' => $chat_id,
                'username' => $user[0]['username'],
                'method' => 'auto_activation',
                'reason' => 'mandatory_referral_disabled'
            ], 'info');
        } else {
			// double check (referral-checker.php)
            $error_text = "❌ <b>Kode Referral Diperlukan</b>\n\n";
            $error_text .= "Untuk menggunakan bot ini, kamu harus memiliki kode referral.\n\n";
            $error_text .= "<i>Gunakan format:</i>\n";
            $error_text .= "<code>/start KODE_REFERRAL</code>";
            
            $bot->sendMessage($chat_id, $error_text, null, 'HTML');
            
            logMessage('referral_enforcement_fallback', [
                'chat_id' => $chat_id,
                'username' => $user[0]['username'],
                'note' => 'User unregistered mencoba akses start tanpa kode, blocked'
            ], 'info');
            
            return;
        }
    }

    // Update posisi user ke main
	if($menu != "main") {
		$update_result = updateUserPosition($chat_id, 'main');
		
		if (!$update_result) {
			$bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
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
                ['text' => '🎁 Referral', 'callback_data' => '/referral']
            ],
            [
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
