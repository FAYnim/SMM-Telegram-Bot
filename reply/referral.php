<?php

$update_result = updateUserPosition($chat_id, 'referral');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
    return;
}

// Check if user has auto-generated referral code
$referral_codes = db_read('smm_referral_codes', ['user_id' => $user_id, 'is_custom' => 0]);

// If no auto-generated code exists, create one using username (uppercase)
if (empty($referral_codes)) {
    // Use username (uppercase) as referral code, fallback to user_id if no username
    $code = !empty($username) ? strtoupper($username) : 'USER' . $user_id;
    
    // Create the referral code
    $create_result = db_create('smm_referral_codes', [
        'user_id' => $user_id,
        'code' => $code,
        'is_custom' => 0
    ]);
    
    if (is_string($create_result) && strpos($create_result, 'Error:') === 0) {
        $bot->sendMessage($chat_id, "❌ Gagal membuat kode referral!\n\n" . $create_result);
        return;
    }
    
    // Reload referral codes after creation
    $referral_codes = db_read('smm_referral_codes', ['user_id' => $user_id, 'is_custom' => 0]);
}

// Get all user's referral codes (auto-generated and custom)
$all_codes = db_read('smm_referral_codes', ['user_id' => $user_id]);

// Get referral statistics
$referral_stats = db_query(
    "SELECT COUNT(*) as total_referrals, SUM(reward_amount) as total_rewards "
    . "FROM smm_referrals WHERE referrer_id = ?",
    [$user_id]
);

if (isset($referral_stats[0]['total_referrals'])) {
    $total_referrals = $referral_stats[0]['total_referrals'];
} else {
    $total_referrals = 0;
}

if (isset($referral_stats[0]['total_rewards'])) {
    $total_rewards = $referral_stats[0]['total_rewards'];
} else {
    $total_rewards = 0;
}

$reply = "<b>🎁 Referral Saya</b>\n\n";
$reply .= "Dapatkan bonus dengan mengajak teman menggunakan kode referral Anda!\n\n";

if (!empty($referral_codes)) {
    $auto_code = $referral_codes[0]['code'];
    $referral_url = "https://t.me/" . $bot_username . "?start=" . $auto_code;
    $reply .= "📋 <b>Link Referral Anda:</b>\n";
    $reply .= $referral_url . "\n\n";
}

// Display custom codes if any
$custom_codes = array_filter($all_codes, function($code) {
    return $code['is_custom'] == 1;
});

if (!empty($custom_codes)) {
    $reply .= "🔖 <b>Link Custom:</b>\n";
    foreach ($custom_codes as $custom) {
        $custom_url = "https://t.me/" . $bot_username . "?start=" . $custom['code'];
        
        // Check if this custom code has been used
        $code_usage = db_read('smm_referrals', ['referral_code' => $custom['code']]);
        $usage_indicator = !empty($code_usage) ? " (used)" : "";
        
        $reply .= $custom_url . $usage_indicator . "\n";
    }
    $reply .= "\n";
}

// Display statistics
$reply .= "📊 <b>Statistik:</b>\n";
$reply .= "• Total Referral: <b>" . $total_referrals . " orang</b>\n";
$reply .= "• Total Reward: <b>Rp " . number_format($total_rewards, 0, ',', '.') . "</b>\n\n";

$reply .= "👇 Menu Referral:";

// Build share text for Telegram share URL
$share_text = "🎁 Gabung Bot SMM Panel & Dapat Bonus!\n\n"
    . "Kerjain task social media simpel, dapat uang!\n\n"
    . "✅ Gratis daftar\n"
    . "✅ Task mudah (like, follow, comment)\n"
    . "✅ Bayaran langsung ke saldo\n"
    . "✅ Withdraw kapan saja\n\n"
    . "Daftar sekarang: " . $referral_url;

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '📊 Statistik Referral', 'callback_data' => '/referral_stats'],
    ],
    [
        ['text' => '➕ Buat Kode Custom', 'callback_data' => '/create_custom_code'],
    ],
    [
        ['text' => '📤 Bagikan Link', 'url' => 'https://t.me/share/url?text=' . urlencode($share_text)],
    ],
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/start']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
