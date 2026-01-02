<?php
// Validasi input target
$target = trim($message);
if (empty($target)) {
    $bot->sendMessage($chat_id, "❌ Target total tidak boleh kosong!\n\nSilakan masukkan target total:");
    return;
}

// Validasi numeric
if (!is_numeric($target) || $target <= 0) {
    $bot->sendMessage($chat_id, "❌ Target total harus berupa angka positif!\n\nSilakan masukkan target total:");
    return;
}

// Update target campaign di database
db_execute("UPDATE smm_campaigns SET target_total = ? WHERE client_id = ? AND status = 'creating'", [$target, $user_id]);

// Hapus pesan lama dengan msg_id
if ($msg_id) {
    $bot->deleteMessage($chat_id, $msg_id);
}

$update_result = updateUserPosition($chat_id, 'buat_campaign_finish');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Ambil semua data campaign yang baru dibuat
$campaign = db_query("SELECT id, campaign_title, type, link_target, price_per_task, target_total, campaign_balance, created_at "
    ."FROM smm_campaigns "
    ."WHERE client_id = ? AND status = 'creating' "
    ."ORDER BY created_at DESC LIMIT 1", [$user_id]);

if (!empty($campaign)) {
    $campaign_data = $campaign[0];

    $reply = "<b>📋 Konfirmasi Campaign Baru</b>\n\n";
    $reply .= "Silakan periksa detail campaign Anda:\n\n";
    $reply .= "<b>📋 Detail Campaign:</b>\n";
    $reply .= "🆔 ID: #" . $campaign_data['id'] . "\n";
    $reply .= "📝 Judul: " . htmlspecialchars($campaign_data['campaign_title']) . "\n";
    $reply .= "🎯 Tipe: " . ucfirst($campaign_data['type']) . "s\n";
    $reply .= "🔗 Link: " . $campaign_data['link_target'] . "\n";
    $reply .= "💰 Harga/task: Rp " . number_format($campaign_data['price_per_task'], 0, ',', '.') . "\n";
    $reply .= "🎯 Target: " . number_format($campaign_data['target_total']) . " tasks\n";
    $reply .= "💰 Total Budget: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n";
    $reply .= "📅 Dibuat: " . date('d/m/Y H:i', strtotime($campaign_data['created_at'])) . "\n\n";
    $reply .= "Apakah detail campaign sudah benar?";
} else {
    $reply = "<b>❓ Konfirmasi Campaign</b>\n\n";
    $reply .= "Apakah Anda ingin menyimpan campaign ini?";
}

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '✅ Simpan Campaign', 'callback_data' => '/simpan_campaign'],
    ],
    [
        ['text' => '❌ Batal', 'callback_data' => '/cek_campaign']
    ]
]);

// Kirim pesan baru dengan keyboard dan dapatkan msg_id baru
$result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);
$new_msg_id = $result['result']['message_id'] ?? null;

// Update msg_id baru di database
if ($new_msg_id) {
    db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
}

?>
