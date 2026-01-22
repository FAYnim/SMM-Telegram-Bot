<?php

require_once 'helpers/error-handler.php';

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

// Cari campaign aktif dan belum pernah dikerjakan user
$campaign = db_query("SELECT id, campaign_title, type, link_target, price_per_task "
	."FROM smm_campaigns WHERE status = 'active' AND client_id != ? "
	."AND NOT EXISTS ("
	."    SELECT 1 FROM smm_tasks WHERE campaign_id = smm_campaigns.id "
	."    AND worker_id = ? AND status IN ('taken', 'pending_review', 'approved')"
	.") "
	."ORDER BY price_per_task DESC LIMIT 0,1", [$user_id, $user_id]);

if (empty($campaign)) {
    $error_message = "📋 <b>Task Tersedia</b>\n\n";
    $error_message .= "❌ Tidak ada task yang tersedia saat ini.\n";
    $error_message .= "Silakan coba lagi nanti!";

    $buttons = [
        [
            ['text' => '🔄 Refresh', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ];

	editErrorWithCustomButtons($bot, $chat_id, $msg_id, $error_message, $buttons);
	return;
}

//$reply .= "Campaign active found";
$campaign_data = $campaign[0];
$campaign_id = $campaign_data['id'];
$campaign_type = $campaign_data['type'];
$campaign_link = $campaign_data['link_target'];
$campaign_price = $campaign_data['price_per_task'];
$campaign_title = $campaign_data['campaign_title'];

// Cari slot task available dari campaign id yang aktif
$task = db_query("SELECT id "
	."FROM smm_tasks WHERE "
	."status = 'available' AND "
	."campaign_id = ? AND "
	."NOT EXISTS ("
	."    SELECT 1 FROM smm_tasks t2 WHERE t2.campaign_id = smm_tasks.campaign_id "
	."    AND t2.worker_id = ? AND t2.status IN ('taken', 'pending_review', 'approved')"
	.") "
	."LIMIT 0,1",
	[$campaign_id, $user_id]);

if (empty($task)) {
    $error_message = "📋 <b>Task Tersedia</b>\n\n";
    $error_message .= "❌ Tidak ada task yang tersedia saat ini.\n";
    $error_message .= "Silakan coba lagi nanti!";

    $buttons = [
        [
            ['text' => '🔄 Refresh', 'callback_data' => '/task_refresh'],
            ['text' => '🔙 Kembali', 'callback_data' => '/start']
        ]
    ];

    editErrorWithCustomButtons($bot, $chat_id, $msg_id, $error_message, $buttons);
    return;
}

// Task tersedia, tampilkan detail
$task_data = $task[0];
$task_id = $task_data["id"];

$reply .= "📌 <b>" . htmlspecialchars($campaign_title) . "</b>\n";
$reply .= "🎯 Jenis: " . ucfirst($campaign_type) . "\n";
$reply .= "💰 Reward: " . number_format($campaign_price, 0, ',', '.') . "\n\n";
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

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>

