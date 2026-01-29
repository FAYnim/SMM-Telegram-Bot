<?php

// Extract referral code
$referral_code_param = null;

if ($message && strpos($message, '/start ') === 0) {
    $parts = explode(' ', $message, 2);
    
    if (isset($parts[1]) && !empty(trim($parts[1]))) {
        $referral_code_param = strtoupper(trim($parts[1]));
    }
}

// Check if user is unregistered and needs referral
if ($user[0]['status'] == 'unregistered') {
    // Read referral mandatory setting
    $mandatory_setting = db_read('smm_settings', [
        'category' => 'referral',
        'setting_key' => 'mandatory'
    ]);
    $referral_mandatory = $mandatory_setting[0]['setting_value'] ?? 'no';
    
    if ($referral_mandatory == 'yes' && empty($referral_code_param)) {
		// referral mandatory and referral code doesnt exist
        $error_text = "❌ <b>Kode Referral Diperlukan</b>\n\n";
        $error_text .= "Untuk menggunakan bot ini, kamu harus memiliki kode referral dari pengguna lain.\n\n";
        $error_text .= "<b>Cara Mendapatkan Kode:</b>\n";
        $error_text .= "1️⃣ Minta teman yang sudah terdaftar untuk share link referral mereka\n";
        $error_text .= "2️⃣ Klik link tersebut untuk bergabung\n\n";
        $error_text .= "<i>Jika kamu sudah punya kode, gunakan format:</i>\n";
        $error_text .= "<code>/start KODE_REFERRAL</code>";
        
        $bot->sendMessage($chat_id, $error_text, null, 'HTML');
        
        logMessage('referral_enforcement', [
            'chat_id' => $chat_id,
            'username' => $user[0]['username'],
            'status' => 'blocked',
            'reason' => 'no_referral_code_mandatory_enabled'
        ], 'info');
        
        return;
    }
}

// If user already registered (active/suspended), bypass and go to start menu
if (in_array($user[0]['status'], ['active', 'suspended'])) {
    include 'reply/start.php';
    return;
}

// Get referral settings from database
$referral_mandatory_setting = db_read('smm_settings', [
    'category' => 'referral',
    'setting_key' => 'mandatory'
]);

$referral_reward_setting = db_read('smm_settings', [
    'category' => 'referral',
    'setting_key' => 'reward_amount'
]);

if (!empty($referral_mandatory_setting)) {
    $referral_mandatory = $referral_mandatory_setting[0]['setting_value'];
} else {
    $referral_mandatory = 'no';
}

if (!empty($referral_reward_setting)) {
    $referral_reward_amount = (int)$referral_reward_setting[0]['setting_value'];
} else {
    $referral_reward_amount = 5000;
}

logMessage('referral_settings', [
    'chat_id' => $chat_id,
    'user_id' => $user_id,
    'mandatory' => $referral_mandatory,
    'reward_amount' => $referral_reward_amount,
    'has_code_param' => $referral_code_param ? true : false
], 'debug');

$referral_return_code = 200;

// Check if user has already been referred
// referrer_id       -- User yang MEMBAGIKAN kode (yang ngajak)
// referred_user_id  -- User yang MENGGUNAKAN kode (yang diajak)
$existing_referral = db_read('smm_referrals', ['referred_user_id' => $user_id]);

if (!empty($existing_referral)) {
    // User has already been invited, skip referral process
    $referral_return_code = 400;
}

