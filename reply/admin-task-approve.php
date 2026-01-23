<?php

require_once 'helpers/error-handler.php';

// Extract Task ID from submenu (format: task_approve_{task_id})
$parts = explode('_', $submenu);
$task_id = $parts[2];

// Validasi Input Konfirmasi
$confirmation = strtoupper(trim($message));

if ($confirmation !== 'YA') {
    $error_message = "❌ <b>Konfirmasi Dibatalkan</b>\n\n";
    $error_message .= "Approve task dibatalkan.\n";
    $error_message .= "Ketik <b>YA</b> (huruf kapital) untuk mengkonfirmasi approve task.";
    sendSimpleError($bot, $chat_id, $error_message);
    return;
}

// Ambil detail task dan campaign
$task_detail = db_query("SELECT t.*, c.campaign_title, c.type, c.price_per_task, c.campaign_balance, c.target_total, c.completed_count, c.client_id, u.full_name, u.chatid as user_chatid, w.id as wallet_id "
    ."FROM smm_tasks t "
    ."JOIN smm_campaigns c ON t.campaign_id = c.id "
    ."JOIN smm_users u ON t.worker_id = u.id "
    ."LEFT JOIN smm_wallets w ON u.id = w.user_id "
    ."WHERE t.id = ? AND t.status = 'pending_review' "
    ."LIMIT 1", [$task_id]);

if (empty($task_detail)) {
    $error_message = "❌ <b>Task Tidak Ditemukan</b>\n\n";
    $error_message .= "Task ID: <code>" . $task_id . "</code>\n\n";
    $error_message .= "Task ini tidak ditemukan atau sudah diproses oleh admin lain.\n";
    $error_message .= "Silakan cek daftar task yang pending review.";
    sendSimpleError($bot, $chat_id, $error_message);
    return;
}

$task = $task_detail[0];
$reward_amount = $task['price_per_task'];
$campaign_balance = $task['campaign_balance'];
$campaign_id = $task['campaign_id'];
$target_total = $task['target_total'];
$completed_count = $task['completed_count'];
$client_id = $task['client_id'];

// Validasi campaign_balance masih cukup
if ($campaign_balance < $reward_amount) {
    $error_message = "❌ <b>Campaign Balance Tidak Cukup</b>\n\n";
    $error_message .= "Campaign tidak memiliki balance cukup untuk membayar reward task ini.\n\n";
    $error_message .= "💰 <b>Detail Balance:</b>\n";
    $error_message .= "• Balance Campaign: " . number_format($campaign_balance, 0, ',', '.') . "\n";
    $error_message .= "• Reward Task: " . number_format($reward_amount, 0, ',', '.') . "\n";
    $error_message .= "• Kekurangan: " . number_format($reward_amount - $campaign_balance, 0, ',', '.') . "\n\n";
    $error_message .= "⚠️ Task tidak bisa diapprove. Client perlu top-up campaign balance terlebih dahulu.";
    sendSimpleError($bot, $chat_id, $error_message);
    return;
}

// Create wallet if not exists
if (!$task['wallet_id']) {
    db_create('smm_wallets', ['user_id' => $task['worker_id'], 'balance' => 0, 'profit' => 0]);
    $wallet_info = db_read('smm_wallets', ['user_id' => $task['worker_id']]);
    $wallet_id = $wallet_info[0]['id'];
    $profit_before = 0;
} else {
    $wallet_id = $task['wallet_id'];
    $wallet_info = db_read('smm_wallets', ['id' => $wallet_id]);
    $profit_before = $wallet_info[0]['profit'];
}

$profit_after = $profit_before + $reward_amount;

// Update Profit Wallet (bukan balance)
$update_wallet = db_update('smm_wallets', ['profit' => $profit_after], ['id' => $wallet_id]);

if (!$update_wallet) {
    $error_message = "❌ <b>Gagal Update Wallet</b>\n\n";
    $error_message .= "Terjadi kesalahan database saat mengupdate profit worker.\n\n";
    $error_message .= "📋 <b>Detail:</b>\n";
    $error_message .= "• Worker ID: " . $task['worker_id'] . "\n";
    $error_message .= "• Wallet ID: " . $wallet_id . "\n";
    $error_message .= "• Reward: " . number_format($reward_amount, 0, ',', '.') . "\n\n";
    $error_message .= "⚠️ Task belum diapprove. Silakan coba lagi atau hubungi developer.";
    sendSimpleError($bot, $chat_id, $error_message);
    return;
}

// Log Transaksi Wallet
$transaction_data = [
    'wallet_id' => $wallet_id,
    'type' => 'task_reward',
    'amount' => $reward_amount,
    'balance_before' => $profit_before,
    'balance_after' => $profit_after,
    'description' => 'Reward task: ' . htmlspecialchars($task['campaign_title']),
    'reference_id' => $task_id,
    'status' => 'approved'
];
db_create('smm_wallet_transactions', $transaction_data);

