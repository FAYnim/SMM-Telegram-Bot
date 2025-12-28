<?php

require_once 'TelegramBot.php';
require_once 'db.php';
require_once 'config.php'; // $api_key

// --- Inisialisasi Bot ---
$bot = new TelegramBot($api_key);

// --- Ambil data dari Telegram ---
$chatid = $bot->getChatId();
$msg = $bot->getMessage();
$msgid = $bot->getMessageId();

// Jika tidak ada chat id atau message, hentikan script
if (!$chatid || !$msg) {
    exit();
}

// --- Manajemen Pengguna ---
function getUserData($chatid) {
    $sql = "SELECT * FROM trs_user WHERE chatid = '$chatid'";
    $userData = db_bind($sql);

    if ($userData === "empty") {
        $sql_insert = "INSERT INTO trs_user (chatid, status, menu, submenu) VALUES ('$chatid', 1, 'main', '')";
        db_query($sql_insert);
        // Mengembalikan data pengguna baru
        return [
            'status' => 1,
            'menu' => 'main',
            'submenu' => ''
        ];
    }
    return $userData;
}

$user = getUserData($chatid);
$db_menu = $user['menu'];
$db_submenu = $user['submenu'];

// --- Cek Akses Pengguna ---
if ($user['status'] != 1) {
    $bot->sendMessage($chatid, "Anda tidak memiliki akses terhadap bot ini!");
    exit();
}

// --- Fungsi Bantuan ---
function resetMenu($chatid) {
    $sql = "UPDATE trs_user SET menu = 'main', submenu = '' WHERE chatid = '$chatid'";
    db_query($sql);
}

// --- Routing Perintah Utama ---
$welcomeMessage = "Halo selamat datang!\n\nSilakan pilih menu di bawah ini:";

if ($msg == '/start' || $msg == '/batal') {
    resetMenu($chatid);
/*    $keyboard = $bot->buildInlineKeyboard([
        [['text' => 'List Order Aktif', 'callback_data' => '/list']],
        [['text' => 'Set Saldo Awal', 'callback_data' => '/setsaldo']],
        [['text' => 'Bantuan', 'callback_data' => '/help']]
    ]);
    $bot->sendMessageWithKeyboard($chatid, $welcomeMessage, $keyboard);*/
    $reply = "Silahkan pilih menu dibawah:\n\n"
    	."/list - lihat order aktif\n"
    	."/setsaldo - atur saldo awal\n"
    	."/help - bantuan\n"
    ;
    $bot->sendMessage($chatid, $reply);
} else if ($msg == '/iya') {
    if ($user['menu'] === 'pair') {
        include 'menu/pair-confirm.php';
    }
    resetMenu($chatid);
} else if ($msg == '/help') {
    include 'menu/help.php';
} else if ($msg == '/list') {
    include 'menu/order.php';
} else if ($msg == '/setsaldo') {
    include 'menu/setsaldo.php';
} else {
    if ($user['menu'] == 'setsaldo') {
        include 'menu/setsaldo-inp.php';
    } else {
        include 'menu/custom-command.php';
    }
}

?>
