<?php

    $full_name = trim($first_name . ' ' . $last_name);
    $welcome_message = "Selamat datang " . $full_name . "! 👋\n\n";

    if ($role == 'unknown') {
        $welcome_message .= "🤖 Selamat datang di SMM Bot!\n\n"
            . "Platform Paid-to-Click untuk meningkatkan engagement media sosial.\n\n"
            . "Silakan pilih peran Anda:";
            
        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '👤 Jadi Client', 'callback_data' => '/jadi_client'],
                ['text' => '👷 Jadi Worker', 'callback_data' => '/jadi_worker']
            ]
        ]);
    } elseif ($role == 'client') {
        $welcome_message .= "🎯 Anda adalah Client\n\n"
            . "Pilih menu di bawah:";
            
        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '📝 Buat Campaign', 'callback_data' => '/buat_campaign'],
                ['text' => '📊 Lihat Campaign', 'callback_data' => '/lihat_campaign']
            ],
            [
                ['text' => '💰 Topup', 'callback_data' => '/topup'],
                ['text' => '📈 Statistik', 'callback_data' => '/statistik']
            ],
            [
                ['text' => '❓ Help', 'callback_data' => '/help']
            ]
        ]);
    } elseif ($role == 'worker') {
        $welcome_message .= "👷 Anda adalah Worker\n\n"
            . "Pilih menu di bawah:";
            
        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '📋 Tugas Tersedia', 'callback_data' => '/tugas'],
                ['text' => '✅ Tugas Saya', 'callback_data' => '/tugas_saya']
            ],
            [
                ['text' => '💸 Withdraw', 'callback_data' => '/withdraw'],
                ['text' => '👤 Profil', 'callback_data' => '/profil']
            ],
            [
                ['text' => '❓ Help', 'callback_data' => '/help']
            ]
        ]);
    } elseif ($role == 'admin') {
        $welcome_message .= "⚙️ Anda adalah Admin\n\n"
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
    
    $bot->sendMessageWithKeyboard($chat_id, $welcome_message, $keyboard);

?>
