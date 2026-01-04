<?php

$update_result = updateUserPosition($chat_id, 'take_task');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Extract task ID dari callback data
$task_id = str_replace('/take_task_', '', $cb_data);

$task = db_query("SELECT campaign_id FROM smm_tasks "
	."WHERE id = ? AND "
	."status = 'available'", [$task_id]);

if (empty($task)) {
    $reply = "❌ <b>Task Tidak Tersedia</b>\n\n";
    $reply .= "Task ini sudah diambil oleh user lain atau campaign tidak lagi aktif.\n";
    $reply .= "Silakan coba task lain!";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔄 Cari Task Lagi', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);
    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    return;
}


$campaign_id = $task[0]['campaign_id'];

$campaign = db_query("SELECT id, campaign_title, type, link_target, price_per_task "
	."FROM smm_campaigns WHERE "
	."id = ? AND status = 'active'", [$campaign_id]);

if (empty($campaign)) {
    $reply = "❌ <b>Campaign Tidak Tersedia</b>\n\n";
    $reply .= "Silakan coba refresh lagi!";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔄 Cari Task Lagi', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);
    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    return;
}

$campaign_data = $campaign[0];
$campaign_id = $campaign_data['id'];
$campaign_type = $campaign_data['type'];
$campaign_link = $campaign_data['link_target'];
$campaign_price = $campaign_data['price_per_task'];
$campaign_title = $campaign_data['campaign_title'];

// Cek apakah user sudah pernah ambil task dari campaign ini
$existing_task = db_query("SELECT COUNT(*) as count "
    ."FROM smm_tasks "
    ."WHERE worker_id = ? AND campaign_id = ? AND status IN ('taken', 'pending_review', 'approved')", [$user_id, $task['campaign_id']]);

if ($existing_task[0]['count'] > 0) {
    $reply = "❌ <b>Kamu Sudah Mengerjakan Campaign Ini</b>\n\n";
    $reply .= "Kamu sudah mengambil task dari campaign \"" . htmlspecialchars($task['campaign_title']) . "\".\n";
    $reply .= "Setiap campaign hanya bisa dikerjakan sekali per user.\n\n";
    $reply .= "Silakan coba task lain!";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔄 Cari Task Lagi', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);

    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    return;
}

// Update task status ke 'taken' dan set worker_id
$update_data = [
    'worker_id' => $user_id,
    'status' => 'taken',
    'taken_at' => date('Y-m-d H:i:s')
];

$task_updated = db_update('smm_tasks', $update_data, ['id' => $task_id]);

if (!$task_updated) {
    $reply = "❌ <b>Gagal Mengambil Task</b>\n\n";
    $reply .= "Terjadi kesalahan saat mengambil task.\n";
    $reply .= "Silakan coba lagi!";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔄 Coba Lagi', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);

    $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    return;
}

// Update user position untuk upload bukti
$update_result = updateUserPosition($chat_id, 'upload_proof', $task_id);

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Tampilkan detail task dan instruksi
$reply = "✅ <b>Task Berhasil Diambil!</b>\n\n";
$reply .= "<b>📋 Detail Task:</b>\n";
$reply .= "📝 Campaign: " . htmlspecialchars($campaign_title) . "\n";
$reply .= "🎯 Jenis: " . ucfirst($campaign_type) . "\n";
$reply .= "💰 Reward: Rp " . number_format($campaign_price, 0, ',', '.') . "\n\n";

$reply .= "<b>🔗 Link Target:</b>\n";
$reply .= "<code>" . htmlspecialchars($campaign_link) . "</code>\n\n";

$reply .= "<b>📋 Instruksi:</b>\n";
$reply .= "1. Klik link di atas\n";
$reply .= "2. Lakukan " . ucfirst($campaign_type) . " sesuai campaign\n";
$reply .= "3. Screenshot sebagai bukti\n";
$reply .= "4. Upload screenshot di chat ini\n\n";

$reply .= "<b>⚠️ Penting:</b>\n";
$reply .= "• Screenshot harus jelas dan menunjukkan username kamu\n";
$reply .= "• Pastikan task benar-benar selesai\n";
$reply .= "• Upload segera setelah selesai\n\n";

$reply .= "📷 <b>Silakan upload screenshot bukti kamu sekarang:</b>";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '❌ Batalkan Task', 'callback_data' => '/cancel_task_' . $task_id]
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
