<?php
require_once 'TelegramBot.php';
require_once 'db.php';
require_once 'config/config.php';

// Inisialisasi bot
$bot = new TelegramBot($bot_token);

// Ambil data dari Telegram
$chat_id = $bot->getChatId();
$message = $bot->getMessage();
$username = $bot->getUsername();
$first_name = $bot->getFirstName();
$last_name = $bot->getLastName();
$cb_data = $bot->getCallbackData();
$msg_id = ($bot->getCallbackQueryId()) ? $bot->getCallbackQueryId() : $bot->getMessageId();

// Trace log
$log_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'chat_id' => $chat_id,
    'msg_id' => $msg_id,
    'message' => $message,
    'username' => $username,
    'cb_data' => $bot->getCallbackData(),
    'update' => $bot->getUpdate()
];
file_put_contents('trace.log', json_encode($log_data));

// Validasi input
if (!$chat_id || (!$message && !$bot->getCallbackData())) {
    exit();
}

// Check atau insert user ke database
$user = db_read('smm_users', ['chatid' => $chat_id]);

if (empty($user)) {
    // Insert user baru
    $full_name = trim($first_name . ' ' . $last_name);
    $user_data = [
        'chatid' => $chat_id,
        'username' => $username,
        'full_name' => $full_name,
        'role' => 'user', // default role
        'status' => 'active'
    ];
    $user_id = db_create('smm_users', $user_data);
}

$user = db_read('smm_users', ['chatid' => $chat_id]);
$user_id = $user[0]['id'];
$role = $user[0]['role'];

//	FOR DEBUGGING ONLY:
//	$reply .= "<pre>".json_encode($user)."</pre>";

// Include reply handlers
if(!$cb_data){
	if ($message == "/start") {
		require_once 'reply/start.php';
	}
	if ($message == "/social") {
		require_once 'reply/social.php';
	}
} else {
	if($cb_data == "/social") {
		require_once 'reply/social.php';
	}
}

?>
