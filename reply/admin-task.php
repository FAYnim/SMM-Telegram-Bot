<?php

$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Gagal memperbarui status admin (Err: 1).");
    return;
}

$bot->deleteMessage($chat_id, $msg_id);

// Cek apakah user adalah admin
$admin = db_read('smm_admins', ['chatid' => $chat_id]);
if (empty($admin)) {
    $bot->sendMessage($chat_id, "❌ Akses ditolak! Anda bukan admin.");
    return;
}

$admin_msg_id = $bot->getCallbackMessageId();
if(!$admin_msg_id) {
    $bot->sendMessage($chat_id, "❌ Gagal mengambil ID pesan (Err: 2).");
    return;
}

if($admin_msg_id != $msg_id) {
    // Update msg_id column with $admin_msg_id in table users and admins
    db_update('smm_users', ['msg_id' => $admin_msg_id], ['chatid' => $chat_id]);
    db_update('smm_admins',  ['msg_id' => $admin_msg_id], ['chatid' => $chat_id]);
}

// Get task_id from callback data
$parts = explode('_', $cb_data);
$task_id = end($parts);

if(strpos($cb_data, "approve") !== false) {
    $update_result = updateUserPosition($chat_id, 'main', 'task_approve_'.$task_id);
    if (!$update_result) {
        $bot->sendMessage($chat_id, "❌ Gagal memperbarui posisi admin.");
        return;
    }

    // Ambil detail task untuk konfirmasi
    $task_detail = db_query("SELECT t.*, c.campaign_title, c.type, c.price_per_task, u.full_name, u.chatid as user_chatid "
        ."FROM smm_tasks t "
        ."JOIN smm_campaigns c ON t.campaign_id = c.id "
        ."JOIN smm_users u ON t.worker_id = u.id "
        ."WHERE t.id = ? AND t.status = 'pending_review' "
        ."LIMIT 1", [$task_id]);

    if (empty($task_detail)) {
        $reply = "❌ <b>Task Tidak Ditemukan</b>\n\n";
        $reply .= "Task ini tidak ada atau sudah diproses.";
        $bot->editMessage($chat_id, $admin_msg_id, $reply, 'HTML');
        return;
    }

    $task = $task_detail[0];
    $user_name = $task['full_name'] ? $task['full_name'] : "User ID: " . $task['user_chatid'];

    $reply = "✅ <b>Setujui Task</b>\n\n";
    $reply .= "Konfirmasi approve task untuk:\n";
    $reply .= "👤 Worker: " . htmlspecialchars($user_name) . " (ID: " . $task['user_chatid'] . ")\n";
    $reply .= "📋 Campaign: " . htmlspecialchars($task['campaign_title']) . "\n";
    $reply .= "🎯 Jenis: " . ucfirst($task['type']) . "\n";
    $reply .= "💰 Reward: <b>Rp " . number_format($task['price_per_task'], 0, ',', '.') . "</b>\n\n";
    $reply .= "Ketik <b>YA</b> untuk konfirmasi approve:";

    $bot->editMessage($chat_id, $admin_msg_id, $reply, 'HTML');

} elseif(strpos($cb_data, "reject") !== false) {
    $update_result = updateUserPosition($chat_id, 'main', 'task_reject_'.$task_id);
    if (!$update_result) {
        $bot->sendMessage($chat_id, "❌ Gagal memperbarui posisi admin.");
        return;
    }

    // Ambil detail task untuk konfirmasi
    $task_detail = db_query("SELECT t.*, c.campaign_title, c.type, u.full_name, u.chatid as user_chatid "
        ."FROM smm_tasks t "
        ."JOIN smm_campaigns c ON t.campaign_id = c.id "
        ."JOIN smm_users u ON t.worker_id = u.id "
        ."WHERE t.id = ? AND t.status = 'pending_review' "
        ."LIMIT 1", [$task_id]);

    if (empty($task_detail)) {
        $reply = "❌ <b>Task Tidak Ditemukan</b>\n\n";
        $reply .= "Task ini tidak ada atau sudah diproses.";
        $bot->editMessage($chat_id, $admin_msg_id, $reply, 'HTML');
        return;
    }

    $task = $task_detail[0];
    $user_name = $task['full_name'] ? $task['full_name'] : "User ID: " . $task['user_chatid'];

    $reply = "❌ <b>Tolak Task</b>\n\n";
    $reply .= "Masukkan <b>alasan penolakan</b> untuk task:\n";
    $reply .= "👤 Worker: " . htmlspecialchars($user_name) . " (ID: " . $task['user_chatid'] . ")\n";
    $reply .= "📋 Campaign: " . htmlspecialchars($task['campaign_title']) . "\n\n";
    $reply .= "<i>Alasan ini akan dikirimkan kepada worker.</i>";

    $bot->editMessage($chat_id, $admin_msg_id, $reply, 'HTML');

} else {
    $bot->sendMessage($chat_id, "❌ Perintah tidak dikenali.");
}
?>
