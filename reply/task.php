<?php

// Update position hanya untuk /task, bukan /task_refresh
if ($cb_data != '/task_refresh') {
    $update_result = updateUserPosition($chat_id, 'task');
    
    if (!$update_result) {
        $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
        return;
    }
} else {
    // Untuk /task_refresh, tampilkan loading dulu
    $loading_reply = "⏳ <b>Memuat...</b>\n\n";
    $loading_reply .= "Sedang mencari task tersedia...";
    $loading_keyboard = [];
    $bot->editMessage($chat_id, $msg_id, $loading_reply, 'HTML', $loading_keyboard);
    sleep(1);
}

$reply = "📋 <b>Task Tersedia</b>\n\n";

// Query untuk cek task available
$tasks = db_query("SELECT t.*, c.campaign_title, c.type, c.price_per_task "
    ."FROM smm_tasks t "
    ."JOIN smm_campaigns c ON t.campaign_id = c.id "
    ."WHERE t.status = 'available' AND c.status = 'active' "
    ."ORDER BY c.created_at DESC "
    ."LIMIT 10");

if (empty($tasks)) {
    $reply .= "❌ Tidak ada task yang tersedia saat ini.\n";
    $reply .= "Silakan coba lagi nanti!";
    
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔄 Refresh', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);
} else {
    $task = $tasks[0];
    $reply .= "📌 <b>" . htmlspecialchars($task['campaign_title']) . "</b>\n";
    $reply .= "🎯 Jenis: " . ucfirst($task['type']) . "\n";
    $reply .= "💰 Reward: Rp " . number_format($task['price_per_task'], 0, ',', '.') . "\n\n";
    $reply .= "Klik tombol di bawah untuk mengambil task ini:";
    
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🎯 Ambil Task', 'callback_data' => '/take_task_' . $task['id']]
        ],
        [
            ['text' => '🔄 Refresh', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);
}

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
