<?php

if ($cb_data == "/tambah_medsos") {
    $update_result = updateUserPosition($chat_id, 'tambah_medsos');

    if (!$update_result) {
        $bot->sendMessage($chat_id, "❌ Something Error!");
        return;
    }

    $reply = "Pilih Medsos yang ingin ditambahkan!";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '📷 Instagram', 'callback_data' => '/add_instagram'],
            ['text' => '🎵 TikTok', 'callback_data' => '/add_tiktok']
        ],
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/social']
        ]
    ]);

    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
}

if ($cb_data == "/add_instagram") {
    $update_result = updateUserPosition($chat_id, 'add_instagram');

    if (!$update_result) {
        $bot->sendMessage($chat_id, "❌ Something Error!");
        return;
    }

    $reply = "📷 <b>Tambah Instagram</b>\n\nSilakan masukkan username Instagram yang ingin ditambahkan:";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/tambah_medsos']
        ]
    ]);

    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
}

if ($cb_data == "/add_tiktok") {
    $update_result = updateUserPosition($chat_id, 'add_tiktok');

    if (!$update_result) {
        $bot->sendMessage($chat_id, "❌ Something Error!");
        return;
    }

    $reply = "🎵 <b>Tambah TikTok</b>\n\nSilakan masukkan username TikTok yang ingin ditambahkan:";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/tambah_medsos']
        ]
    ]);

    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
}

// Handle Add Instagram
if (!$cb_data && $user[0]['menu'] == 'add_instagram') {
    $username_input = trim($message);
    
    if (empty($username_input)) {
        $bot->sendMessage($chat_id, "❌ Username tidak boleh kosong!");
        return;
    }
    
    // Remove @ if user enters @username
    if (strpos($username_input, '@') === 0) {
        $username_input = substr($username_input, 1);
    }
    
    // Check if username already exists for this platform
    $existing_username = db_read('smm_social_accounts', [
        'platform' => 'instagram',
        'username' => $username_input
    ]);
    
    if (!empty($existing_username)) {
        $error_reply = "❌ Username @{$username_input} sudah digunakan oleh pengguna lain. Silakan gunakan username lain.";
        
        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $error_reply);
        
        // Save msg_id and show social menu after 3 seconds
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
            
            sleep(3);
            
            // Get social accounts for display
            $social_reply = "Media sosialmu:\n\n";
            $social_accounts = db_query("SELECT platform, username, account_url, status "
                ."FROM smm_social_accounts "
                ."WHERE user_id = ? AND status = 'active' "
                ."ORDER BY platform, created_at", [$user_id]);
            
            if (count($social_accounts) > 0) {
                $platform_icons = [
                    'instagram' => '📷',
                    'tiktok' => '🎵'
                ];
                
                foreach ($social_accounts as $account) {
                    $icon = $platform_icons[$account['platform']] ?? '🌐';
                    $social_reply .= $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'] . "\n";
                }
                $social_reply .= "\n";
            } else {
                $social_reply .= "📝 Belum ada akun media sosial yang ditambahkan\n\n";
            }
            
            $social_reply .= "Pilih menu di bawah:";
            
            $social_keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
                    ['text' => '🔙 Kembali', 'callback_data' => '/start']
                ]
            ]);
            
            $bot->editMessage($chat_id, $new_msg_id, $social_reply, 'HTML', $social_keyboard);
        }
        return;
    }
    
    $insert_data = [
        'user_id' => $user_id,
        'platform' => 'instagram',
        'username' => $username_input,
        'account_url' => 'https://instagram.com/' . $username_input,
        'status' => 'active'
    ];
    $result = db_create('smm_social_accounts', $insert_data);
    $action_text = "ditambahkan";
    
    if ($result) {
    	// Success Update/Insert
        $reply = "✅ <b>Instagram berhasil {$action_text}!</b>\n\n📷 Username: @{$username_input}\n\nTerima kasih! Akun Instagram Anda telah {$action_text} ke sistem.";
        
        updateUserPosition($chat_id, 'social');
        
        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $reply);
        
        // Save new msg_id to database
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
            
            // Wait 2 seconds then show social menu
            sleep(3);
            
            // Get social accounts for display
            $social_reply = "Media sosialmu:\n\n";
            $social_accounts = db_query("SELECT platform, username, account_url, status "
                ."FROM smm_social_accounts "
                ."WHERE user_id = ? AND status = 'active' "
                ."ORDER BY platform, created_at", [$user_id]);
            
            if (count($social_accounts) > 0) {
                $platform_icons = [
                    'instagram' => '📷',
                    'tiktok' => '🎵'
                ];
                
                foreach ($social_accounts as $account) {
                    $icon = $platform_icons[$account['platform']] ?? '🌐';
                    $social_reply .= $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'] . "\n";
                }
                $social_reply .= "\n";
            } else {
                $social_reply .= "📝 Belum ada akun media sosial yang ditambahkan\n\n";
            }
            
            $social_reply .= "Pilih menu di bawah:";
            
            $social_keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
                    ['text' => '🔙 Kembali', 'callback_data' => '/start']
                ]
            ]);
            
            $bot->editMessage($chat_id, $new_msg_id, $social_reply, 'HTML', $social_keyboard);
        }
    } else {
        $bot->sendMessage($chat_id, "❌ Gagal menambahkan Instagram. Silakan coba lagi.");
    }
}

