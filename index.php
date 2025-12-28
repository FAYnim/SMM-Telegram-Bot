<?php
require_once 'TelegramBot.php';
require_once 'db.php';
require_once 'config.php';

// Inisialisasi bot
$bot = new TelegramBot($bot_token);

// Ambil data dari Telegram
$chatId = $bot->getChatId();
$message = $bot->getMessage();

// Validasi input
if (!$chatId || !$message) {
    exit();
}

// Handle command /start
if ($message == "/start") {
    $bot->sendMessage($chatId, 'Connection ON');
}
$bot->sendMessage($chatId, "Hello World!");
?>
