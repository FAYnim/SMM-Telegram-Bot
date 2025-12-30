<?php

// Handle delete account callback
if($cb_data && strpos($cb_data, '/delete_account_') === 0) {
	// Get accoint id
    $account_id = str_replace('/delete_account_', '', $cb_data);

    // Get account data
    $account = db_read('smm_social_accounts', [
        'id' => $account_id,
        'user_id' => $user_id
    ]);

    if (!empty($account)) {
    	// Konfirmasi
        $account_data = $account[0];
        $platform_icons = [
            'instagram' => '📷',
            'tiktok' => '🎵'
        ];
        $icon = $platform_icons[$account_data['platform']] ?? '🌐';

        $reply = "⚠️ <b>Konfirmasi Hapus Akun</b>\n\n" .
                $icon . " " . ucfirst($account_data['platform']) . ": @" . $account_data['username'] . "\n\n" .
                "Apakah Anda yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan.";

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '✅ Ya, Hapus', 'callback_data' => '/confirm_delete_' . $account_id],
                ['text' => '❌ Batal', 'callback_data' => '/edit_account_' . $account_id]
            ]
        ]);
        $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    } else {
        $error_reply = "❌ Akun media sosial tidak ditemukan atau tidak valid.";
        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $error_reply);

        // Save msg_id
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

            sleep(3);

            // Rebuild account list
            $social_accounts = db_query("SELECT id, platform, username, account_url, status "
                ."FROM smm_social_accounts "
                ."WHERE user_id = ? AND status = 'active' "
                ."ORDER BY platform, created_at", [$user_id]);

            $list_reply = "Media sosial mana yang mau di-edit?";

            if (count($social_accounts) > 0) {
                $platform_icons = [
                    'instagram' => '📷',
                    'tiktok' => '🎵'
                ];

                $keyboard_buttons = [];

                foreach ($social_accounts as $account) {
                    $icon = $platform_icons[$account['platform']] ?? '🌐';
                    $display_text = $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'];
                    $callback_data = '/edit_account_' . $account['id'];

                    $keyboard_buttons[] = [$display_text, $callback_data];
                }

                // Add back
                $keyboard_buttons[] = ['🔙 Kembali', '/social'];

                // Build keyboard with all accounts
                $list_keyboard = [];
                foreach ($keyboard_buttons as $button) {
                    $list_keyboard[] = [
                        ['text' => $button[0], 'callback_data' => $button[1]]
                    ];
                }
            } else {
                $list_reply .= "\n\n📝 Belum ada akun media sosial yang ditambahkan";

                $list_keyboard = $bot->buildInlineKeyboard([
                    [
                        ['text' => '🔙 Kembali', 'callback_data' => '/social']
                    ]
                ]);
            }
            $bot->editMessage($chat_id, $new_msg_id, $list_reply, 'HTML', $list_keyboard);
        }
    }
}

// Handle confirm delete callback
if($cb_data && strpos($cb_data, '/confirm_delete_') === 0) {
    // get account id
    $account_id = str_replace('/confirm_delete_', '', $cb_data);

    // Get account data
    $account = db_read('smm_social_accounts', [
        'id' => $account_id,
        'user_id' => $user_id
    ]);

    if (!empty($account)) {
        $account_data = $account[0];
        $platform_icons = [
            'instagram' => '📷',
            'tiktok' => '🎵'
        ];
        $icon = $platform_icons[$account_data['platform']] ?? '🌐';

        // Delete the account from database
        $delete_result = db_delete('smm_social_accounts', [
            'id' => $account_id,
            'user_id' => $user_id
        ]);

        if ($delete_result) {
            $reply = "✅ <b>Akun Berhasil Dihapus!</b>\n\n" .
                    $icon . " " . ucfirst($account_data['platform']) . ": @" . $account_data['username'] . "\n\n" .
                    "Akun media sosial telah dihapus dari sistem.";

            $keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '🔙 Kembali ke Menu Sosial', 'callback_data' => '/social']
                ]
            ]);

            $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
        } else {
            $reply = "❌ Gagal menghapus akun. Silakan coba lagi.";

            $keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '🔙 Kembali', 'callback_data' => '/edit_account_' . $account_id]
                ]
            ]);

            $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
        }
    } else {
        $error_reply = "❌ Akun media sosial tidak ditemukan atau tidak valid.";
        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $error_reply);

        // Save msg_id
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

            sleep(3);

            // Rebuild account list
            $social_accounts = db_query("SELECT id, platform, username, account_url, status "
                ."FROM smm_social_accounts "
                ."WHERE user_id = ? AND status = 'active' "
                ."ORDER BY platform, created_at", [$user_id]);

            $list_reply = "Media sosial mana yang mau di-edit?";

            if (count($social_accounts) > 0) {
                $platform_icons = [
                    'instagram' => '📷',
                    'tiktok' => '🎵'
                ];

                $keyboard_buttons = [];

                foreach ($social_accounts as $account) {
                    $icon = $platform_icons[$account['platform']] ?? '🌐';
                    $display_text = $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'];
                    $callback_data = '/edit_account_' . $account['id'];

                    $keyboard_buttons[] = [$display_text, $callback_data];
                }

                // back
                $keyboard_buttons[] = ['🔙 Kembali', '/social'];

                // Build keyboard with all accounts
                $list_keyboard = [];
                foreach ($keyboard_buttons as $button) {
                    $list_keyboard[] = [
                        ['text' => $button[0], 'callback_data' => $button[1]]
                    ];
                }
            } else {
                $list_reply .= "\n\n📝 Belum ada akun media sosial yang ditambahkan";
                $list_keyboard = $bot->buildInlineKeyboard([
                    [
                        ['text' => '🔙 Kembali', 'callback_data' => '/social']
                    ]
                ]);
            }
            $bot->editMessage($chat_id, $new_msg_id, $list_reply, 'HTML', $list_keyboard);
        }
    }
}

?>
