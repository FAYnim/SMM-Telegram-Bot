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
    $target_total = $campaign_data['target_total'];

    // Cek apakah campaign masih draft
    if ($campaign_data['status'] != 'draft') {
        $bot->editMessage($chat_id, $current_msg_id, "❌ Campaign ini sudah diproses sebelumnya!", 'HTML');
        return;
    }

    // Ambil wallet client
    $wallet = db_read('smm_wallets', ['user_id' => $client_id]);
    
    if (empty($wallet)) {
        // Buat wallet jika belum ada
        $wallet_data = ['user_id' => $client_id];
        db_create('smm_wallets', $wallet_data);
        $wallet = db_read('smm_wallets', ['user_id' => $client_id]);
    }
    
    $wallet_data = $wallet[0];
    $wallet_id = $wallet_data['id'];
    $balance_before = $wallet_data['balance'];
    
    // Cek saldo cukup atau tidak
    if ($balance_before >= $campaign_balance) {
        // SALDO CUKUP - Active campaign
        
        // Kurangi saldo wallet client
        $balance_after = $balance_before - $campaign_balance;
        db_execute("UPDATE smm_wallets SET balance = ? WHERE id = ?", [$balance_after, $wallet_id]);
        
        // Buat record transaksi
        $transaction_data = [
            'wallet_id' => $wallet_id,
            'type' => 'adjustment',
            'amount' => -$campaign_balance,
            'balance_before' => $balance_before,
            'balance_after' => $balance_after,
            'description' => "Pembayaran campaign #".$campaign_id." - ".$campaign_data['campaign_title'],
            'reference_id' => $campaign_id,
            'status' => 'approved'
        ];
        db_create('smm_wallet_transactions', $transaction_data);
        
        // Generate tasks untuk campaign
        $tasks_generated = 0;
        for ($i = 0; $i < $target_total; $i++) {
            $task_data = [
                'campaign_id' => $campaign_id,
                'status' => 'available'
            ];
            
            $task_id = db_create('smm_tasks', $task_data);
            if ($task_id) {
                $tasks_generated++;
            }
        }
        
        // Update status campaign menjadi active
        db_execute("UPDATE smm_campaigns SET status = 'active' WHERE id = ?", [$campaign_id]);
        
        // Notifikasi ke client
        $client_reply = "✅ <b>Campaign Disetujui!</b>\n\n";
        $client_reply .= "Campaign Anda telah diverifikasi dan diaktifkan.\n\n";
        $client_reply .= "<b>📋 Detail Campaign:</b>\n";
        $client_reply .= "🆔 ID: #" . $campaign_id . "\n";
        $client_reply .= "📝 Judul: " . htmlspecialchars($campaign_data['campaign_title']) . "\n";
        $client_reply .= "🎯 Target: " . number_format($target_total) . " tasks\n";
        $client_reply .= "💰 Total Budget: Rp " . number_format($campaign_balance, 0, ',', '.') . "\n";
        $client_reply .= "📊 Tasks Generated: " . $tasks_generated . "\n";
        $client_reply .= "💳 Saldo Terpotong: Rp " . number_format($campaign_balance, 0, ',', '.') . "\n\n";
        $client_reply .= "Campaign Anda sekarang aktif dan siap menerima workers!";
        
        $bot->sendMessage($client_chatid, $client_reply);
        
        // Notifikasi ke admin
        $admin_reply = "✅ <b>Campaign Berhasil Disetujui</b>\n\n";
        $admin_reply .= "Campaign #" . $campaign_id . " telah diaktifkan.\n";
        $admin_reply .= "💳 Saldo client: Rp " . number_format($balance_before, 0, ',', '.') . " → Rp " . number_format($balance_after, 0, ',', '.') . "\n";
        $admin_reply .= "📊 Tasks generated: " . $tasks_generated . "/" . $target_total;
        
        $bot->editMessage($chat_id, $current_msg_id, $admin_reply, 'HTML');
        
        logMessage('campaign_approved', [
            'campaign_id' => $campaign_id,
            'client_id' => $client_id,
            'admin_id' => $user_id,
            'campaign_balance' => $campaign_balance,
            'tasks_generated' => $tasks_generated,
            'new_status' => 'active'
        ], 'info');
        
    } else {
        // SALDO TIDAK CUKUP - Paused campaign
        
        // Update status campaign menjadi paused
        db_execute("UPDATE smm_campaigns SET status = 'paused' WHERE id = ?", [$campaign_id]);
        
        // Notifikasi ke client
        $client_reply = "⚠️ <b>Campaign Disetujui - Saldo Tidak Cukup</b>\n\n";
        $client_reply .= "Campaign Anda telah diverifikasi oleh admin.\n\n";
        $client_reply .= "<b>📋 Detail Campaign:</b>\n";
        $client_reply .= "🆔 ID: #" . $campaign_id . "\n";
        $client_reply .= "📝 Judul: " . htmlspecialchars($campaign_data['campaign_title']) . "\n";
        $client_reply .= "💰 Total Budget: Rp " . number_format($campaign_balance, 0, ',', '.') . "\n";
        $client_reply .= "💳 Saldo Anda: Rp " . number_format($balance_before, 0, ',', '.') . "\n\n";
        $client_reply .= "❌ <b>Saldo tidak mencukupi!</b>\n";
        $client_reply .= "Campaign akan di-pause sampai saldo Anda cukup.\n\n";
        $client_reply .= "Silakan top-up minimal Rp " . number_format($campaign_balance - $balance_before, 0, ',', '.') . " untuk mengaktifkan campaign.";
        
        $bot->sendMessage($client_chatid, $client_reply);
        
        // Notifikasi ke admin
        $admin_reply = "⚠️ <b>Campaign Disetujui - Status Paused</b>\n\n";
        $admin_reply .= "Campaign #" . $campaign_id . " diverifikasi.\n";
        $admin_reply .= "💳 Saldo client: Rp " . number_format($balance_before, 0, ',', '.') . "\n";
        $admin_reply .= "💰 Dibutuhkan: Rp " . number_format($campaign_balance, 0, ',', '.') . "\n";
        $admin_reply .= "❌ Saldo tidak cukup - campaign di-pause.\n\n";
        $admin_reply .= "Client perlu top-up untuk mengaktifkan campaign.";
        
        $bot->editMessage($chat_id, $current_msg_id, $admin_reply, 'HTML');
        
        logMessage('campaign_approved_paused', [
            'campaign_id' => $campaign_id,
            'client_id' => $client_id,
            'admin_id' => $user_id,
            'campaign_balance' => $campaign_balance,
            'client_balance' => $balance_before,
            'new_status' => 'paused'
        ], 'info');
    }
    
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
