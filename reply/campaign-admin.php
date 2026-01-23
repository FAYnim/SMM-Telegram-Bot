<?php
	$update_result = updateUserPosition($chat_id, 'campaign_admin', '');
	
	if(!$update_result) {
		$bot->sendMessage($chat_id, '❌ Terjadi kesalahan sistem!');
		return;
	}
	
	$sql = db_query("SELECT id, client_id, campaign_title, type, link_target, price_per_task, target_total, campaign_balance, campaign_budget, created_at FROM smm_campaigns WHERE status = 'draft' ORDER BY id DESC LIMIT 0,1");
		
	if(count($sql) > 0) {
		$campaign_data = $sql[0];
		$campaign_id = $campaign_data["id"];
		$client_id = $campaign_data["client_id"];
		$campaign_title = $campaign_data["campaign_title"];
		$type = $campaign_data["type"];
		$link_target = $campaign_data["link_target"];
		$price_per_task = $campaign_data["price_per_task"];
		$target_total = $campaign_data["target_total"];
		$campaign_balance = $campaign_data["campaign_balance"];
		$campaign_budget = $campaign_data["campaign_budget"];
		$created_at = $campaign_data["created_at"];

		// Get user data
		$user = db_read("smm_users", ["id" => $client_id]);
		
		if(empty($user)) {
			$reply = "❌ User not found";
			$keyboard = $bot->buildInlineKeyboard([
				[
					["text" => "Kembali", "callback_data" => "/start"]
				]
			]);
			$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, "HTML", $keyboard);
			return;
		}
		
		$user_data = $user[0];
		$user_username = $user_data["username"];
		$user_chat_id = $user_data["chatid"];
		$full_name = $user_data["full_name"];
		
		// Format waktu
		$campaign_time = date('d M Y, H:i', strtotime($created_at));
		
		// Format type
		$type_labels = [
			'view' => '👁️ Views',
			'like' => '❤️ Likes',
			'comment' => '💬 Comments',
			'share' => '🔄 Shares',
			'follow' => '👥 Follows'
		];
		$type_label = isset($type_labels[$type]) ? $type_labels[$type] : ucfirst($type);
		
		// Hapus pesan yang memicu callback
		$bot->deleteMessage($chat_id, $bot->getCallbackMessageId());
		
		// Kirim pesan detail campaign ke admin
		$reply = "🔔 <b>CAMPAIGN BARU</b>\n\n";
		$reply .= "👤 Nama: " . $full_name . "\n";
		$reply .= "🆔 User ID: <code>" . $user_chat_id . "</code>\n";
		if($user_username) {
			$reply .= "📝 Username: @" . $user_username . "\n";
		}
		$reply .= "🕐 Waktu: " . $campaign_time . "\n";
		$reply .= "📋 Campaign ID: <code>" . $campaign_id . "</code>\n\n";
		$reply .= "<b>📝 Detail Campaign:</b>\n";
		$reply .= "🎬 Judul: " . htmlspecialchars($campaign_title) . "\n";
		$reply .= "📊 Jenis: " . $type_label . "\n";
		$reply .= "🔗 Link: " . htmlspecialchars($link_target) . "\n";
		$reply .= "💰 Reward: " . number_format($price_per_task, 0, ',', '.') . "\n";
		$reply .= "🎯 Target: " . number_format($target_total) . " tasks\n";
		
		$bot->sendMessage($chat_id, $reply, null, 'HTML');
		
		// Kirim pesan menu aksi baru dengan keyboard
		$reply = "👇 <b>Tindakan Admin</b>\n";
		$reply .= "Silakan review campaign di atas.\n";
		$reply .= "Apakah campaign ini disetujui atau ditolak?";
		
		$keyboard = $bot->buildInlineKeyboard([
			[
				["text" => "✅ Terima", "callback_data" => "admin_approve_campaign_".$campaign_id],
				["text" => "❌ Tolak", "callback_data" => "admin_reject_campaign_".$campaign_id],
			],
			[
				["text" => "Kembali", "callback_data" => "/start"]
			]
		]);
		
		$bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
		
	} else {
		// Tidak ada campaign pending
		$reply = "📋 <b>Daftar Campaign Pending</b>\n\n";
		$reply .= "✨ Tidak ada campaign yang menunggu verifikasi saat ini.";
		
		$keyboard = $bot->buildInlineKeyboard([
			[
				["text" => "Kembali", "callback_data" => "/start"]
			]
		]);
		
		$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, "HTML", $keyboard);
	}
?>
