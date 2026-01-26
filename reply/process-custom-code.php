<?php

// Delete old message first
$bot->deleteMessage($chat_id, $msg_id);

// Convert to uppercase and trim
$custom_code = strtoupper(trim($message));

// Validate code format
if (strlen($custom_code) < 3) {
    $reply = "❌ <b>Kode Terlalu Pendek</b>\n\n";
    $reply .= "Kode referral minimal 3 karakter.\n\n";
    $reply .= "<i>Silakan kirim kode yang baru.</i>";
    
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/referral']
        ]
    ]);
    
    $result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
    return;
}

if (strlen($custom_code) > 20) {
    $reply = "❌ <b>Kode Terlalu Panjang</b>\n\n";
    $reply .= "Kode referral maksimal 20 karakter.\n\n";
    $reply .= "<i>Silakan kirim kode yang baru.</i>";
    
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/referral']
        ]
    ]);
    
    $result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
    return;
}

// Validate characters (only A-Z except I, L, O, S, Z and 0-9)
if (!preg_match('/^[A-HJ-NP-RU-Y0-9]+$/', $custom_code)) {
    $reply = "❌ <b>Format Kode Tidak Valid</b>\n\n";
    $reply .= "Kode referral hanya boleh mengandung:\n";
    $reply .= "• Huruf (A-Z)\n";
    $reply .= "• Angka (0-9)\n";
    $reply .= "• Dilarang karakter: I, L, O, S, Z\n\n";
    $reply .= "<i>Silakan kirim kode yang baru.</i>";
    
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/referral']
        ]
    ]);
    
    $result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
    return;
}

// Check if code already exists
$existing_code = db_read('smm_referral_codes', ['code' => $custom_code]);

if (!empty($existing_code)) {
    $reply = "❌ <b>Kode Sudah Digunakan</b>\n\n";
    $reply .= "Kode <code>" . $custom_code . "</code> sudah digunakan oleh user lain.\n\n";
    $reply .= "<i>Silakan pilih kode yang berbeda.</i>";
    
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/referral']
        ]
    ]);
    
    $result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
    return;
}

// Create custom referral code
$create_result = db_create('smm_referral_codes', [
    'user_id' => $user_id,
    'code' => $custom_code,
    'is_custom' => 1
]);

if (is_string($create_result) && strpos($create_result, 'Error:') === 0) {
    $reply = "❌ <b>Gagal Membuat Kode</b>\n\n";
    $reply .= $create_result . "\n\n";
    $reply .= "<i>Silakan coba lagi atau hubungi admin.</i>";
    
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/referral']
        ]
    ]);
    
    $result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
    return;
}

// Success - show the new custom code
$update_result = updateUserPosition($chat_id, 'create_custom_code_finsih');

$custom_url = "https://t.me/" . $bot_username . "?start=" . $custom_code;

$reply = "✅ <b>Kode Berhasil Dibuat</b>\n\n";
$reply .= "Kode referral custom Anda:\n";
$reply .= "<code>" . $custom_code . "</code>\n\n";
$reply .= "📋 <b>Link Referral:</b>\n";
$reply .= $custom_url . "\n\n";
$reply .= "<i>💡 Anda bisa langsung membagikan link ini kepada teman-teman.</i>";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '📤 Bagikan Link', 'callback_data' => '/share_referral'],
    ],
    [
        ['text' => '🔙 Kembali ke Menu Referral', 'callback_data' => '/referral']
    ]
]);

$result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');

// Simpan msg_id baru
if ($result && isset($result['result']['message_id'])) {
    $new_msg_id = $result['result']['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}

?>
