<?php
	$update_result = updateUserPosition($chat_id, 'deposit_admin', '');
	
	if(!$update_result) {
		$bot->sendMessage($chat_id, '❌ Terjadi kesalahan sistem');
	}
	
	$sql = db_query("SELECT  id, user_id FROM smm_deposits WHERE status = 'pending' ORDER BY id DESC LIMIT 0,1");
		
	if(count($sql) > 0) {
		$deposit_data = $sql[0];
		$deposit_id = $deposit_data["id"];
		$deposit_user_id = $deposit_data["user_id"];

		// Get user data
		$sql = db_read("smm_users", ["id" => $deposit_user_id]);
		
		if(empty($sql)) {
			$reply = "User not found";
			$keyboard = $bot->buildInlineKeyboard([
				[
					["text" => "Kembali", "callback_data" => "/start"]
				]
			]);
			$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply);
			return;
		}
		
		$user_data = $sql[0];
		$user_username = $user_data["username"];
		$user_chat_id = $user_data["chatid"];
		
		$reply = "<b>Topup</b>\n\n";
		$reply .= "User: ".$user_username."\n";
		$reply .= "User ID: ".$user_chat_id."\n\n";
		
		$keyboard = $bot->buildInlineKeyboard([
			[
				["text" => "Terima", "callback_data" => "admin_approve_topup_".$deposit_id],
				["text" => "Tolak", "callback_data" => "admin_reject_topup_".$deposit_id],
			],
			[
				["text" => "Kembali", "callback_data" => "/start"]
			]
		]);
	}
	
	$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, "HTML", $keyboard);
?>