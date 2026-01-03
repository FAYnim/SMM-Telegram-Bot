<?php
// Handle edit campaign target input
if ($message && $user[0]['menu'] == 'edit_campaign_target') {
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

        $bot->sendMessage($chat_id, "❌ Pembatalan edit target campaign.");
        return;
    }

    // Validate input
    if (!is_numeric($message) || $message <= 0) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Target harus berupa angka positif. Silakan coba lagi:");
        return;
    }

    $new_target = intval($message);

    // Get current campaign data to check completed count
    $campaign = db_query("SELECT completed_count, target_total FROM smm_campaigns WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

    if (empty($campaign)) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Campaign tidak ditemukan.");
        updateUserPosition($chat_id, 'main');
        return;
    }

    $completed_count = $campaign[0]['completed_count'];
    $current_target = $campaign[0]['target_total'];

    if ($new_target <= $completed_count) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Target harus lebih besar dari yang sudah selesai (" . number_format($completed_count) . "). Silakan coba lagi:");
        return;
    }

    if ($new_target > 100000) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Target maksimal 100.000. Silakan coba lagi:");
        return;
    }

    // Update campaign target
    $update_result = db_update('smm_campaigns', ['target_total' => $new_target], ['id' => $campaign_id, 'client_id' => $user_id]);

    if ($update_result) {
        // Reset position
//        updateUserPosition($chat_id, 'main');

        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }

        $reply = "✅ <b>Target Campaign Berhasil Diubah!</b>\n\n" .
                "🎯 Target Baru: " . number_format($new_target) . " tugas\n" .
                "✅ Sudah Selesai: " . number_format($completed_count) . " tugas\n" .
                "📊 Sisa Target: " . number_format($new_target - $completed_count) . " tugas\n" .
                "ID Campaign: " . $campaign_id . "\n\n" .
                "🔙 Kembali ke menu utama...";

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '📋 Lihat Campaign', 'callback_data' => '/cek_campaign']
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
    } else {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Gagal mengubah target campaign. Silakan coba lagi.");
    }
}
?>
