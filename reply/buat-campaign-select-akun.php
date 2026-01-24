<?php
require_once 'helpers/error-handler.php';

// Extract account ID from callback data
$account_id = str_replace('/select_account_', '', $cb_data);

// Validate account ID
if (!is_numeric($account_id)) {
    $bot->sendMessage($chat_id, "❌ ID akun tidak valid!");
    return;
}

// Get account data and verify ownership
$account = db_read('smm_social_accounts', ['id' => $account_id, 'user_id' => $user_id, 'status' => 'active']);

if (empty($account)) {
    $bot->sendMessage($chat_id, "❌ Akun tidak ditemukan atau bukan milik Anda!");
    return;
}

$account = $account[0];

// Get campaign data to determine the type
$campaign = db_query(
    "SELECT id, type FROM smm_campaigns WHERE client_id = ? AND status = 'creating' ORDER BY id DESC LIMIT 1",
    [$user_id]
);

if (empty($campaign)) {
    $bot->sendMessage($chat_id, "❌ Campaign tidak ditemukan!");
    return;
}

$campaign_id = $campaign[0]['id'];
$campaign_type = $campaign[0]['type'];

// Update campaign with social_account_id only (link will be inputted manually next)
db_execute(
    "UPDATE smm_campaigns SET social_account_id = ? WHERE id = ?",
    [$account_id, $campaign_id]
);

// Update user position to next step (link input)
$update_result = updateUserPosition($chat_id, 'buat_campaign_link');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
    return;
}

// Show reward input screen
$platform_icons = [
    'instagram' => '📷',
    'tiktok' => '🎵',
    'youtube' => '▶️',
    'twitter' => '🐦',
    'facebook' => '👍'
];

$icon = $platform_icons[$account['platform']] ?? '📱';
$platform_name = ucfirst($account['platform']);

$reply = "<b>📝 Buat Campaign - Link Target</b>\n\n";
$reply .= "Akun terpilih: " . $icon . " <b>" . $platform_name . " - @" . $account['username'] . "</b>\n\n";
$reply .= "Silakan masukkan link target untuk campaign ini:\n\n";

// Give example based on platform
$link_examples = [
    'instagram' => "💡 <i>Contoh: https://www.instagram.com/p/ABC123/</i>",
    'tiktok' => "💡 <i>Contoh: https://www.tiktok.com/@username/video/1234567890</i>",
    'youtube' => "💡 <i>Contoh: https://www.youtube.com/watch?v=ABC123</i>",
    'twitter' => "💡 <i>Contoh: https://twitter.com/username/status/1234567890</i>",
    'facebook' => "💡 <i>Contoh: https://www.facebook.com/username/posts/1234567890</i>"
];

$reply .= $link_examples[$account['platform']] ?? "💡 <i>Contoh: URL lengkap konten Anda</i>";
$reply .= "\n\n🔗 Ketik link target Anda:";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Batal', 'callback_data' => '/cek_campaign']
    ]
]);

// Edit message with new content
$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
