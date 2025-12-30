<?php

$update_result = updateUserPosition($chat_id, 'social');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

$reply = "Media sosialmu:\n\n";

// System Logic
// Get user's social media accounts
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

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
