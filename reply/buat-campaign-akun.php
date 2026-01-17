<?php
$reply = "<b>📝 Buat Campaign - Pilih Akun Media Sosial</b>\n\n";
$reply .= "Silakan pilih akun media sosial yang akan digunakan untuk campaign ini:\n\n";

// Get user's active social media accounts
$social_accounts = db_query(
    "SELECT id, platform, username, account_url " .
    "FROM smm_social_accounts " .
    "WHERE user_id = ? AND status = 'active' " .
    "ORDER BY platform, created_at",
    [$user_id]
);

if (empty($social_accounts)) {
    $reply .= "⚠️ <i>Anda belum memiliki akun media sosial yang terhubung.</i>\n\n";
    $reply .= "Silakan tambahkan akun media sosial terlebih dahulu melalui menu <b>Akun Media Sosial</b>.";
    
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '➕ Tambah Akun Medsos', 'callback_data' => '/tambah_medsos']
        ],
        [
            ['text' => '🔙 Batal', 'callback_data' => '/cek_campaign']
        ]
    ]);
    
    // Kirim pesan baru dengan keyboard dan dapatkan msg_id baru
    $result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);
    $new_msg_id = $result['result']['message_id'] ?? null;
    
    // Update msg_id baru di database
    if ($new_msg_id) {
        db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
    }
    
    return;
}

// Build keyboard from social accounts
$keyboard_rows = [];

// Group accounts by platform for better display
$platform_icons = [
    'instagram' => '📷',
    'tiktok' => '🎵',
    'youtube' => '▶️',
    'twitter' => '🐦',
    'facebook' => '👍'
];

foreach ($social_accounts as $account) {
    $icon = $platform_icons[$account['platform']] ?? '📱';
    $platform_name = ucfirst($account['platform']);
    $keyboard_rows[] = [
        [
            'text' => $icon . " " . $platform_name . " - @" . $account['username'],
            'callback_data' => "/select_account_" . $account['id']
        ]
    ];
}

// Add cancel button
$keyboard_rows[] = [
    ['text' => '🔙 Batal', 'callback_data' => '/cek_campaign']
];

$keyboard = $bot->buildInlineKeyboard($keyboard_rows);

$reply .= "👇 Pilih salah satu akun di bawah ini:";

// Kirim pesan baru dengan keyboard dan dapatkan msg_id baru
$result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);
$new_msg_id = $result['result']['message_id'] ?? null;

// Update msg_id baru di database
if ($new_msg_id) {
    db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
}

?>
