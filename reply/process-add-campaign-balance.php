<?php
// Handle add campaign balance input
if ($message && $user[0]['menu'] == 'add_campaign_balance') {
    // Get campaign id from submenu
    $campaign_id = $user[0]['submenu'];

    // Check for cancel
    if ($message == '/batal') {
        // Reset position
        updateUserPosition($chat_id, 'main');

        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }

        $bot->sendMessage($chat_id, "❌ Pembatalan tambah saldo campaign.");
        return;
    }

    // Validate input
    if (!is_numeric($message) || $message <= 0) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Jumlah saldo harus berupa angka positif. Silakan coba lagi:");
        return;
    }

    $add_amount = intval($message);

    if ($add_amount < 10000) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Minimal tambah saldo Rp 10.000. Silakan coba lagi:");
        return;
    }

    // Get current campaign data and user wallet
    $campaign = db_query("SELECT campaign_balance FROM smm_campaigns WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);
    $wallet = db_query("SELECT balance FROM smm_wallets WHERE user_id = ?", [$user_id]);

    if (empty($campaign)) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Campaign tidak ditemukan.");
        updateUserPosition($chat_id, 'main');
        return;
    }

    if (empty($wallet)) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Wallet tidak ditemukan.");
        updateUserPosition($chat_id, 'main');
        return;
    }

    $current_campaign_balance = $campaign[0]['campaign_balance'];
    $user_balance = $wallet[0]['balance'];

    // Check if user has enough balance
    if ($user_balance < $add_amount) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Saldo wallet Anda tidak mencukupi.\n💰 Saldo Anda: Rp " . number_format($user_balance, 0, ',', '.') . "\n💰 Yang dibutuhkan: Rp " . number_format($add_amount, 0, ',', '.'));
        return;
    }

    // Start transaction
    try {
        // Deduct from user wallet
        $new_user_balance = $user_balance - $add_amount;
        db_update('smm_wallets', ['balance' => $new_user_balance], ['user_id' => $user_id]);

        // Add to campaign balance
        $new_campaign_balance = $current_campaign_balance + $add_amount;
        db_update('smm_campaigns', ['campaign_balance' => $new_campaign_balance], ['id' => $campaign_id, 'client_id' => $user_id]);

        // Create wallet transaction record
        $transaction_data = [
            'user_id' => $user_id,
            'type' => 'campaign_balance_add',
            'amount' => $add_amount,
            'description' => 'Tambah saldo campaign ID: ' . $campaign_id,
            'balance_before' => $user_balance,
            'balance_after' => $new_user_balance,
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s')
        ];
        db_create('smm_wallet_transactions', $transaction_data);

        // Reset position
//        updateUserPosition($chat_id, 'main');

        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }

        $reply = "✅ <b>Saldo Campaign Berhasil Ditambahkan!</b>\n\n" .
                "💰 Jumlah yang ditambahkan: Rp " . number_format($add_amount, 0, ',', '.') . "\n" .
                "💸 Saldo Campaign Baru: Rp " . number_format($new_campaign_balance, 0, ',', '.') . "\n" .
                "💰 Sisa Saldo Wallet: Rp " . number_format($new_user_balance, 0, ',', '.') . "\n" .
                "ID Campaign: " . $campaign_id . "\n\n" .
                "🔙 Kembali ke menu utama...";

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '📋 Lihat Campaign', 'callback_data' => '/cek_campaign']
            ],
            [
                ['text' => '💰 Cek Saldo', 'callback_data' => '/cek_saldo']
            ],
            [
                ['text' => '🏠 Menu Utama', 'callback_data' => '/start']
            ]
        ]);

		$result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);
		$new_msg_id = $result['result']['message_id'] ?? null;

		// Update msg_id baru di database
		if ($new_msg_id) {
		    db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
		}
    } catch (Exception $e) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Terjadi kesalahan saat memproses transaksi. Silakan coba lagi.");
    }
}
?>
