<?php
require_once 'TelegramBot.php';
require_once 'db.php';
require_once 'config.php';

// Inisialisasi bot
$bot = new TelegramBot($bot_token);

// Ambil data dari Telegram
$chat_id = $bot->getChatId();
$message = $bot->getMessage();
$username = $bot->getUsername();
$first_name = $bot->getFirstName();
$last_name = $bot->getLastName();

// Validasi input
if (!$chat_id || !$message) {
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
        'role' => 'worker', // default role
        'status' => 'active'
    ];
    $user_id = db_create('smm_users', $user_data);
} else {
    $user_id = $user[0]['id'];
}

// Handle command /start
if ($message == "/start") {
    $full_name = trim($first_name . ' ' . $last_name);
    $welcome_message = "Selamat datang " . $full_name . "! 👋";
    $bot->sendMessage($chat_id, $welcome_message);
}
?>
