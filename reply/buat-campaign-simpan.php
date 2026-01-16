<?php
$update_result = updateUserPosition($chat_id, 'buat_campaign_finished');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Kirim pesan "sedang diproses"
$reply = "⏳ <b>Campaign Sedang Diproses</b>\n\n";
$reply .= "Mohon tunggu, sedang mengirim campaign ke admin untuk verifikasi...\n";

$keyboard = []; // Empty keyboard - no buttons during processing
$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

// Ambil campaign yang baru dibuat (status 'creating')
$campaign = db_query("SELECT id, campaign_title, type, link_target, target_total, campaign_balance, price_per_task "
    ."FROM smm_campaigns "
    ."WHERE client_id = ? AND status = 'creating' "
    ."ORDER BY updated_at DESC LIMIT 1", [$user_id]);

if (!empty($campaign)) {
    $campaign_data = $campaign[0];
    $campaign_id = $campaign_data['id'];

// Update status campaign menjadi draft (menunggu verifikasi admin)
    db_execute("UPDATE smm_campaigns SET status = 'draft' WHERE id = ?", [$campaign_id]);

    // Kirim notifikasi ke admin dengan permission campaign_verify
    $admin_chat_ids = getAdminChatIdsByPermission('campaign_verify');
    
    if ($admin_chat_ids) {
        $admin_reply = "🔔 <b>Campaign Baru Menunggu Verifikasi</b>\n\n";
        $admin_reply .= "<b>📋 Detail Campaign:</b>\n";
        $admin_reply .= "🆔 ID: #" . $campaign_data['id'] . "\n";
        $admin_reply .= "👤 Client: " . htmlspecialchars($user[0]['full_name']) . " (@" . $user[0]['username'] . ")\n";
        $admin_reply .= "📝 Judul: " . htmlspecialchars($campaign_data['campaign_title']) . "\n";
        $admin_reply .= "🎯 Tipe: " . ucfirst($campaign_data['type']) . "s\n";
        $admin_reply .= "🔗 Link: " . $campaign_data['link_target'] . "\n";
        $admin_reply .= "💰 Harga/task: Rp " . number_format($campaign_data['price_per_task'], 0, ',', '.') . "\n";
        $admin_reply .= "🎯 Target: " . number_format($campaign_data['target_total']) . " tasks\n";
        $admin_reply .= "💰 Total Budget: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n\n";
        $admin_reply .= "Silakan verifikasi campaign ini.";
        
        $admin_keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '✅ Approve', 'callback_data' => '/admin_approve_campaign_' . $campaign_id],
                ['text' => '❌ Reject', 'callback_data' => '/admin_reject_campaign_' . $campaign_id]
            ]
        ]);
        
        foreach ($admin_chat_ids as $admin_chatid) {
            $bot->sendMessageWithKeyboard($admin_chatid, $admin_reply, $admin_keyboard, null, 'HTML');
            sleep(1);
        }
    }

    logMessage('campaign_draft', [
        'campaign_id' => $campaign_id,
        'user_id' => $user_id,
        'campaign_balance' => $campaign_data['campaign_balance'],
        'target_total' => $campaign_data['target_total']
    ], 'debug');
}

if (!empty($campaign)) {
    $campaign_data = $campaign[0];

    $reply = "<b>✅ Campaign Berhasil Dibuat!</b>\n\n";
    $reply .= "Campaign Anda telah dikirim ke admin untuk verifikasi.\n\n";
    $reply .= "<b>📋 Ringkasan Campaign:</b>\n";
    $reply .= "🆔 ID: #" . $campaign_data['id'] . "\n";
    $reply .= "📝 Judul: " . htmlspecialchars($campaign_data['campaign_title']) . "\n";
    $reply .= "🎯 Tipe: " . ucfirst($campaign_data['type']) . "s\n";
    $reply .= "🎯 Target: " . number_format($campaign_data['target_total']) . " tasks\n";
    $reply .= "💰 Total Budget: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n";
    $reply .= "📊 Status: <i>Menunggu Verifikasi Admin</i>\n\n";
    $reply .= "Anda akan mendapat notifikasi setelah campaign diverifikasi.";
} else {
    $reply = "<b>❌ Gagal membuat campaign!</b>\n\n";
    $reply .= "Terjadi kesalahan saat membuat campaign. Silakan coba lagi.";
}

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '📋 Lihat Campaign Saya', 'callback_data' => '/cek_campaign'],
    ],
    [
        ['text' => '🔙 Kembali ke Menu Utama', 'callback_data' => '/start']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
