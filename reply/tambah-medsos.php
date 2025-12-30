<?php

// Load username validation helper
require_once __DIR__ . '/../helpers/username-validator.php';

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
	// Validate Username
    $validation = validateUsername($message, 'instagram');

    if (!$validation['valid']) {
        $bot->sendMessage($chat_id, $validation['message']);
        return;
    }

	// Insert username
    $username_input = $validation['username'];

    $insert_data = [
        'user_id' => $user_id,
        'platform' => 'instagram',
        'username' => $username_input,
        'account_url' => generatePlatformUrl('instagram', $username_input),
        'status' => 'active'
    ];
    $result = db_create('smm_social_accounts', $insert_data);
    $action_text = "ditambahkan";

    if ($result) {
    	// Success Update
        $reply = "✅ <b>Instagram berhasil {$action_text}!</b>\n\n📷 Username: @{$username_input}\n\nTerima kasih! Akun Instagram Anda telah {$action_text} ke sistem.";

        updateUserPosition($chat_id, 'social');

        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $reply);

		// Edit message (and save msg_id)
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

            sleep(3);

            // Get social accounts for display
            $reply = "Media sosialmu:\n\n";
            $social_accounts = db_query("SELECT platform, username, account_url, status "
                ."FROM smm_social_accounts "
                ."WHERE user_id = ? AND status = 'active' "
                ."ORDER BY platform, created_at", [$user_id]);

            if (count($social_accounts) > 0) {
                foreach ($social_accounts as $account) {
                    $icon = getPlatformIcon($account['platform']);
                    $reply .= $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'] . "\n";
                }
                $reply .= "\n";
            } else {
                $reply .= "📝 Belum ada akun media sosial yang ditambahkan\n\n";
            }

            $reply .= "Pilih menu di bawah:";

            $keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
                    ['text' => '🔙 Kembali', 'callback_data' => '/start']
                ]
            ]);

            $bot->editMessage($chat_id, $new_msg_id, $reply, 'HTML', $keyboard);
        }
    } else {
        $bot->sendMessage($chat_id, "❌ Gagal menambahkan Instagram. Silakan coba lagi.");
    }
}

// Handle Add Tiktok
if (!$cb_data && $user[0]['menu'] == 'add_tiktok') {
	// validate username
    $validation = validateUsername($message, 'tiktok');

    if (!$validation['valid']) {
        $bot->sendMessage($chat_id, $validation['message']);
        return;
    }

	// Insert username
    $username_input = $validation['username'];

    $insert_data = [
        'user_id' => $user_id,
        'platform' => 'tiktok',
        'username' => $username_input,
        'account_url' => generatePlatformUrl('tiktok', $username_input),
        'status' => 'active'
    ];
    $result = db_create('smm_social_accounts', $insert_data);
    $action_text = "ditambahkan";

    if ($result) {
    	// Success Update
        $reply = "✅ <b>TikTok berhasil {$action_text}!</b>\n\n🎵 Username: @{$username_input}\n\nTerima kasih! Akun TikTok Anda telah {$action_text} ke sistem.";

        updateUserPosition($chat_id, 'social');

        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $reply);

		// Edit msg (and save msg_id)
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

            sleep(3);

            // Get social accounts for display
            $reply = "Media sosialmu:\n\n";
            $social_accounts = db_query("SELECT platform, username, account_url, status "
                ."FROM smm_social_accounts "
                ."WHERE user_id = ? AND status = 'active' "
                ."ORDER BY platform, created_at", [$user_id]);

            if (count($social_accounts) > 0) {
                foreach ($social_accounts as $account) {
                    $icon = getPlatformIcon($account['platform']);
                    $reply .= $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'] . "\n";
                }
                $reply .= "\n";
            } else {
                $reply .= "📝 Belum ada akun media sosial yang ditambahkan\n\n";
            }

            $reply .= "Pilih menu di bawah:";

            $keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
                    ['text' => '🔙 Kembali', 'callback_data' => '/start']
                ]
            ]);

            $bot->editMessage($chat_id, $new_msg_id, $reply, 'HTML', $keyboard);
        }
    } else {
        $bot->sendMessage($chat_id, "❌ Gagal menambahkan TikTok. Silakan coba lagi.");
    }
}

?>
