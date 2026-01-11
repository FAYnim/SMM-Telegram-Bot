<?php
	$update_result = updateUserPosition($chat_id, 'deposit_admin', '');
	
	if(!$update_result) {
		$bot->sendMessage($chat_id, '❌ Terjadi kesalahan sistem');
		return;
	}
	
	$sql = db_query("SELECT id, user_id, proof_image_id, created_at FROM smm_deposits WHERE status = 'pending' ORDER BY id DESC LIMIT 0,1");
		
	if(count($sql) > 0) {
		$deposit_data = $sql[0];
		$deposit_id = $deposit_data["id"];
		$deposit_user_id = $deposit_data["user_id"];
		$proof_image_id = $deposit_data["proof_image_id"];
		$created_at = $deposit_data["created_at"];

		// Get user data
		$user = db_read("smm_users", ["id" => $deposit_user_id]);
		
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
		$deposit_time = date('d M Y, H:i', strtotime($created_at));
		
		// Hapus pesan yang memicu callback
		$bot->deleteMessage($chat_id, $bot->getCallbackMessageId());
		
		// Kirim foto bukti ke admin
		$caption = "🔔 <b>TOPUP BARU</b>\n\n";
		$caption .= "👤 Nama: " . $full_name . "\n";
		$caption .= "🆔 User ID: <code>" . $user_chat_id . "</code>\n";
		if($user_username) {
			$caption .= "📝 Username: @" . $user_username . "\n";
		}
		$caption .= "🕐 Waktu: " . $deposit_time . "\n";
		$caption .= "📋 Deposit ID: <code>" . $deposit_id . "</code>";
		
		$bot->sendPhoto($chat_id, $proof_image_id, $caption, 'HTML');
		
		// Kirim pesan menu aksi baru dengan keyboard
		$reply = "👇 <b>Tindakan Admin</b>\n";
		$reply .= "Silakan cek bukti transfer di atas.\n";
		$reply .= "Apakah topup ini disetujui atau ditolak?";
		
		$keyboard = $bot->buildInlineKeyboard([
			[
				["text" => "✅ Terima", "callback_data" => "admin_approve_topup_".$deposit_id],
				["text" => "❌ Tolak", "callback_data" => "admin_reject_topup_".$deposit_id],
			],
			[
				["text" => "Kembali", "callback_data" => "/start"]
			]
		]);
		
		$bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
		
	} else {
		// Tidak ada deposit pending
		$reply = "📋 <b>Daftar Topup Pending</b>\n\n";
		$reply .= "✨ Tidak ada topup yang menunggu verifikasi saat ini.";
		
		$keyboard = $bot->buildInlineKeyboard([
			[
				["text" => "Kembali", "callback_data" => "/start"]
			]
		]);
		
		$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, "HTML", $keyboard);
	}
?>