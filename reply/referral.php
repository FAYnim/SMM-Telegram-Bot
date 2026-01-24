<?php

$update_result = updateUserPosition($chat_id, 'referral');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
    return;
}

$reply = "<b>🎁 Referral Saya</b>\n\n";
$reply .= "Dapatkan bonus dengan mengajak teman menggunakan kode referral Anda!\n\n";

// TODO: Get user's referral codes and statistics
// For now, show placeholder message
$reply .= "⚠️ <i>Fitur referral sedang dalam pengembangan.</i>\n";
$reply .= "Anda akan dapat membagikan kode referral dan mendapatkan bonus segera.\n\n";

$reply .= "👇 Menu Referral:";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '📊 Statistik Referral', 'callback_data' => '/referral_stats'],
    ],
    [
        ['text' => '➕ Buat Kode Custom', 'callback_data' => '/create_custom_code'],
    ],
    [
        ['text' => '📤 Bagikan Link', 'callback_data' => '/share_referral'],
    ],
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/start']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
