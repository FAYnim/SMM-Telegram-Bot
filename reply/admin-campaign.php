<?php
// Handle admin approve/reject campaign

// Simpan message ID dari callback
$current_msg_id = $bot->getCallbackMessageId();

if (strpos($cb_data, 'admin_approve_campaign_') === 0) {
    // Extract campaign ID
    $campaign_id = str_replace('admin_approve_campaign_', '', $cb_data);

    // Ambil data campaign
    $campaign = db_query("SELECT c.*, u.chatid as client_chatid, u.full_name as client_name, u.username as client_username "
        ."FROM smm_campaigns c "
        ."JOIN smm_users u ON c.client_id = u.id "
        ."WHERE c.id = ?", [$campaign_id]);

    if (empty($campaign)) {
        $bot->editMessage($chat_id, $current_msg_id, "❌ Campaign tidak ditemukan!", 'HTML');
        return;
    }

    $campaign_data = $campaign[0];
    $client_id = $campaign_data['client_id'];
    $client_chatid = $campaign_data['client_chatid'];
    $campaign_balance = $campaign_data['campaign_balance'];
	$campaign_budget = $campaign_data['campaign_budget'];
    $target_total = $campaign_data['target_total'];

    // Cek apakah campaign masih draft
    if ($campaign_data['status'] != 'draft') {
        $bot->editMessage($chat_id, $current_msg_id, "❌ Campaign ini sudah diproses sebelumnya!", 'HTML');
        return;
    }

    // Update status campaign menjadi paused (menunggu topup campaign balance)
    db_execute("UPDATE smm_campaigns SET status = 'paused' WHERE id = ?", [$campaign_id]);
    
    // Notifikasi ke client
    $client_reply = "✅ <b>Campaign Disetujui!</b>\n\n";
    $client_reply .= "Campaign Anda telah diverifikasi dan disetujui oleh admin.\n\n";
    $client_reply .= "<b>📋 Detail Campaign:</b>\n";
    $client_reply .= "🆔 ID: #" . $campaign_id . "\n";
    $client_reply .= "📝 Judul: " . htmlspecialchars($campaign_data['campaign_title']) . "\n";
    $client_reply .= "🎯 Target: " . number_format($target_total) . " tasks\n";
    $client_reply .= "💰 Budget Diperlukan: " . number_format($campaign_budget, 0, ',', '.') . "\n";
    $client_reply .= "🔴 Status: Paused\n\n";
    $client_reply .= "⚠️ <b>Campaign masih dalam status pause.</b>\n\n";
    $client_reply .= "💡 <b>Untuk mengaktifkan campaign:</b>\n";
    $client_reply .= "1. Topup Campaign Balance minimal " . number_format($campaign_budget, 0, ',', '.') . "\n";
    $client_reply .= "2. Campaign akan otomatis aktif setelah saldo cukup\n\n";
    $client_reply .= "<i>Gunakan menu Campaign → Edit Campaign → Topup Balance</i>";
    
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
    $admin_reply = "✅ <b>Campaign Berhasil Disetujui</b>\n\n";
    $admin_reply .= "Campaign #" . $campaign_id . " telah disetujui.\n";
    $admin_reply .= "📊 Status: Paused\n";
    $admin_reply .= "💰 Budget Campaign: " . number_format($campaign_budget, 0, ',', '.') . "\n\n";
    $admin_reply .= "Client perlu topup campaign balance untuk mengaktifkan campaign.";
    
    $bot->editMessage($chat_id, $current_msg_id, $admin_reply, 'HTML');
    
    logMessage('campaign_approved_paused', [
        'campaign_id' => $campaign_id,
        'client_id' => $client_id,
        'admin_id' => $user_id,
        'campaign_budget' => $campaign_budget,
        'new_status' => 'paused'
    ], 'info');
    
} elseif (strpos($cb_data, 'admin_reject_campaign_') === 0) {
    // Trace debug
    logMessage('admin_campaign_reject_triggered', [
        'chat_id' => $chat_id,
        'cb_data' => $cb_data,
        'user_id' => $user_id,
        'role' => $role
    ], 'debug');
    
    // Extract campaign ID
    $campaign_id = str_replace('admin_reject_campaign_', '', $cb_data);
    
    // Update user position untuk minta reject reason
    updateUserPosition($chat_id, 'main', 'campaign_reject_' . $campaign_id);
    
    $reply = "❌ <b>Reject Campaign #" . $campaign_id . "</b>\n\n";
    $reply .= "Silakan masukkan alasan penolakan campaign:";
    
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Batal', 'callback_data' => '/admin_campaign_list']
        ]
    ]);
    
    // Update msg_id di database
    db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$current_msg_id, $chat_id]);
    
    $bot->editMessage($chat_id, $current_msg_id, $reply, 'HTML', $keyboard);
}

?>
