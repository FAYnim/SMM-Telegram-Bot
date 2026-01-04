<?php

// Extract Task ID from submenu (format: task_approve_{task_id})
$parts = explode('_', $submenu);
$task_id = $parts[2];

// Validasi Input Konfirmasi
$confirmation = strtoupper(trim($message));

if ($confirmation !== 'YA') {
    $reply = "❌ <b>Konfirmasi Dibatalkan</b>\n";
    $reply .= "Approve task dibatalkan. Ketik <b>YA</b> untuk konfirmasi.";
    $bot->sendMessage($chat_id, $reply, 'HTML');
    return;
}

// Ambil detail task
$task_detail = db_query("SELECT t.*, c.campaign_title, c.type, c.price_per_task, u.full_name, u.chatid as user_chatid, w.id as wallet_id "
    ."FROM smm_tasks t "
    ."JOIN smm_campaigns c ON t.campaign_id = c.id "
    ."JOIN smm_users u ON t.worker_id = u.id "
    ."LEFT JOIN smm_wallets w ON u.id = w.user_id "
    ."WHERE t.id = ? AND t.status = 'pending_review' "
    ."LIMIT 1", [$task_id]);

if (empty($task_detail)) {
    $bot->sendMessage($chat_id, "❌ Task tidak ditemukan atau sudah diproses.");
    return;
}

$task = $task_detail[0];
$reward_amount = $task['price_per_task'];

// Create wallet if not exists
if (!$task['wallet_id']) {
    db_create('smm_wallets', ['user_id' => $task['worker_id'], 'balance' => 0]);
    $wallet_info = db_read('smm_wallets', ['user_id' => $task['worker_id']]);
    $wallet_id = $wallet_info[0]['id'];
    $balance_before = 0;
} else {
    $wallet_id = $task['wallet_id'];
    $wallet_info = db_read('smm_wallets', ['id' => $wallet_id]);
    $balance_before = $wallet_info[0]['balance'];
}

$balance_after = $balance_before + $reward_amount;

// Update Saldo Wallet
$update_wallet = db_update('smm_wallets', ['balance' => $balance_after], ['id' => $wallet_id]);

if (!$update_wallet) {
    $bot->sendMessage($chat_id, "❌ Gagal mengupdate saldo worker.");
    return;
}

// Log Transaksi Wallet
$transaction_data = [
    'wallet_id' => $wallet_id,
    'type' => 'task_reward',
    'amount' => $reward_amount,
    'balance_before' => $balance_before,
    'balance_after' => $balance_after,
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

// Update Campaign completed_count
db_execute("UPDATE smm_campaigns SET completed_count = completed_count + 1 WHERE id = ?", [$task['campaign_id']]);

// Reset Posisi Admin
updateUserPosition($chat_id, 'main', '');

// Hapus pesan prompt admin sebelumnya
$bot->deleteMessage($chat_id, $msg_id);

// --- NOTIFIKASI KE WORKER ---
$user_reply = "✅ <b>Task Disetujui!</b>\n\n";
$user_reply .= "Selamat! Task Anda telah disetujui oleh Admin.\n\n";
$user_reply .= "📋 <b>Detail Task:</b>\n";
$user_reply .= "• Campaign: " . htmlspecialchars($task['campaign_title']) . "\n";
$user_reply .= "• Reward: <b>Rp " . number_format($reward_amount, 0, ',', '.') . "</b>\n\n";
$user_reply .= "💰 <b>Saldo Ditambahkan!</b>\n";
$user_reply .= "Saldo Anda sekarang: <b>Rp " . number_format($balance_after, 0, ',', '.') . "</b>\n\n";
$user_reply .= "Terima kasih telah mengerjakan task! 🎉";

$bot->sendMessage($task['user_chatid'], $user_reply, 'HTML');

// --- KONFIRMASI KE ADMIN ---
$admin_reply = "✅ <b>Task Disetujui</b>\n\n";
$admin_reply .= "👤 Worker: " . htmlspecialchars($task['full_name']) . " (ID: " . $task['user_chatid'] . ")\n";
$admin_reply .= "📋 Campaign: " . htmlspecialchars($task['campaign_title']) . "\n";
$admin_reply .= "🎯 Jenis: " . ucfirst($task['type']) . "\n";
$admin_reply .= "💰 Reward: <b>Rp " . number_format($reward_amount, 0, ',', '.') . "</b>\n";
$admin_reply .= "📢 Status: Worker telah dinotifikasi dan saldo ditambahkan.";

$message_result = $bot->sendMessage($chat_id, $admin_reply, 'HTML');

// Update last msg_id admin
if ($message_result && isset($message_result['result']['message_id'])) {
    $new_msg_id = $message_result['result']['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}

?>
