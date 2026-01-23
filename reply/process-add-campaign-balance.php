<?php
require_once 'helpers/error-handler.php';

if ($message && $user[0]['menu'] == 'add_campaign_balance') {
    $campaign_id = $user[0]['submenu'];

    if ($message == '/batal') {
        updateUserPosition($chat_id, 'main');

        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }

        $bot->sendMessage($chat_id, "❌ Pembatalan tambah saldo campaign.");
        return;
    }

    if (!is_numeric($message) || $message <= 0) {
        sendErrorWithBackButton(
            $bot,
            $chat_id,
            $msg_id,
            "❌ Jumlah saldo harus berupa angka positif. Silakan coba lagi:",
            "/edit_campaign_detail_" . $campaign_id,
            "Batal"
        );
        return;
    }

    $add_amount = intval($message);

    if ($add_amount < 10000) {
        sendErrorWithBackButton(
            $bot,
            $chat_id,
            $msg_id,
            "❌ Minimal tambah saldo 10.000. Silakan coba lagi:",
            "/edit_campaign_detail_" . $campaign_id,
            "Batal"
        );
        return;
    }

    $campaign = db_query("SELECT campaign_balance, target_total, completed_count, price_per_task, status FROM smm_campaigns WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);
    $wallet = db_query("SELECT balance FROM smm_wallets WHERE user_id = ?", [$user_id]);

    if (empty($campaign)) {
        sendErrorWithBackButton(
            $bot,
            $chat_id,
            $msg_id,
            "❌ Campaign tidak ditemukan.",
            "/cek_campaign",
            "Batal"
        );
        return;
    }

    if (empty($wallet)) {
        sendErrorWithBackButton(
            $bot,
            $chat_id,
            $msg_id,
            "❌ Wallet tidak ditemukan.",
            "/cek_saldo",
            "Batal"
        );
        return;
    }

    $current_campaign_balance = $campaign[0]['campaign_balance'];
    $user_balance = $wallet[0]['balance'];

    if ($user_balance < $add_amount) {
        sendErrorWithBackButton(
            $bot,
            $chat_id,
            $msg_id,
            "❌ Saldo wallet Anda tidak mencukupi.\n💰 Saldo Anda: " . number_format($user_balance, 0, ',', '.') . "\n💰 Yang dibutuhkan: " . number_format($add_amount, 0, ',', '.'),
            "/edit_campaign_detail_" . $campaign_id,
            "Batal"
        );
        return;
    }

    try {
        $new_user_balance = $user_balance - $add_amount;
        db_update('smm_wallets', ['balance' => $new_user_balance], ['user_id' => $user_id]);

        $new_campaign_balance = $current_campaign_balance + $add_amount;

        $target_total = $campaign[0]['target_total'];
        $completed_count = $campaign[0]['completed_count'];
        $price_per_task = $campaign[0]['price_per_task'];
        $current_status = $campaign[0]['status'];

        $total_task_exist = db_query("SELECT COUNT(*) as count FROM smm_tasks WHERE campaign_id = ?", [$campaign_id]);
        $total_task_exist = $total_task_exist[0]['count'];

        $can_create_tasks = floor($new_campaign_balance / $price_per_task);

        $new_tasks_needed = max(0, $can_create_tasks - $total_task_exist);

        $tasks_generated = 0;
        if ($new_tasks_needed > 0) {
            for ($i = 0; $i < $new_tasks_needed; $i++) {
                $task_data = [
                    'campaign_id' => $campaign_id,
                    'status' => 'available'
                ];
                $task_id = db_create('smm_tasks', $task_data);
                if ($task_id) {
                    $tasks_generated++;
                }
            }

            if ($tasks_generated > 0) {
                $new_target_total = max($target_total, $total_task_exist + $tasks_generated);
                db_update('smm_campaigns', ['target_total' => $new_target_total], ['id' => $campaign_id]);
            }
        }

        db_update('smm_campaigns', ['campaign_balance' => $new_campaign_balance], ['id' => $campaign_id, 'client_id' => $user_id]);

        if ($current_status == 'paused' && $can_create_tasks > $completed_count) {
            db_update('smm_campaigns', ['status' => 'active'], ['id' => $campaign_id]);
        }

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

        updateUserPosition($chat_id, 'add_campaign_balance_success');

        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }

        $reply = "✅ <b>Saldo Campaign Berhasil Ditambahkan!</b>\n\n" .
                "💰 Jumlah yang ditambahkan: " . number_format($add_amount, 0, ',', '.') . "\n" .
                "💸 Saldo Campaign Baru: " . number_format($new_campaign_balance, 0, ',', '.') . "\n" .
                "💰 Sisa Saldo Wallet: " . number_format($new_user_balance, 0, ',', '.') . "\n" .
                "ID Campaign: " . $campaign_id;

        if ($tasks_generated > 0) {
            $reply .= "\n📊 Tasks baru dibuat: " . $tasks_generated . "\n";
        }
        if ($current_status == 'paused') {
            $reply .= "\n🟢 Campaign otomatis diaktifkan!";
        }

        $reply .= "\n\n🔙 Kembali ke menu utama...";

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

        if ($new_msg_id) {
            db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
        }
    } catch (Exception $e) {
        sendErrorWithBackButton(
            $bot,
            $chat_id,
            $msg_id,
            "❌ Terjadi kesalahan saat memproses transaksi. Silakan coba lagi.",
            "/edit_campaign_detail_" . $campaign_id,
            "Batal"
        );
    }
}
?>
