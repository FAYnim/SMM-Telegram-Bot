<?php

updateUserPosition($chat_id, 'help');

$reply = "ℹ️ <b>Bantuan & FAQ</b>\n\n"
    . "Selamat datang di halaman bantuan <b>SMM Bot Marketplace</b>!\n\n"
    . "Pilih topik yang ingin Anda pelajari:";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '📖 Tentang Bot', 'callback_data' => '/help_about']
    ],
    [
        ['text' => '📢 Cara Buat Campaign', 'callback_data' => '/help_campaign']
    ],
    [
        ['text' => '💼 Cara Kerjakan Tugas', 'callback_data' => '/help_task']
    ],
    [
        ['text' => '💰 Topup & Saldo', 'callback_data' => '/help_saldo']
    ],
    [
        ['text' => '💸 Withdraw & Transfer', 'callback_data' => '/help_withdraw']
    ],
    [
        ['text' => '👤 Akun Medsos', 'callback_data' => '/help_medsos']
    ],
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/start']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
