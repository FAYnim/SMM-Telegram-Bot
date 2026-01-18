<?php
require_once 'helpers/error-handler.php';

// Validasi input
$link = trim($message);
if (empty($link)) {
	$error_reply = "❌ Link tidak boleh kosong!\n\nSilakan masukkan link target atau batal untuk membatalkan pembuatan campaign:";
    sendErrorWithBackButton(
        $bot, 
        $chat_id, 
        $msg_id,
		$error_reply,
		"/cek_campaign"
    );
    return;
}

// Get campaign data and social account info
$campaign = db_query(
    "SELECT c.id, c.social_account_id, c.type, s.platform, s.username " .
    "FROM smm_campaigns c " .
    "LEFT JOIN smm_social_accounts s ON c.social_account_id = s.id " .
    "WHERE c.client_id = ? AND c.status = 'creating' " .
    "ORDER BY c.id DESC LIMIT 1",
    [$user_id]
);

if (empty($campaign)) {
    $bot->sendMessage($chat_id, "❌ Campaign tidak ditemukan!");
    return;
}

$campaign_data = $campaign[0];
$campaign_type = $campaign_data['type'];
$selected_platform = $campaign_data['platform'];

// Validasi link sesuai platform yang dipilih
$platform_checks = [
    'instagram' => (strpos($link, 'instagram.com') !== false || strpos($link, 'instagr.am') !== false),
    'tiktok' => (strpos($link, 'tiktok.com') !== false),
    'youtube' => (strpos($link, 'youtube.com') !== false || strpos($link, 'youtu.be') !== false),
    'twitter' => (strpos($link, 'twitter.com') !== false || strpos($link, 'x.com') !== false),
    'facebook' => (strpos($link, 'facebook.com') !== false || strpos($link, 'fb.com') !== false)
];

$is_valid_platform = $platform_checks[$selected_platform] ?? false;

if (!$is_valid_platform) {
    $platform_names = [
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'twitter' => 'Twitter',
        'facebook' => 'Facebook'
    ];
    
    $platform_name = $platform_names[$selected_platform] ?? ucfirst($selected_platform);
    
    $error_reply = "❌ Link tidak sesuai dengan platform yang dipilih!\n\n";
    $error_reply .= "Platform akun Anda: <b>" . $platform_name . "</b>\n";
    $error_reply .= "Pastikan link yang Anda masukkan adalah link " . $platform_name . ".\n\n";
    $error_reply .= "Silakan masukkan link kembali atau batal untuk membatalkan pembuatan campaign:";
    
    sendErrorWithBackButton(
        $bot, 
        $chat_id, 
        $msg_id,
		$error_reply,
		"/cek_campaign"
    );
    return;
}

// Update link_target campaign di database
db_execute("UPDATE smm_campaigns SET link_target = ? WHERE client_id = ? AND status = 'creating'", [$link, $user_id]);

// Hapus pesan lama dengan msg_id
if ($msg_id) {
    $bot->deleteMessage($chat_id, $msg_id);
}

$update_result = updateUserPosition($chat_id, 'buat_campaign_price');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Platform icons
$platform_icons = [
    'instagram' => '📷',
    'tiktok' => '🎵',
    'youtube' => '▶️',
    'twitter' => '🐦',
    'facebook' => '👍'
];

$icon = $platform_icons[$selected_platform] ?? '📱';
$platform_names = [
    'instagram' => 'Instagram',
    'tiktok' => 'TikTok',
    'youtube' => 'YouTube',
    'twitter' => 'Twitter',
    'facebook' => 'Facebook'
];
$platform_name = $platform_names[$selected_platform] ?? ucfirst($selected_platform);

$reply = "<b>📝 Buat Campaign - Price</b>\n\n";
$reply .= "Akun: " . $icon . " <b>" . $platform_name . " - @" . $campaign[0]['username'] . "</b>\n";
$reply .= "Link: <code>" . $link . "</code>\n\n";
$reply .= "Silakan masukkan price/harga untuk tiap ".$campaign_type.":\n\n";
$reply .= "💡 <i>Contoh: 500</i>\n\n";
$reply .= "💰 Ketik price:";

$keyboard = $bot->buildInlineKeyboard([
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

?>
