<?php
require_once __DIR__ . '/../helpers/username-validator.php';
require_once __DIR__ . '/../helpers/error-handler.php';

if ($cb_data == "/tambah_medsos") {
    $update_result = updateUserPosition($chat_id, 'tambah_medsos');

    if (!$update_result) {
        $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
        return;
    }

    $reply = "<b>➕ Tambah Akun Media Sosial</b>\n\n";
    $reply .= "Pilih platform media sosial yang ingin Anda hubungkan:";

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
        $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
        return;
    }

    $reply = "📷 <b>Hubungkan Instagram</b>\n\n";
    $reply .= "Silakan kirimkan <b>username Instagram</b> Anda (tanpa @).\n";
    $reply .= "Contoh: <code>jokowi</code>";

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
        $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
        return;
    }

    $reply = "🎵 <b>Hubungkan TikTok</b>\n\n";
    $reply .= "Silakan kirimkan <b>username TikTok</b> Anda (tanpa @).\n";
    $reply .= "Contoh: <code>jokowi</code>";

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
        sendErrorWithBackButton($bot, $chat_id, $msg_id, $validation['message'], '/add_instagram', '🔙 Kembali');
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
    	// Saving notification
        $reply = "⏳ <b>Sedang menyimpan akun Instagram...</b>\n\n📷 Username: @{$username_input}";

        updateUserPosition($chat_id, 'social');

        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $reply);

		// Edit message (and save msg_id)
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

            sleep(3);

            // Get social accounts for display
            $reply = "<b>📱 Kelola Akun Media Sosial</b>\n\n";
            $reply .= "Berikut adalah daftar akun yang telah Anda hubungkan:\n\n";
            $social_accounts = db_query("SELECT platform, username, account_url, status "
                ."FROM smm_social_accounts "
                ."WHERE user_id = ? AND status = 'active' "
                ."ORDER BY platform, created_at", [$user_id]);

            if (count($social_accounts) > 0) {
                $current_platform = '';
                foreach ($social_accounts as $account) {
                    if ($current_platform !== $account['platform']) {
                        if ($current_platform !== '') {
                            $reply .= "\n";
                        }
                        $reply .= ucfirst($account['platform']) . "\n\n";
                        $current_platform = $account['platform'];
                    }
                    $account_url = $account["account_url"];
                    $remove_https = explode("//", $account_url);
                    $display_url = $remove_https[1];
                    $reply .= "- " .$display_url. "\n";
                }
                $reply .= "\n";
            } else {
                $reply .= "⚠️ <i>Belum ada akun terhubung.</i>\n";
                $reply .= "Hubungkan akun media sosial Anda untuk mulai mengambil tugas.\n\n";
            }

            $reply .= "👇 Gunakan menu di bawah ini:";

            $keyboard = $bot->buildInlineKeyboard([
			    [
			        ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
			    ],
			    [
			        ['text' => '🎛️ Edit/Hapus Medsos', 'callback_data' => '/edit_medsos'],
			    ],
			    [
			        ['text' => '🔙 Kembali', 'callback_data' => '/start']
			    ]
            ]);

            $bot->editMessage($chat_id, $new_msg_id, $reply, 'HTML', $keyboard);
        }
    } else {
        sendErrorWithBackButton($bot, $chat_id, $msg_id, "❌ Gagal menambahkan Instagram. Silakan coba lagi.", '/tambah_medsos', '🔙 Kembali');
    }
}

// Handle Add Tiktok
if (!$cb_data && $user[0]['menu'] == 'add_tiktok') {
	// validate username
    $validation = validateUsername($message, 'tiktok');

    if (!$validation['valid']) {
        sendErrorWithBackButton($bot, $chat_id, $msg_id, $validation['message'], '/add_tiktok', '🔙 Kembali');
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
    	// Saving notification
        $reply = "⏳ <b>Sedang menyimpan akun TikTok...</b>\n\n🎵 Username: @{$username_input}";

        updateUserPosition($chat_id, 'social');

        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $reply);

		// Edit msg (and save msg_id)
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

            sleep(3);

            // Get social accounts for display
            $reply = "<b>📱 Kelola Akun Media Sosial</b>\n\n";
            $reply .= "Berikut adalah daftar akun yang telah Anda hubungkan:\n\n";
            $social_accounts = db_query("SELECT platform, username, account_url, status "
                ."FROM smm_social_accounts "
                ."WHERE user_id = ? AND status = 'active' "
                ."ORDER BY platform, created_at", [$user_id]);

            if (count($social_accounts) > 0) {
                $current_platform = '';
                foreach ($social_accounts as $account) {
                    if ($current_platform !== $account['platform']) {
                        if ($current_platform !== '') {
                            $reply .= "\n";
                        }
                        $reply .= ucfirst($account['platform']) . "\n\n";
                        $current_platform = $account['platform'];
                    }
                    $account_url = $account["account_url"];
                    $remove_https = explode("//", $account_url);
                    $display_url = $remove_https[1];
                    $reply .= "- " .$display_url. "\n";
                }
                $reply .= "\n";
            } else {
                $reply .= "⚠️ <i>Belum ada akun terhubung.</i>\n";
                $reply .= "Hubungkan akun media sosial Anda untuk mulai mengambil tugas.\n\n";
            }

            $reply .= "👇 Gunakan menu di bawah ini:";

            $keyboard = $bot->buildInlineKeyboard([
			    [
			        ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
			    ],
			    [
			        ['text' => '🎛️ Edit/Hapus Medsos', 'callback_data' => '/edit_medsos'],
			    ],
			    [
			        ['text' => '🔙 Kembali', 'callback_data' => '/start']
			    ]
            ]);

            $bot->editMessage($chat_id, $new_msg_id, $reply, 'HTML', $keyboard);
        }
    } else {
        sendErrorWithBackButton($bot, $chat_id, $msg_id, "❌ Gagal menambahkan TikTok. Silakan coba lagi.", '/tambah_medsos', '🔙 Kembali');
    }
}

?>
