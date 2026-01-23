<?php

require_once 'helpers/error-handler.php';

// Extract Task ID from submenu (format: task_reject_{task_id})
$parts = explode('_', $submenu);
$task_id = $parts[2];

// Validasi Alasan Penolakan
$reason = trim($message);

if (empty($reason)) {
    $error_message = "❌ <b>Alasan Wajib Diisi</b>\n\n";
    $error_message .= "Mohon berikan alasan penolakan agar worker mengerti mengapa task ditolak.\n\n";
    $error_message .= "💡 <b>Tips:</b>\n";
    $error_message .= "• Jelaskan apa yang salah dengan bukti screenshot\n";
    $error_message .= "• Berikan instruksi apa yang perlu diperbaiki\n";
    $error_message .= "• Gunakan bahasa yang sopan dan konstruktif";
    sendSimpleError($bot, $chat_id, $error_message);
    return;
}

// Ambil detail task
$task_detail = db_query("SELECT t.*, c.campaign_title, c.type, u.full_name, u.chatid as user_chatid "
	."FROM smm_tasks t "
    ."JOIN smm_campaigns c ON t.campaign_id = c.id "
    ."JOIN smm_users u ON t.worker_id = u.id "
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

// Update Status Task ke Rejected
$task_update = [
    'status' => 'rejected',
    'reviewed_at' => date('Y-m-d H:i:s')
];
db_update('smm_tasks', $task_update, ['id' => $task_id]);

// Update admin notes di task_proofs
$proof_update = [
    'admin_notes' => $reason
];
db_update('smm_task_proofs', $proof_update, ['task_id' => $task_id]);

// Reset Posisi Admin
$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Hapus pesan prompt admin
$bot->deleteMessage($chat_id, $msg_id);

// --- NOTIFIKASI KE WORKER ---
$user_reply = "❌ <b>Task Ditolak</b>\n\n";
$user_reply .= "Mohon maaf, task Anda tidak dapat disetujui saat ini.\n\n";
$user_reply .= "📋 <b>Detail Task:</b>\n";
$user_reply .= "• Campaign: " . htmlspecialchars($task['campaign_title']) . "\n";
$user_reply .= "• Jenis: " . ucfirst($task['type']) . "\n\n";
$user_reply .= "📝 <b>Alasan Penolakan:</b>\n";
$user_reply .= htmlspecialchars($reason) . "\n\n";
$user_reply .= "Silakan perbaiki bukti task atau hubungi Admin jika ada kesalahan.";

// Keyboard untuk tutup notifikasi
$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '✖️ Tutup Notifikasi', 'callback_data' => 'close_notif']
        ]
    ]
];

$bot->sendMessageWithKeyboard($task['user_chatid'], $user_reply, $keyboard, null, 'HTML');

// --- KONFIRMASI KE ADMIN ---
$admin_reply = "✅ <b>Task Ditolak</b>\n\n";
$admin_reply .= "👤 Worker: " . htmlspecialchars($task['full_name']) . " (ID: " . $task['user_chatid'] . ")\n";
$admin_reply .= "📋 Campaign: " . htmlspecialchars($task['campaign_title']) . "\n";
$admin_reply .= "🎯 Jenis: " . ucfirst($task['type']) . "\n";
$admin_reply .= "📝 Alasan: " . htmlspecialchars($reason) . "\n";
$admin_reply .= "📢 Status: Worker telah dinotifikasi.";

$message_result = $bot->sendMessage($chat_id, $admin_reply, 'HTML');

// Update last msg_id admin
if ($message_result && isset($message_result['result']['message_id'])) {
    $new_msg_id = $message_result['result']['message_id'];
    db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);
}

?>
