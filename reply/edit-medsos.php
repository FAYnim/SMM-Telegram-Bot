<?php
require_once __DIR__ . '/../helpers/username-validator.php';

$update_result = updateUserPosition($chat_id, 'edit_medsos');

// Handle edit account callback
if($cb_data && strpos($cb_data, '/edit_account_') === 0) {
    // get account id
    $account_id = str_replace('/edit_account_', '', $cb_data);

    // Get account data from db
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

        $reply = "Pilih aksi untuk akun ini:\n\n" . 
                $icon . " " . ucfirst($account_data['platform']) . ": @" . $account_data['username'];

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '✏️ Edit Username', 'callback_data' => '/edit_username_' . $account_id],
                ['text' => '🗑️ Hapus Akun', 'callback_data' => '/delete_account_' . $account_id]
            ],
            [
                ['text' => '🔙 Kembali', 'callback_data' => '/edit_medsos']
            ]
        ]);

        $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    } else {
		// not found
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

                // back button
                $keyboard_buttons[] = ['🔙 Kembali', '/social'];

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
} else {

/*	if (!$update_result) {
	    $bot->sendMessage($chat_id, "❌ Something Error!");
	    return;
	}*/

	$reply = "Media sosial mana yang mau di-edit?";

	// Get medsos data
	$social_accounts = db_query("SELECT id, platform, username, account_url, status "
	    ."FROM smm_social_accounts "
	    ."WHERE user_id = ? AND status = 'active' "
		."ORDER BY platform, created_at LIMIT 0,5", [$user_id]);

	if (count($social_accounts) > 0) {
	    $platform_icons = [
	        'instagram' => '📷',
	        'tiktok' => '🎵'
	    ];

	    $keyboard_buttons = [];

	    foreach ($social_accounts as $account) {
	        $icon = $platform_icons[$account['platform']] ?? '🌐';
	//        $display_text = $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'];
	        $display_text = $icon . " " . $account['username'];
	        $callback_data = '/edit_account_' . $account['id'];

	        $keyboard_buttons[] = [$display_text, $callback_data];
	    }

	    // back button
	    $keyboard_buttons[] = ['🔙 Kembali', '/social'];

	    $keyboard = [];
	    foreach ($keyboard_buttons as $button) {
	        $keyboard[] = [
	            ['text' => $button[0], 'callback_data' => $button[1]]
	        ];
	    }
	    $keyboard = $bot->buildInlineKeyboard($keyboard);
	} else {
	    $reply .= "\n\n📝 Belum ada akun media sosial yang ditambahkan";

	    $keyboard = $bot->buildInlineKeyboard([
	        [
	            ['text' => '🔙 Kembali', 'callback_data' => '/social']
	        ]
	    ]);
	}

	$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
}

?>
