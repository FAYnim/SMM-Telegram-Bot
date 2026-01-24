<?php
$update_result = updateUserPosition($chat_id, 'buat_campaign_type');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
    return;
}

// Extract campaign type from callback data
$campaign_type = str_replace('/buat_campaign_', '', $cb_data);

// Update campaign type ke database
db_execute("UPDATE smm_campaigns SET type = ? WHERE client_id = ? AND status = 'creating'", [$campaign_type, $user_id]);

$reply = "<b>📝 Buat Campaign - " . ucfirst($campaign_type) . "s</b>\n\n";
$reply .= "Silakan masukkan judul campaign Anda:\n\n";

// Contoh berdasarkan tipe
$examples = [
    'view' => 'Campaign Views Video Musik Terbaru',
    'like' => 'Campaign Likes Foto Produk Terbaru', 
    'comment' => 'Campaign Comments Review Produk',
    'share' => 'Campaign Share Artikel Blog',
    'follow' => 'Campaign Follow Akun Instagram'
];

$reply .= "💡 <i>Contoh: " . ($examples[$campaign_type] ?? 'Campaign Baru') . "</i>\n\n";
$reply .= "📝 Ketik judul campaign Anda:";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Batal', 'callback_data' => '/cek_campaign']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>