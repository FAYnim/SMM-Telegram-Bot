<?php
require_once __DIR__ . '/../helpers/username-validator.php';

$update_result = updateUserPosition($chat_id, 'edit_medsos');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

$reply = "Media sosial mana yang mau di-edit?";

// Get user's social media accounts
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
    
    // Add back button
    $keyboard_buttons[] = ['🔙 Kembali', '/social'];
    
    // Build keyboard with all accounts
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

?>
