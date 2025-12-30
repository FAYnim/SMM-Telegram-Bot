<?php

// Load helpers
require_once __DIR__ . '/../helpers/username-validator.php';
require_once __DIR__ . '/../helpers/error-handler.php';

// Handle edit username callback
if($cb_data && strpos($cb_data, '/edit_username_') === 0) {
    // Extract account ID from callback data
    $account_id = str_replace('/edit_username_', '', $cb_data);

    // Get account details from database
    $account = db_read('smm_social_accounts', [
        'id' => $account_id,
        'user_id' => $user_id
    ]);

    if (!empty($account)) {
        $account_data = $account[0];
        $platform_name = ucfirst($account_data['platform']);

        // Update user position to edit-username
        $update_result = updateUserPosition($chat_id, 'edit_username', $account_id);

        if (!$update_result) {
            $bot->sendMessage($chat_id, "❌ Something Error!");
            return;
        }

        $reply = "Tulis nama username {$platform_name} baru anda!";

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '🔙 Kembali', 'callback_data' => '/edit_account_' . $account_id]
            ]
        ]);

        $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    } else {
        // Account not found - error handling
        handleErrorAndRebuildList($bot, $chat_id, $msg_id, "❌ Akun media sosial tidak ditemukan atau tidak valid.", $user_id);
    }
}

// Handle username input for edit
if(!$cb_data && $user[0]['menu'] == 'edit_username') {
    $username_input = trim($message);
    $account_id = $user[0]['submenu'];

    if (empty($username_input)) {
        $bot->sendMessage($chat_id, "❌ Username tidak boleh kosong!");
        return;
    }

    // Remove @ if user enters @username
    if (strpos($username_input, '@') === 0) {
        $username_input = substr($username_input, 1);
    }

    // check account data
    $current_account = db_read('smm_social_accounts', [
        'id' => $account_id,
        'user_id' => $user_id
    ]);

    if (empty($current_account)) {
        handleErrorAndRebuildList($bot, $chat_id, $msg_id, "❌ Akun media sosial tidak ditemukan atau tidak valid.", $user_id);
        return;
    }

    $current_data = $current_account[0];
    $platform = $current_data['platform'];

    // Check if new username already exists for this platform (excluding current account)
    $existing_username = db_query("SELECT id FROM smm_social_accounts "
        ."WHERE platform = ? AND username = ? AND id != ? "
        ."LIMIT 1", [$platform, $username_input, $account_id]);
    
    if (count($existing_username) > 0) {
        handleErrorAndRebuildList($bot, $chat_id, $msg_id, "❌ Username @{$username_input} sudah digunakan oleh pengguna lain. Silakan gunakan username lain.", $user_id);
        return;
    }

    // Update account URL based on platform
    $account_url = generatePlatformUrl($platform, $username_input);

    // Update username in database
    $update_data = [
        'username' => $username_input,
        'account_url' => $account_url,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $update_result = db_update('smm_social_accounts', $update_data, [
        'id' => $account_id,
        'user_id' => $user_id
    ]);

    if ($update_result) {
        $icon = getPlatformIcon($platform);

        $reply = "✅ <b>Username berhasil diperbarui!</b>\n\n" .
                $icon . " " . ucfirst($platform) . ": @{$username_input}\n\n" .
                "Username {$platform} Anda telah diperbarui.";

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '🔙 Kembali ke Menu Sosial', 'callback_data' => '/social']
            ]
        ]);

        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $reply);

        // Save new msg_id
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

            sleep(3);

            // Show updated social menu
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
                    ['text' => '🎛️ Edit/Hapus Medsos', 'callback_data' => '/edit_medsos'],
                ],
                [
                    ['text' => '🔙 Kembali', 'callback_data' => '/start']
                ]
            ]);

            $bot->editMessage($chat_id, $new_msg_id, $social_reply, 'HTML', $social_keyboard);
        }
    } else {
        handleErrorAndRebuildList($bot, $chat_id, $msg_id, "❌ Gagal memperbarui username. Silakan coba lagi.", $user_id);
    }
}

?>
