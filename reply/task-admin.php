<?php
	$update_result = updateUserPosition($chat_id, 'task_admin', '');
	
	if(!$update_result) {
		$bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
		return;
	}
	
	$sql = db_query("SELECT t.id as task_id, t.campaign_id, t.worker_id, t.completed_at, 
		c.campaign_title, c.type, c.link_target, c.price_per_task,
		u.chatid as worker_chatid, u.username as worker_username, u.full_name as worker_name,
		tp.proof_image_path
		FROM smm_tasks t
		JOIN smm_campaigns c ON t.campaign_id = c.id
		JOIN smm_users u ON t.worker_id = u.id
		LEFT JOIN smm_task_proofs tp ON t.id = tp.task_id
		WHERE t.status = 'pending_review'
		ORDER BY t.id DESC LIMIT 0,1");
		
	if(count($sql) > 0) {
		$task_data = $sql[0];
		$task_id = $task_data["task_id"];
		$campaign_id = $task_data["campaign_id"];
		$worker_id = $task_data["worker_id"];
		$completed_at = $task_data["completed_at"];
		$campaign_title = $task_data["campaign_title"];
		$type = $task_data["type"];
		$link_target = $task_data["link_target"];
		$price_per_task = $task_data["price_per_task"];
		$worker_chatid = $task_data["worker_chatid"];
		$worker_username = $task_data["worker_username"];
		$worker_name = $task_data["worker_name"];
		$proof_image_path = $task_data["proof_image_path"];
		
		// Format waktu
		$completed_time = date('d M Y, H:i', strtotime($completed_at));
		
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
		
		// Kirim bukti foto ke admin
		$caption = "🔔 <b>TASK BARU UNTUK VERIFIKASI</b>\n\n";
		$caption .= "👤 Worker: " . $worker_name . "\n";
		$caption .= "🆔 Worker ID: <code>" . $worker_chatid . "</code>\n";
		if($worker_username) {
			$caption .= "📝 Username: @" . $worker_username . "\n";
		}
		$caption .= "🕐 Waktu submit: " . $completed_time . "\n";
		$caption .= "📋 Task ID: <code>" . $task_id . "</code>\n\n";
		$caption .= "<b>📝 Detail Campaign:</b>\n";
		$caption .= "🎬 Judul: " . htmlspecialchars($campaign_title) . "\n";
		$caption .= "📊 Jenis: " . $type_label . "\n";
		$caption .= "💰 Reward: Rp " . number_format($price_per_task, 0, ',', '.') . "\n\n";
		
		if($proof_image_path) {
			$bot->sendPhoto($chat_id, $proof_image_path, $caption, 'HTML');
		} else {
			$caption .= "\n\n❌ Tidak ada bukti gambar yang ditemukan!";
			$bot->sendMessage($chat_id, $caption, null, 'HTML');
		}
		
		// Kirim pesan menu aksi baru dengan keyboard
		$reply = "👇 <b>Tindakan Admin</b>\n";
		$reply .= "Silakan review bukti screenshot di atas.\n";
		$reply .= "Apakah task ini disetujui atau ditolak?\n\n";
		$reply .= "Link target:\n" . htmlspecialchars($link_target);
		
		$keyboard = $bot->buildInlineKeyboard([
			[
				["text" => "✅ Terima", "callback_data" => "admin_approve_task_".$task_id],
				["text" => "❌ Tolak", "callback_data" => "admin_reject_task_".$task_id],
			],
			[
				["text" => "Kembali", "callback_data" => "/start"]
			]
		]);
		
		$bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
		
	} else {
		// Tidak ada task pending
		$reply = "📋 <b>Daftar Task Pending</b>\n\n";
		$reply .= "✨ Tidak ada task yang menunggu verifikasi saat ini.";
		
		$keyboard = $bot->buildInlineKeyboard([
			[
				["text" => "Kembali", "callback_data" => "/start"]
			]
		]);
		
		$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, "HTML", $keyboard);
	}
?>
