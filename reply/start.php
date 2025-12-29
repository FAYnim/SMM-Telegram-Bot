<?php

    // Update posisi user ke main
    $update_result = updateUserPosition($chat_id, 'main');
    
    if (!$update_result) {
        $bot->sendMessage($chat_id, "❌ Something Error!");
        return;
    }
    
    $full_name = trim($first_name . ' ' . $last_name);
    $reply = "Selamat datang " . $full_name . "! 👋\n\n";

    if ($role == 'user') {
        $reply .= "👤 Selamat datang di SMM Bot!\n\n"
            . "Platform Paid-to-Click untuk meningkatkan engagement media sosial.\n\n"
            . "Pilih menu di bawah:";
            
        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '📝 Buat Campaign', 'callback_data' => '/buat_campaign'],
            ],
            [
                ['text' => '📋 Campaign Tersedia', 'callback_data' => '/tugas']
            ],
            [
                ['text' => '💰 Topup', 'callback_data' => '/topup'],
                ['text' => '💸 Withdraw', 'callback_data' => '/withdraw']
            ],
            [
                ['text' => '👤 Media Social', 'callback_data' => '/social'],
            ]
        ]);
    } elseif ($role == 'admin') {
        $reply .= "⚙️ Anda adalah Admin\n\n"
            . "Pilih menu di bawah:";
            
        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '📋 Verifikasi', 'callback_data' => '/verifikasi'],
                ['text' => '💰 Deposit', 'callback_data' => '/deposit']
            ],
            [
                ['text' => '💸 Withdraw', 'callback_data' => '/withdraw_admin'],
                ['text' => '👥 Manage User', 'callback_data' => '/manage_user']
            ],
            [
                ['text' => '📊 Laporan', 'callback_data' => '/laporan'],
                ['text' => '❓ Help', 'callback_data' => '/help']
            ]
        ]);
    }
    
    $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);

?>
