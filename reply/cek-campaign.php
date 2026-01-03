<?php

$update_result = updateUserPosition($chat_id, 'cek_campaign');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Hapus campaign yang masih dalam proses pembuatan (status='creating')
db_execute("DELETE FROM smm_campaigns WHERE client_id = ? AND status = 'creating'", [$user_id]);

$reply = "<b>📋 Cek Campaign Saya</b>\n\n";
$reply .= "Berikut adalah daftar campaign yang Anda buat:\n\n";

// System Logic
// Get user's campaigns
$campaigns = db_query("SELECT id, campaign_title, type, link_target, price_per_task, target_total, completed_count, campaign_balance, status, created_at "
    ."FROM smm_campaigns "
    ."WHERE client_id = ? and status NOT IN ('deleted', 'creating') "
	."ORDER BY created_at DESC", [$user_id]);

if (count($campaigns) > 0) {
    foreach ($campaigns as $campaign) {
        $reply .= "<b>" . htmlspecialchars($campaign['campaign_title']) . "</b>\n";
        $reply .= "🆔 ID: #" . $campaign['id'] . "\n";
        $reply .= "🎯 Tipe: " . ucfirst($campaign['type']) . "\n";
        $reply .= "💰 Harga/task: Rp " . number_format($campaign['price_per_task'], 0, ',', '.') . "\n";
        $reply .= "📊 Progress: " . $campaign['completed_count'] . "/" . $campaign['target_total'] . " tasks\n";
        $reply .= "💰 Total Budget: Rp " . number_format($campaign['campaign_balance'], 0, ',', '.') . "\n";
        $reply .= "📈 Status: " . ucfirst($campaign['status']) . "\n";
        $reply .= "📅 Dibuat: " . date('d/m/Y', strtotime($campaign['created_at'])) . "\n";
        $reply .= "==================\n\n";
    }
} else {
    $reply .= "⚠️ <i>Belum ada campaign.</i>\n";
    $reply .= "Buat campaign pertama Anda untuk mulai mendapatkan engagement.\n\n";
}

$reply .= "👇 Gunakan menu di bawah ini:";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '➕ Buat Campaign', 'callback_data' => '/buat_campaign'],
    ],
    [
        ['text' => '🎛️ Edit Campaign', 'callback_data' => '/edit_campaign'],
    ],
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/start']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
