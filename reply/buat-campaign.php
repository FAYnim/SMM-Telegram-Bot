<?php
$update_result = updateUserPosition($chat_id, 'buat_campaign');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
    return;
}

// Hapus campaign yang masih dalam proses pembuatan (status='creating')
db_execute("DELETE FROM smm_campaigns WHERE client_id = ? AND status = 'creating'", [$user_id]);

$reply = "<b>📝 Buat Campaign Baru</b>\n\n";
$reply .= "Silakan pilih jenis campaign yang ingin Anda buat:\n\n";
$reply .= "👇 Pilih jenis campaign:";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '👁️ Views', 'callback_data' => '/buat_campaign_view'],
    ],
    [
        ['text' => '❤️ Likes', 'callback_data' => '/buat_campaign_like'],
    ],
    [
        ['text' => '💬 Comments', 'callback_data' => '/buat_campaign_comment'],
    ],
    [
        ['text' => '🔄 Shares', 'callback_data' => '/buat_campaign_share'],
    ],
    [
        ['text' => '👥 Follows', 'callback_data' => '/buat_campaign_follow'],
    ],
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/cek_campaign']
    ]
]);

// Insert campaign baru ke database
$campaign_data = [
    'client_id' => $user_id,
];

$campaign_id = db_create('smm_campaigns', $campaign_data);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
