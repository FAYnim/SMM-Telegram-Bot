<?php

require_once 'helpers/error-handler.php';

$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $error_message = "❌ <b>Gagal Update Position</b>\n\n";
    $error_message .= "Terjadi kesalahan saat memperbarui status admin.\n";
    $error_message .= "Silakan coba lagi atau hubungi developer jika masalah berlanjut.";
    sendSimpleError($bot, $chat_id, $error_message);
    return;
}

$bot->deleteMessage($chat_id, $msg_id);

// Cek apakah user adalah admin
$admin = db_read('smm_admins', ['chatid' => $chat_id]);
if (empty($admin)) {
    $error_message = "❌ <b>Akses Ditolak</b>\n\n";
    $error_message .= "Anda tidak memiliki akses sebagai admin.\n";
    $error_message .= "Fitur ini hanya tersedia untuk admin yang terdaftar.";
    sendSimpleError($bot, $chat_id, $error_message);
    return;
}

$admin_msg_id = $bot->getCallbackMessageId();
if(!$admin_msg_id) {
    $error_message = "❌ <b>Gagal Ambil Message ID</b>\n\n";
    $error_message .= "Terjadi kesalahan teknis saat mengambil ID pesan.\n";
    $error_message .= "Silakan coba lagi atau hubungi developer.";
    sendSimpleError($bot, $chat_id, $error_message);
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
        $error_message = "❌ <b>Gagal Update Position</b>\n\n";
        $error_message .= "Terjadi kesalahan saat memperbarui posisi admin untuk approve task.\n";
        $error_message .= "Silakan coba lagi.";
        sendSimpleError($bot, $chat_id, $error_message);
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
        $error_message = "❌ <b>Task Tidak Ditemukan</b>\n\n";
        $error_message .= "Task ID: <code>" . $task_id . "</code>\n\n";
        $error_message .= "Task ini tidak ada atau sudah diproses oleh admin lain.\n";
        $error_message .= "Silakan cek daftar task yang pending review.";
        
        $bot->editMessage($chat_id, $admin_msg_id, $error_message, 'HTML');
        return;
    }

    $task = $task_detail[0];
    $user_name = $task['full_name'] ? $task['full_name'] : "User ID: " . $task['user_chatid'];

    $reply = "✅ <b>Setujui Task</b>\n\n";
    $reply .= "Konfirmasi approve task untuk:\n";
    $reply .= "👤 Worker: " . htmlspecialchars($user_name) . " (ID: " . $task['user_chatid'] . ")\n";
    $reply .= "📋 Campaign: " . htmlspecialchars($task['campaign_title']) . "\n";
    $reply .= "🎯 Jenis: " . ucfirst($task['type']) . "\n";
    $reply .= "💰 Reward: <b>" . number_format($task['price_per_task'], 0, ',', '.') . "</b>\n\n";
    $reply .= "Ketik <b>YA</b> untuk konfirmasi approve:";

    $bot->editMessage($chat_id, $admin_msg_id, $reply, 'HTML');

} elseif(strpos($cb_data, "reject") !== false) {
    $update_result = updateUserPosition($chat_id, 'main', 'task_reject_'.$task_id);
    if (!$update_result) {
        $error_message = "❌ <b>Gagal Update Position</b>\n\n";
        $error_message .= "Terjadi kesalahan saat memperbarui posisi admin untuk reject task.\n";
        $error_message .= "Silakan coba lagi.";
        sendSimpleError($bot, $chat_id, $error_message);
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
        $error_message = "❌ <b>Task Tidak Ditemukan</b>\n\n";
        $error_message .= "Task ID: <code>" . $task_id . "</code>\n\n";
        $error_message .= "Task ini tidak ada atau sudah diproses oleh admin lain.\n";
        $error_message .= "Silakan cek daftar task yang pending review.";
        
        $bot->editMessage($chat_id, $admin_msg_id, $error_message, 'HTML');
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
    $error_message = "❌ <b>Perintah Tidak Dikenali</b>\n\n";
    $error_message .= "Callback data: <code>" . htmlspecialchars($cb_data) . "</code>\n\n";
    $error_message .= "Perintah yang Anda coba jalankan tidak valid.\n";
    $error_message .= "Silakan gunakan tombol yang tersedia atau hubungi developer.";
    sendSimpleError($bot, $chat_id, $error_message);
}
?>
