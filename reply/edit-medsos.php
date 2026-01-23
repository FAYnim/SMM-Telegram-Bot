<?php
require_once __DIR__ . '/../helpers/username-validator.php';
require_once __DIR__ . '/../helpers/error-handler.php';

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

        $reply = "📋 <b>Detail Akun</b>\n\n" . 
                "Apa yang ingin Anda lakukan dengan akun ini?\n\n" .
                $icon . " <b>" . ucfirst($account_data['platform']) . "</b>\n" .
                "👤 Username: <code>@" . $account_data['username'] . "</code>";

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
        $error_message = "❌ Akun media sosial tidak ditemukan atau tidak valid.";
        sendErrorWithBackButton($bot, $chat_id, $msg_id, $error_message, '/edit_medsos', '🔙 Kembali ke Daftar Akun');
    }
} else {

/*	if (!$update_result) {
	    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
	    return;
	}*/

	$reply = "🎛️ <b>Kelola Akun Media Sosial</b>\n\nSilakan pilih akun yang ingin Anda ubah atau hapus:";

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
	    $reply = "⚠️ <b>Tidak ada akun terhubung.</b>\n\nAnda belum menghubungkan akun media sosial apapun.";

	    $keyboard = $bot->buildInlineKeyboard([
	        [
	            ['text' => '🔙 Kembali', 'callback_data' => '/social']
	        ]
	    ]);
	}

	$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
}

?>
