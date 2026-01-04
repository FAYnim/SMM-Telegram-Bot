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

$campaign = db_query("SELECT id, campaign_title, type, link_target, price_per_task "
	."FROM smm_campaigns WHERE status = 'active' "
	."ORDER BY price_per_task DESC LIMIT 0,1");

if (empty($campaign)) {
    $reply .= "❌ Tidak ada task yang tersedia saat ini.\n";
    $reply .= "Silakan coba lagi nanti!";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔄 Refresh', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
}

//$reply .= "Campaign active found";
$campaign_data = $campaign[0];
$campaign_id = $campaign_data['id'];
$campaign_type = $campaign_data['type'];
$campaign_link = $campaign_data['link_target'];
$campaign_price = $campaign_data['price_per_task'];
$campaign_title = $campaign_data['campaign_title'];

$task = db_query("SELECT id "
	."FROM smm_tasks WHERE "
	."status = 'available' AND "
	."campaign_id = ? LIMIT 0,1",
	[$campaign_id]);

if (empty($task)) {
    $reply .= "❌ Tidak ada task yang tersedia saat ini.\n";
    $reply .= "Silakan coba lagi nanti!";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔄 Refresh', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);
} else {
	$task_data = $task[0];
	$task_id = $task_data["id"];

    $reply .= "📌 <b>" . htmlspecialchars($campaign_title) . "</b>\n";
    $reply .= "🎯 Jenis: " . ucfirst($campaign_type) . "\n";
    $reply .= "💰 Reward: Rp " . number_format($campaign_price, 0, ',', '.') . "\n\n";
    $reply .= "Klik tombol di bawah untuk mengambil task ini:";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🎯 Ambil Task', 'callback_data' => '/take_task_' . $task_id]
        ],
        [
            ['text' => '🔄 Refresh', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ]);
}

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>

