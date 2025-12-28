<?php
require_once 'TelegramBot.php';
require_once 'db.php';
require_once 'config.php';

// Inisialisasi bot
$bot = new TelegramBot($bot_token);

// Ambil data dari Telegram
$chatId = $bot->getChatId();
$message = $bot->getMessage();
$username = $bot->getUsername();
$firstName = $bot->getFirstName();
$lastName = $bot->getLastName();

// Validasi input
if (!$chatId || !$message) {
    exit();
}

// Handle command /start
if ($message == "/start") {
    $fullName = trim($firstName . ' ' . $lastName);
    $welcomeMessage = "Selamat datang " . $fullName . "! 👋";
    $bot->sendMessage($chatId, $welcomeMessage);
}
?>