if($referral_return_code == 200) {
	
    // Check referral code in database
    if ($referral_code_param) {
        $referral_code_data = db_read('smm_referral_codes', ['code' => $referral_code_param]);
        
        logMessage('referral_code_check', [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
            'referral_code' => $referral_code_param,
            'code_found' => !empty($referral_code_data),
            'code_data' => $referral_code_data
        ], 'debug');
        
        if (empty($referral_code_data)) {
            // Referral code not found
            $referral_return_code = 400;
            
			$error_reply = "❌ <b>Kode Referral Tidak Valid</b>\n\n"
				."Kode referral yang Anda masukkan tidak ditemukan.\n\n"
				."Silakan gunakan kode yang valid";
			
            $bot->sendMessage($chat_id, $error_reply, null, 'HTML');
            
            logMessage('referral_code_invalid', [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'referral_code' => $referral_code_param
            ], 'info');
            
        } else {
            // Referral code is valid
            $referrer_id = $referral_code_data[0]['user_id'];
            
            logMessage('referral_code_valid', [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'referral_code' => $referral_code_param,
                'referrer_id' => $referrer_id
            ], 'info');
        }
    }

}

// Check if referral code has already been used
if($referral_return_code == 200) {
    
    if ($referral_code_param && isset($referral_code_data) && !empty($referral_code_data)) {
        // Get code info to check is_custom
        $is_custom = $referral_code_data[0]['is_custom'] ?? 0;
        
        // CUSTOM CODE: Hanya bisa dipakai 1x (check usage)
        // AUTO-GENERATED CODE: Bisa dipakai unlimited (skip check)
        if ($is_custom == 1) {
            // Check if this custom code has been used before
            $existing_code_usage = db_read('smm_referrals', ['referral_code' => $referral_code_param]);
            
            logMessage('referral_code_usage_check', [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'referral_code' => $referral_code_param,
                'is_custom' => true,
                'already_used' => !empty($existing_code_usage),
                'usage_count' => count($existing_code_usage)
            ], 'debug');
            
            if (!empty($existing_code_usage)) {
                // Custom code has already been used
                $referral_return_code = 400;
                
                $error_text = "❌ <b>Kode Tidak Valid</b>\n\n";
                $error_text .= "Kode custom <code>" . htmlspecialchars($referral_code_param) . "</code> sudah pernah digunakan oleh user lain.\n\n";
                $error_text .= "<i>Minta kode lain dari teman kamu atau gunakan kode default mereka.</i>";
                
                $bot->sendMessage($chat_id, $error_text, null, 'HTML');
                
                logMessage('referral_custom_code_already_used', [
                    'chat_id' => $chat_id,
                    'user_id' => $user_id,
                    'referral_code' => $referral_code_param,
                    'code_type' => 'custom',
                    'used_by_user_id' => $existing_code_usage[0]['referred_user_id'],
                    'used_at' => $existing_code_usage[0]['created_at']
                ], 'info');
            }
        } else {
            // Auto-generated code: unlimited usage, skip check
            logMessage('referral_code_usage_check', [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'referral_code' => $referral_code_param,
                'is_custom' => false,
                'check_skipped' => true,
                'reason' => 'auto_generated_code_unlimited_usage'
            ], 'debug');
        }
    }

}

// Check Self-referral
if($referral_return_code == 200) {

    // Check if referral code and referrer_id (invitor) exist
    if ($referral_code_param && isset($referrer_id)) {
        
        logMessage('referral_self_check', [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
            'referrer_id' => $referrer_id,
            'is_self_referral' => ($referrer_id == $user_id)
        ], 'debug');
        
        if ($referrer_id == $user_id) {
            // Self-referral detected
            $referral_return_code = 400;
            
            $error_reply = "❌ <b>Tidak Dapat Menggunakan Kode Sendiri</b>\n\n"
                ."Anda tidak dapat menggunakan kode referral milik Anda sendiri.\n\n"
                ."<i>Silakan gunakan kode referral dari pengguna lain</i>";
            
            $bot->sendMessage($chat_id, $error_reply, null, 'HTML');
            
            logMessage('referral_self_detected', [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'referral_code' => $referral_code_param
            ], 'info');
        }
    }

}

