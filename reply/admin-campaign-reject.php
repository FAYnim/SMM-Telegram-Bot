<?php
// Handle admin reject campaign with reason

// Trace debug - file executed
logMessage('admin_campaign_reject_file_executed', [
    'chat_id' => $chat_id,
    'user_id' => $user_id,
    'submenu' => $submenu,
    'message' => $message,
    'role' => $role
], 'debug');

// Extract campaign ID dari submenu
$campaign_id = str_replace('campaign_reject_', '', $submenu);

// Ambil reject reason dari message
$reject_reason = trim($message);

if (empty($reject_reason)) {
    $bot->sendMessage($chat_id, "❌ Alasan penolakan tidak boleh kosong!\n\nSilakan masukkan alasan penolakan:");
    return;
}

// Ambil data campaign
$campaign = db_query("SELECT c.*, u.chatid as client_chatid, u.full_name as client_name "
    ."FROM smm_campaigns c "
    ."JOIN smm_users u ON c.client_id = u.id "
    ."WHERE c.id = ?", [$campaign_id]);

if (empty($campaign)) {
    $bot->sendMessage($chat_id, "❌ Campaign tidak ditemukan!");
    return;
}

$campaign_data = $campaign[0];
$client_chatid = $campaign_data['client_chatid'];

// Update status campaign menjadi deleted
db_execute("UPDATE smm_campaigns SET status = 'deleted' WHERE id = ?", [$campaign_id]);

// Hapus pesan lama dengan msg_id jika ada
if ($msg_id) {
    $bot->deleteMessage($chat_id, $msg_id);
}

// Reset user position
$update_result = updateUserPosition($chat_id, 'main', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Notifikasi ke client
$client_reply = "❌ <b>Campaign Ditolak</b>\n\n";
$client_reply .= "Campaign Anda telah ditolak oleh admin.\n\n";
$client_reply .= "<b>📋 Detail Campaign:</b>\n";
$client_reply .= "🆔 ID: #" . $campaign_id . "\n";
$client_reply .= "📝 Judul: " . htmlspecialchars($campaign_data['campaign_title']) . "\n";
$client_reply .= "🎯 Tipe: " . ucfirst($campaign_data['type']) . "s\n";
$client_reply .= "💰 Total Budget: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n\n";
$client_reply .= "📝 <b>Alasan:</b>\n<i>" . htmlspecialchars($reject_reason) . "</i>\n\n";
$client_reply .= "Silakan perbaiki dan buat campaign baru.";

// Keyboard untuk tutup notifikasi
$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '✖️ Tutup Notifikasi', 'callback_data' => 'close_notif']
        ]
    ]
];

$bot->sendMessageWithKeyboard($client_chatid, $client_reply, $keyboard, null, 'HTML');

// Notifikasi ke admin
$admin_reply = "✅ <b>Campaign Berhasil Ditolak</b>\n\n";
$admin_reply .= "Campaign #" . $campaign_id . " telah ditolak.\n";
$admin_reply .= "👤 Client: " . htmlspecialchars($campaign_data['client_name']) . "\n";
$admin_reply .= "📝 Alasan: " . htmlspecialchars($reject_reason);

$result = $bot->sendMessage($chat_id, $admin_reply);
$new_msg_id = $result['result']['message_id'] ?? null;

// Update msg_id baru di database
if ($new_msg_id) {
    db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
}

logMessage('campaign_rejected', [
    'campaign_id' => $campaign_id,
    'client_id' => $campaign_data['client_id'],
    'admin_id' => $user_id,
    'reject_reason' => $reject_reason
], 'info');

?>
