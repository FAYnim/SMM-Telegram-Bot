<?php
$update_result = updateUserPosition($chat_id, 'buat_campaign_finished');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Update status campaign menjadi active
db_execute("UPDATE smm_campaigns SET status = 'active' WHERE client_id = ? AND status = 'creating'", [$user_id]);

// Ambil campaign yang baru disimpan
$campaign = db_query("SELECT id, campaign_title, type, target_total, campaign_balance "
    ."FROM smm_campaigns "
    ."WHERE client_id = ? AND status = 'active' "
    ."ORDER BY updated_at DESC LIMIT 1", [$user_id]);

if (!empty($campaign)) {
    $campaign_data = $campaign[0];
    
    $reply = "<b>✅ Campaign Berhasil Disimpan!</b>\n\n";
    $reply .= "Campaign Anda telah aktif dan siap menerima tugas dari workers.\n\n";
    $reply .= "<b>📋 Ringkasan Campaign:</b>\n";
    $reply .= "🆔 ID: #" . $campaign_data['id'] . "\n";
    $reply .= "📝 Judul: " . htmlspecialchars($campaign_data['campaign_title']) . "\n";
    $reply .= "🎯 Tipe: " . ucfirst($campaign_data['type']) . "s\n";
    $reply .= "🎯 Target: " . number_format($campaign_data['target_total']) . " tasks\n";
    $reply .= "💰 Total Budget: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n\n";
    $reply .= "Anda dapat memantau progress campaign di menu \"Campaignku\".";
} else {
    $reply = "<b>✅ Campaign Berhasil Disimpan!</b>\n\n";
    $reply .= "Campaign Anda telah aktif dan siap menerima tugas dari workers.\n\n";
    $reply .= "Anda dapat memantau progress campaign di menu \"Campaignku\".";
}

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '📋 Lihat Campaign Saya', 'callback_data' => '/cek_campaign'],
    ],
    [
        ['text' => '🔙 Kembali ke Menu Utama', 'callback_data' => '/start']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