// Update Status Task ke Approved
$task_update = [
    'status' => 'approved',
    'reviewed_at' => date('Y-m-d H:i:s')
];
db_update('smm_tasks', $task_update, ['id' => $task_id]);

// Kurangi campaign_balance dan update completed_count
$new_campaign_balance = $campaign_balance - $reward_amount;
$new_completed_count = $completed_count + 1;

db_execute("UPDATE smm_campaigns SET completed_count = ?, campaign_balance = ? WHERE id = ?", [$new_completed_count, $new_campaign_balance, $campaign_id]);

// Cek apakah campaign sudah completed
$is_target_reached = ($new_completed_count >= $target_total);
$is_balance_empty = ($new_campaign_balance <= 0);

if ($is_target_reached || $is_balance_empty) {
    // Update status campaign menjadi completed
    db_execute("UPDATE smm_campaigns SET status = 'completed' WHERE id = ?", [$campaign_id]);
    
    // Notifikasi ke client bahwa campaign sudah selesai
    $client_data = db_query("SELECT chatid FROM smm_users WHERE id = ?", [$client_id]);
    
    if (!empty($client_data)) {
        $client_chatid = $client_data[0]['chatid'];
        $completion_reason = $is_target_reached ? "target tercapai" : "balance habis";
        
        $client_notification = "🎉 <b>Campaign Selesai!</b>\n\n";
        $client_notification .= "Campaign Anda telah selesai (".$completion_reason.").\n\n";
        $client_notification .= "📋 Campaign: ".htmlspecialchars($task['campaign_title'])."\n";
        $client_notification .= "✅ Completed: ".$new_completed_count."/".$target_total." tasks\n";
        $client_notification .= "💰 Sisa Balance: ".number_format($new_campaign_balance, 0, ',', '.')."\n\n";
        $client_notification .= "Terima kasih telah menggunakan layanan kami!";
        
        // Keyboard untuk tutup notifikasi
        $keyboard_campaign_done = [
            'inline_keyboard' => [
                [
                    ['text' => '✖️ Tutup Notifikasi', 'callback_data' => 'close_notif']
                ]
            ]
        ];
        
        $bot->sendMessageWithKeyboard($client_chatid, $client_notification, $keyboard_campaign_done, null, 'HTML');
    }
}

// Reset Posisi Admin
$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Hapus pesan prompt admin sebelumnya
$bot->deleteMessage($chat_id, $msg_id);

// --- NOTIFIKASI KE WORKER ---
$user_reply = "✅ <b>Task Disetujui!</b>\n\n";
$user_reply .= "Selamat! Task Anda telah disetujui oleh Admin.\n\n";
$user_reply .= "📋 <b>Detail Task:</b>\n";
$user_reply .= "• Campaign: " . htmlspecialchars($task['campaign_title']) . "\n";
$user_reply .= "• Reward: <b>" . number_format($reward_amount, 0, ',', '.') . "</b>\n\n";
$user_reply .= "💰 <b>Profit Ditambahkan!</b>\n";
$user_reply .= "Profit Anda sekarang: <b>" . number_format($profit_after, 0, ',', '.') . "</b>\n\n";
$user_reply .= "Terima kasih telah mengerjakan task! 🎉";

// Keyboard untuk tutup notifikasi
$keyboard_task = [
    'inline_keyboard' => [
        [
            ['text' => '✖️ Tutup Notifikasi', 'callback_data' => 'close_notif']
        ]
    ]
];

$bot->sendMessageWithKeyboard($task['user_chatid'], $user_reply, $keyboard_task, null, 'HTML');

// --- KONFIRMASI KE ADMIN ---
$admin_reply = "✅ <b>Task Disetujui</b>\n\n";
$admin_reply .= "👤 Worker: " . htmlspecialchars($task['full_name']) . " (ID: " . $task['user_chatid'] . ")\n";
$admin_reply .= "📋 Campaign: " . htmlspecialchars($task['campaign_title']) . "\n";
$admin_reply .= "🎯 Jenis: " . ucfirst($task['type']) . "\n";
$admin_reply .= "💰 Reward: <b>" . number_format($reward_amount, 0, ',', '.') . "</b>\n";
$admin_reply .= "📊 Progress: ".$new_completed_count."/".$target_total." tasks\n";
$admin_reply .= "💳 Campaign Balance: ".number_format($new_campaign_balance, 0, ',', '.')."\n";

if ($is_target_reached || $is_balance_empty) {
    $admin_reply .= "\n🎉 <b>Campaign Completed!</b>\n";
}

$admin_reply .= "\n📢 Status: Worker telah dinotifikasi dan profit ditambahkan.";

$message_result = $bot->sendMessage($chat_id, $admin_reply, 'HTML');

// Update last msg_id admin
if ($message_result && isset($message_result['result']['message_id'])) {
    $new_msg_id = $message_result['result']['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}

?>
