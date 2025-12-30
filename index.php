<?php
require_once 'TelegramBot.php';
require_once 'db.php';
require_once 'config/config.php';

// Fungsi handle posisi user
function updateUserPosition($chatid, $menu, $submenu = '') {
    $sql = "UPDATE smm_users SET menu = ?, submenu = ? WHERE chatid = ?";
    $params = [$menu, $submenu, $chatid];
    
    $result = db_execute($sql, $params);
    
    // Log position update result
    $position_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => 'update_position',
        'chatid' => $chatid,
        'menu' => $menu,
        'submenu' => $submenu,
        'result' => $result
    ];
    file_put_contents('log/position.log', json_encode($position_log));
    if($menu === "main") {
    	$result = 1;
    }
    return $result;
}

// Inisialisasi bot
$bot = new TelegramBot($bot_token);

// Ambil data dari Telegram
$chat_id = $bot->getChatId();
$message = $bot->getMessage();
$username = $bot->getUsername();
$first_name = $bot->getFirstName();
$last_name = $bot->getLastName();
$cb_data = $bot->getCallbackData();

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
file_put_contents('log/trace.log', json_encode($log_data));

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
$msg_id = $user[0]['msg_id'] ?? null;

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
	if($cb_data == "/start") {
		require_once 'reply/start.php';
	}
	if($cb_data == "/social") {
		require_once 'reply/social.php';
	}
	if($cb_data == "/tambah_medsos") {
		require_once 'reply/tambah_medsos.php';
	}
}

?>