// Handle Add Tiktok
if (!$cb_data && $user[0]['menu'] == 'add_tiktok') {
    $username_input = trim($message);
    
    if (empty($username_input)) {
        $bot->sendMessage($chat_id, "❌ Username tidak boleh kosong!");
        return;
    }
    
    // Remove @ if user enters @username
    if (strpos($username_input, '@') === 0) {
        $username_input = substr($username_input, 1);
    }
    
    // Check if username already exists for this platform
    $existing_username = db_read('smm_social_accounts', [
        'platform' => 'tiktok',
        'username' => $username_input
    ]);
    
    if (!empty($existing_username)) {
        $error_reply = "❌ Username @{$username_input} sudah digunakan oleh pengguna lain. Silakan gunakan username lain.";
        
        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $error_reply);
        
        // Save msg_id and show social menu after 3 seconds
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
            
            sleep(3);
            
            // Get social accounts for display
            $social_reply = "Media sosialmu:\n\n";
            $social_accounts = db_query("SELECT platform, username, account_url, status "
                ."FROM smm_social_accounts "
                ."WHERE user_id = ? AND status = 'active' "
                ."ORDER BY platform, created_at", [$user_id]);
            
            if (count($social_accounts) > 0) {
                $platform_icons = [
                    'instagram' => '📷',
                    'tiktok' => '🎵'
                ];
                
                foreach ($social_accounts as $account) {
                    $icon = $platform_icons[$account['platform']] ?? '🌐';
                    $social_reply .= $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'] . "\n";
                }
                $social_reply .= "\n";
            } else {
                $social_reply .= "📝 Belum ada akun media sosial yang ditambahkan\n\n";
            }
            
            $social_reply .= "Pilih menu di bawah:";
            
            $social_keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
                    ['text' => '🔙 Kembali', 'callback_data' => '/start']
                ]
            ]);
            
            $bot->editMessage($chat_id, $new_msg_id, $social_reply, 'HTML', $social_keyboard);
        }
        return;
    }
    
    $insert_data = [
        'user_id' => $user_id,
        'platform' => 'tiktok',
        'username' => $username_input,
        'account_url' => 'https://tiktok.com/@' . $username_input,
        'status' => 'active'
    ];
    $result = db_create('smm_social_accounts', $insert_data);
    $action_text = "ditambahkan";
    
    if ($result) {
    	// Success Update/Insert
        $reply = "✅ <b>TikTok berhasil {$action_text}!</b>\n\n🎵 Username: @{$username_input}\n\nTerima kasih! Akun TikTok Anda telah {$action_text} ke sistem.";
        
        updateUserPosition($chat_id, 'social');
        
        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $reply);
        
        // Save new msg_id to database
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
            
            // Wait 2 seconds then show social menu
            sleep(3);
            
            // Get social accounts for display
            $social_reply = "Media sosialmu:\n\n";
            $social_accounts = db_query("SELECT platform, username, account_url, status "
                ."FROM smm_social_accounts "
                ."WHERE user_id = ? AND status = 'active' "
                ."ORDER BY platform, created_at", [$user_id]);
            
            if (count($social_accounts) > 0) {
                $platform_icons = [
                    'instagram' => '📷',
                    'tiktok' => '🎵'
                ];
                
                foreach ($social_accounts as $account) {
                    $icon = $platform_icons[$account['platform']] ?? '🌐';
                    $social_reply .= $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'] . "\n";
                }
                $social_reply .= "\n";
            } else {
                $social_reply .= "📝 Belum ada akun media sosial yang ditambahkan\n\n";
            }
            
            $social_reply .= "Pilih menu di bawah:";
            
            $social_keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
                    ['text' => '🔙 Kembali', 'callback_data' => '/start']
                ]
            ]);
            
            $bot->editMessage($chat_id, $new_msg_id, $social_reply, 'HTML', $social_keyboard);
        }
    } else {
        $bot->sendMessage($chat_id, "❌ Gagal menambahkan TikTok. Silakan coba lagi.");
    }
}

?>
