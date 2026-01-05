<?php
$update_result = updateUserPosition($chat_id, 'buat_campaign_finished');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Kirim pesan "sedang diproses"
$reply = "⏳ <b>Campaign Sedang Dibuat</b>\n\n";
$reply .= "Mohon tunggu, sedang membuat tasks untuk campaign Anda...\n";

$keyboard = []; // Empty keyboard - no buttons during processing
$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

// Ambil campaign yang baru dibuat (status 'creating')
$campaign = db_query("SELECT id, campaign_title, type, target_total, campaign_balance, price_per_task "
    ."FROM smm_campaigns "
    ."WHERE client_id = ? AND status = 'creating' "
    ."ORDER BY updated_at DESC LIMIT 1", [$user_id]);

if (!empty($campaign)) {
    $campaign_data = $campaign[0];
    $campaign_id = $campaign_data['id'];
    $target_total = $campaign_data['target_total'];
    $price_per_task = $campaign_data['price_per_task'];
    $campaign_balance = $campaign_data['campaign_balance'];

    // Ambil wallet client
    $wallet = db_read('smm_wallets', ['user_id' => $user_id]);

    if (empty($wallet)) {
        $bot->editMessage($chat_id, $msg_id, "❌ <b>Gagal Membuat Campaign</b>\n\nWallet tidak ditemukan. Silakan hubungi admin.", 'HTML', []);
        return;
    }

    $wallet_data = $wallet[0];
    $wallet_id = $wallet_data['id'];
    $balance_before = $wallet_data['balance'];

    // Cek saldo cukup
    if ($balance_before < $campaign_balance) {
        $bot->editMessage($chat_id, $msg_id, "❌ <b>Saldo Tidak Cukup</b>\n\nSaldo Anda: Rp ".number_format($balance_before, 0, ',', '.')."\nDibutuhkan: Rp ".number_format($campaign_balance, 0, ',', '.')."\n\nSilakan top-up terlebih dahulu.", 'HTML', []);

        // Hapus campaign yang gagal
        db_execute("DELETE FROM smm_campaigns WHERE id = ?", [$campaign_id]);
        return;
    }

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

    logMessage('task_generation', [
        'campaign_id' => $campaign_id,
        'target_total' => $target_total,
        'tasks_generated' => $tasks_generated,
        'user_id' => $user_id,
        'campaign_balance' => $campaign_balance,
        'wallet_balance_before' => $balance_before,
        'wallet_balance_after' => $balance_after
    ], 'debug');
}

if (!empty($campaign)) {
    $campaign_data = $campaign[0];

    $reply = "<b>✅ Campaign Berhasil Disimpan!</b>\n\n";
    $reply .= "Campaign Anda telah aktif dan siap menerima tugas dari workers.\n\n";
    $reply .= "<b>📋 Ringkasan Campaign:</b>\n";
    $reply .= "🆔 ID: #" . $campaign_data['id'] . "\n";
    $reply .= "📝 Judul: " . htmlspecialchars($campaign_data['campaign_title']) . "\n";
    $reply .= "🎯 Tipe: " . ucfirst($campaign_data['type']) . "s\n";
    $reply .= "🎯 Target: " . number_format($campaign_data['target_total']) . " tasks\n";
    $reply .= "💰 Total Budget: Rp " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n";
    $reply .= "📊 Tasks Generated: " . $tasks_generated . "/" . $target_total . "\n\n";
    $reply .= "Anda dapat memantau progress campaign di menu \"Campaignku\".";
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
