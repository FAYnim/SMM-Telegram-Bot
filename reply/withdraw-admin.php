<?php
	$update_result = updateUserPosition($chat_id, 'withdraw_admin', '');
	
	if(!$update_result) {
		$bot->sendMessage($chat_id, '❌ Terjadi kesalahan sistem');
		return;
	}
	
	$sql = db_query("SELECT id, user_id, amount, destination_account, fee, created_at FROM smm_withdrawals WHERE status = 'pending' ORDER BY id DESC LIMIT 0,1");
		
	if(count($sql) > 0) {
		$withdraw_data = $sql[0];
		$withdraw_id = $withdraw_data["id"];
		$withdraw_user_id = $withdraw_data["user_id"];
		$amount = $withdraw_data["amount"];
		$destination_account = $withdraw_data["destination_account"];
		$fee = $withdraw_data["fee"];
		$created_at = $withdraw_data["created_at"];

		// Get user data
		$user = db_read("smm_users", ["id" => $withdraw_user_id]);
		
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
		
		// Get user wallet
		$wallet = db_read("smm_wallets", ["user_id" => $withdraw_user_id]);
		$current_balance = !empty($wallet) ? $wallet[0]["balance"] : 0;
		$current_profit = !empty($wallet) ? $wallet[0]["profit"] : 0;
		
		// Format waktu
		$withdraw_time = date('d M Y, H:i', strtotime($created_at));
		
		// Hapus pesan yang memicu callback
		$bot->deleteMessage($chat_id, $bot->getCallbackMessageId());
		
		// Kirim pesan detail withdraw ke admin
		$reply = "🔔 <b>WITHDRAW BARU</b>\n\n";
		$reply .= "👤 Nama: " . $full_name . "\n";
		$reply .= "🆔 User ID: <code>" . $user_chat_id . "</code>\n";
		if($user_username) {
			$reply .= "📝 Username: @" . $user_username . "\n";
		}
		$reply .= "🕐 Waktu: " . $withdraw_time . "\n";
		$reply .= "📋 Withdraw ID: <code>" . $withdraw_id . "</code>\n\n";
		$reply .= "<b>💰 Detail Withdraw:</b>\n";
		$reply .= "💳 Jumlah Withdraw: " . number_format($amount, 0, ',', '.') . "\n";
		if($fee > 0) {
			$reply .= "📦 Biaya Admin: " . number_format($fee, 0, ',', '.') . "\n";
			$net_amount = $amount - $fee;
			$reply .= "💵 Jumlah Diterima: <b>" . number_format($net_amount, 0, ',', '.') . "</b>\n";
		}
		$reply .= "🏦 Rekening Tujuan: " . htmlspecialchars($destination_account) . "\n\n";
		$reply .= "<b>💳 Info Saldo User:</b>\n";
		$reply .= "💵 Balance: " . number_format($current_balance, 0, ',', '.') . "\n";
		$reply .= "📊 Profit: " . number_format($current_profit, 0, ',', '.');
		
		$bot->sendMessage($chat_id, $reply, null, 'HTML');
		
		// Kirim pesan menu aksi baru dengan keyboard
		$reply = "👇 <b>Tindakan Admin</b>\n";
		$reply .= "Silakan review permintaan withdraw di atas.\n";
		$reply .= "Apakah withdraw ini disetujui atau ditolak?";
		
		$keyboard = $bot->buildInlineKeyboard([
			[
				["text" => "✅ Terima", "callback_data" => "admin_approve_withdraw_".$withdraw_id],
				["text" => "❌ Tolak", "callback_data" => "admin_reject_withdraw_".$withdraw_id],
			],
			[
				["text" => "Kembali", "callback_data" => "/start"]
			]
		]);
		
		$bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard, null, 'HTML');
		
	} else {
		// Tidak ada withdraw pending
		$reply = "📋 <b>Daftar Withdraw Pending</b>\n\n";
		$reply .= "✨ Tidak ada withdraw yang menunggu verifikasi saat ini.";
		
		$keyboard = $bot->buildInlineKeyboard([
			[
				["text" => "Kembali", "callback_data" => "/start"]
			]
		]);
		
		$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, "HTML", $keyboard);
	}
?>