// Process reward
if($referral_return_code == 200 && isset($referrer_id)) {
    
    logMessage('referral_reward_process_start', [
        'chat_id' => $chat_id,
        'user_id' => $user_id,
        'referrer_id' => $referrer_id,
        'referral_code' => $referral_code_param,
        'reward_amount' => $referral_reward_amount,
        'return_code' => $referral_return_code
    ], 'debug');

    // Get referrer wallet
    $referrer_wallet = db_read('smm_wallets', ['user_id' => $referrer_id]);

    logMessage('referral_wallet_check', [
        'referrer_id' => $referrer_id,
        'wallet_found' => !empty($referrer_wallet),
        'wallet_data' => $referrer_wallet
    ], 'debug');
    
    if (!empty($referrer_wallet)) {
        $wallet_id = $referrer_wallet[0]['id'];
        $current_balance = $referrer_wallet[0]['balance'];
        $new_balance = $current_balance + $referral_reward_amount;
        
        // Update wallet balance
        $update_wallet = db_update('smm_wallets', ['balance' => $new_balance], ['id' => $wallet_id]);
        
        if ($update_wallet) {
            // Create wallet transaction record
            $transaction_data = [
                'wallet_id' => $wallet_id,
                'type' => 'deposit',
                'amount' => $referral_reward_amount,
                'balance_before' => $current_balance,
                'balance_after' => $new_balance,
                'description' => 'Reward referral dari user baru',
                'status' => 'approved'
            ];
            db_create('smm_wallet_transactions', $transaction_data);
            
            // Create referral record
            $referral_data = [
                'referrer_id' => $referrer_id,
                'referred_user_id' => $user_id,
                'referral_code' => $referral_code_param,
                'reward_amount' => $referral_reward_amount
            ];
            db_create('smm_referrals', $referral_data);
            
            logMessage('referral_success', [
                'referrer_id' => $referrer_id,
                'referred_user_id' => $user_id,
                'referral_code' => $referral_code_param,
                'reward_amount' => $referral_reward_amount
            ], 'info');
            
            // Update referred user status to 'active'
            $update_status = db_update('smm_users', 
                ['status' => 'active'], 
                ['chatid' => $chat_id]
            );
            
            if (!$update_status) {
                logMessage('referral_activation_error', [
                    'chat_id' => $chat_id,
                    'user_id' => $user_id,
                    'error' => 'failed_to_activate_user',
                    'db_error' => $update_status
                ], 'info');
            } else {
                logMessage('user_activation', [
                    'chat_id' => $chat_id,
                    'username' => $user[0]['username'],
                    'method' => 'referral',
                    'referrer_id' => $referrer_id,
                    'referral_code' => $referral_code_param
                ], 'info');
            }
            
            // Get referrer data
            $referrer_user = db_read('smm_users', ['id' => $referrer_id]);
            
            if (!empty($referrer_user)) {
                $referrer_chatid = $referrer_user[0]['chatid'];
                
                // Send notification to referrer
                $reward_formatted = "Rp " . number_format($referral_reward_amount, 0, ',', '.');
                $notif_referrer = "🎉 <b>Selamat! Reward Referral</b>\n\n"
                    ."Anda mendapatkan reward sebesar <b>{$reward_formatted}</b> "
                    ."karena ada pengguna baru yang menggunakan kode referral Anda.\n\n"
                    ."<i>Reward sudah ditambahkan ke saldo Anda</i>";
                
                // Keyboard untuk tutup notifikasi
                $keyboard_referral = $bot->buildInlineKeyboard([
                    [
                        ['text' => '✖️ Tutup Notifikasi', 'callback_data' => 'close_notif']
                    ]
                ]);
                
                $bot->sendMessageWithKeyboard($referrer_chatid, $notif_referrer, $keyboard_referral, null, 'HTML');
            }
        }
    }
}

// Send greeting message to new user
if($referral_return_code == 200 || $referral_return_code == 400) {
    
    // Update user position to main
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
    
    // Send new message
    $sent_message = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);
    
    if ($sent_message && isset($sent_message['result']['message_id'])) {
        $msg_id = $sent_message['result']['message_id'];
        db_update('smm_users', ['msg_id' => $msg_id], ['chatid' => $chat_id]);
    }
}

?>