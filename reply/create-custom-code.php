<?php

// Show loading message first
$loading_reply = "⏳ <b>Membuat Kode Referral...</b>\n\n";
$loading_reply .= "Mohon tunggu sebentar.\n\n";
$loading_reply .= "<i>Sistem sedang generate kode unik untuk Anda.</i>";

$bot->editMessage($chat_id, $msg_id, $loading_reply, 'HTML');

// Fungsi generate kode random 8 karakter
// Menggunakan karakter A-Z (kecuali I, L, O, S, Z) dan 0-9
function generate_random_code($length = 8) {
    // Karakter yang diperbolehkan sesuai rules: A-HJ-NP-RU-Y dan 0-9
    $chars = 'ABCDEFGHJKMNPQRTUVY0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

// Generate kode dengan retry mechanism (max 10 attempts)
$max_attempts = 10;
$attempt = 0;
$custom_code = null;

while ($attempt < $max_attempts) {
    $custom_code = generate_random_code(8);
    $existing = db_read('smm_referral_codes', ['code' => $custom_code]);
    
    if (empty($existing)) {
        break; // Kode unique, keluar dari loop
    }
    $attempt++;
}

// Jika setelah 10x masih gagal (sangat jarang terjadi)
if ($attempt >= $max_attempts) {
    $reply = "❌ <b>Gagal Generate Kode</b>\n\n";
    $reply .= "Sistem tidak dapat membuat kode unique saat ini.\n\n";
    $reply .= "<i>Silakan coba lagi dalam beberapa saat.</i>";
    
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/referral']
        ]
    ]);
    
    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    return;
}

// Create custom referral code
$create_result = db_create('smm_referral_codes', [
    'user_id' => $user_id,
    'code' => $custom_code,
    'is_custom' => 1
]);

// Error handling
if (is_string($create_result) && strpos($create_result, 'Error:') === 0) {
    $reply = "❌ <b>Gagal Membuat Kode</b>\n\n";
    $reply .= $create_result . "\n\n";
    $reply .= "<i>Silakan coba lagi atau hubungi admin.</i>";
    
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/referral']
        ]
    ]);
    
    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    return;
}

// Success - update user position
$update_result = updateUserPosition($chat_id, 'create_custom_code_finish');

// Wait 1 second before showing result
sleep(1);

// Show generated code
$custom_url = "https://t.me/" . $bot_username . "?start=" . $custom_code;

$reply = "✅ <b>Kode Berhasil Dibuat</b>\n\n";
$reply .= "Kode referral custom Anda:\n";
$reply .= "<code>" . $custom_code . "</code>\n\n";
$reply .= "📋 <b>Link Referral:</b>\n";
$reply .= $custom_url . "\n\n";
$reply .= "<i>💡 Kode ini dibuat otomatis dan unik untuk Anda.</i>";

// Build share text for Telegram share URL
$share_text = "🎁 Gabung Bot SMM Panel & Dapat Bonus!\n\n"
    . "Kerjain task social media simpel, dapat uang!\n\n"
    . "✅ Gratis daftar\n"
    . "✅ Task mudah (like, follow, comment)\n"
    . "✅ Bayaran langsung ke saldo\n"
    . "✅ Withdraw kapan saja\n\n"
    . "Daftar sekarang: " . $custom_url;

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '📤 Bagikan Link', 'url' => 'https://t.me/share/url?text=' . urlencode($share_text)],
    ],
    [
        ['text' => '🔙 Kembali ke Menu Referral', 'callback_data' => '/referral']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
