<?php
// Handle edit campaign title input
if ($message && $user[0]['menu'] == 'edit_campaign_title') {
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

        $bot->sendMessage($chat_id, "❌ Pembatalan edit judul campaign.");
        return;
    }

    // Validate input
    $title = trim($message);

    if (strlen($title) < 5) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Judul campaign minimal 5 karakter. Silakan coba lagi:");
        return;
    }

    if (strlen($title) > 100) {
        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }
        $bot->sendMessage($chat_id, "❌ Judul campaign maksimal 100 karakter. Silakan coba lagi:");
        return;
    }

    // Update campaign title
    $update_result = db_update('smm_campaigns', ['campaign_title' => $title], ['id' => $campaign_id, 'client_id' => $user_id]);

    if ($update_result) {
        // Reset position
//        updateUserPosition($chat_id, 'main');

        // Delete previous message
        if ($msg_id) {
            $bot->deleteMessage($chat_id, $msg_id);
        }

        $reply = "✅ <b>Judul Campaign Berhasil Diubah!</b>\n\n" .
                "📝 Judul Baru: " . $title . "\n" .
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
        $bot->sendMessage($chat_id, "❌ Gagal mengubah judul campaign. Silakan coba lagi.");
    }
}
?>
