<?php
$update_result = updateUserPosition($chat_id, 'buat_campaign_finished');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Kirim pesan "sedang diproses"
$reply = "⏳ <b>Campaign Sedang Dibuat</b>\n\n";
$reply .= "Mohon tunggu, sedang membuat tasks untuk campaign Anda...\n";

$keyboard = []; // Empty keyboard - no buttons during processing
$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

// Ambil campaign yang baru dibuat (status 'creating')
$campaign = db_query("SELECT id, campaign_title, type, target_total, campaign_balance, price_per_task "
    ."FROM smm_campaigns "
    ."WHERE client_id = ? AND status = 'creating' "
    ."ORDER BY updated_at DESC LIMIT 1", [$user_id]);

if (!empty($campaign)) {
    $campaign_data = $campaign[0];
    $campaign_id = $campaign_data['id'];
    $target_total = $campaign_data['target_total'];
    $price_per_task = $campaign_data['price_per_task'];

    // Generate tasks untuk campaign
    $tasks_generated = 0;
    for ($i = 0; $i < $target_total; $i++) {
        $task_data = [
            'campaign_id' => $campaign_id,
            'status' => 'available'
        ];

        $task_id = db_create('smm_tasks', $task_data);
        if ($task_id) {
            $tasks_generated++;
        }
    }

    // Update status campaign menjadi active
    db_execute("UPDATE smm_campaigns SET status = 'active' WHERE id = ?", [$campaign_id]);

    logMessage('task_generation', [
        'campaign_id' => $campaign_id,
        'target_total' => $target_total,
        'tasks_generated' => $tasks_generated,
        'user_id' => $user_id
    ], 'debug');
}

if (!empty($campaign)) {
    $campaign_data = $campaign[0];

    $reply = "<b>✅ Campaign Berhasil Disimpan!</b>\n\n";
    $reply .= "Campaign Anda telah aktif dan siap menerima tugas dari workers.\n\n";
    $reply .= "<b>📋 Ringkasan Campaign:</b>\n";
    $reply .= "🆔 ID: #" . $campaign_data['id'] . "\n";
    $reply .= "📝 Judul: " . htmlspecialchars($campaign_data['campaign_title']) . "\n";
    $reply .= "🎯 Tipe: " . ucfirst($campaign_data['type']) . "s\n";
    $reply .= "🎯 Target: " . number_format($campaign_data['target_total']) . " tasks\n";
    $reply .= "💰 Total Budget: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n";
    $reply .= "📊 Tasks Generated: " . $tasks_generated . "/" . $target_total . "\n\n";
    $reply .= "Anda dapat memantau progress campaign di menu \"Campaignku\".";
} else {
    $reply = "<b>❌ Gagal membuat campaign!</b>\n\n";
    $reply .= "Terjadi kesalahan saat membuat campaign. Silakan coba lagi.";
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
