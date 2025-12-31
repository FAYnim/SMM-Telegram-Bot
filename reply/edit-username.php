<?php

// Load helpers
require_once __DIR__ . '/../helpers/username-validator.php';
require_once __DIR__ . '/../helpers/error-handler.php';

// Handle edit username callback
if($cb_data && strpos($cb_data, '/edit_username_') === 0) {
    // Extract account ID from callback data
    $account_id = str_replace('/edit_username_', '', $cb_data);

    // Get account details from database
    $account = db_read('smm_social_accounts', [
        'id' => $account_id,
        'user_id' => $user_id
    ]);

    if (!empty($account)) {
        $account_data = $account[0];
        $platform_name = ucfirst($account_data['platform']);

        // Update user position to edit-username
        $update_result = updateUserPosition($chat_id, 'edit_username', $account_id);

        if (!$update_result) {
            $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
            return;
        }

        $reply = "✏️ <b>Ubah Username {$platform_name}</b>\n\n";
        $reply .= "Silakan kirimkan username baru untuk akun ini (tanpa @).\n";
        $reply .= "Contoh: <code>jokowi</code>";

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '🔙 Kembali', 'callback_data' => '/edit_account_' . $account_id]
            ]
        ]);

        $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    } else {
        // Account not found - error handling
        handleErrorAndRebuildList($bot, $chat_id, $msg_id, "❌ Akun media sosial tidak ditemukan atau tidak valid.", $user_id);
    }
}

// Handle username input for edit
if(!$cb_data && $user[0]['menu'] == 'edit_username') {
    $username_input = trim($message);
    $account_id = $user[0]['submenu'];

    if (empty($username_input)) {
        $bot->sendMessage($chat_id, "❌ Username tidak boleh kosong!");
        return;
    }

    // Remove @ if user enters @username
    if (strpos($username_input, '@') === 0) {
        $username_input = substr($username_input, 1);
    }

    // check account data
    $current_account = db_read('smm_social_accounts', [
        'id' => $account_id,
        'user_id' => $user_id
    ]);

    if (empty($current_account)) {
        handleErrorAndRebuildList($bot, $chat_id, $msg_id, "❌ Akun media sosial tidak ditemukan atau tidak valid.", $user_id);
        return;
    }

    $current_data = $current_account[0];
    $platform = $current_data['platform'];

    // Check if new username already exists for this platform (excluding current account)
    $existing_username = db_query("SELECT id FROM smm_social_accounts "
        ."WHERE platform = ? AND username = ? AND id != ? "
        ."LIMIT 1", [$platform, $username_input, $account_id]);
    
    if (count($existing_username) > 0) {
        handleErrorAndRebuildList($bot, $chat_id, $msg_id, "❌ Username @{$username_input} sudah digunakan oleh pengguna lain. Silakan gunakan username lain.", $user_id);
        return;
    }

    // Update account URL based on platform
    $account_url = generatePlatformUrl($platform, $username_input);

    // Update username in database
    $update_data = [
        'username' => $username_input,
        'account_url' => $account_url,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $update_result = db_update('smm_social_accounts', $update_data, [
        'id' => $account_id,
        'user_id' => $user_id
    ]);

    if ($update_result) {
        $icon = getPlatformIcon($platform);

        $reply = "✅ <b>Username Berhasil Diperbarui!</b>\n\n" .
                $icon . " <b>" . ucfirst($platform) . "</b>\n" .
                "👤 Username baru: <code>@" . $username_input . "</code>\n\n" .
                "Data akun Anda telah berhasil disimpan.";

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '🔙 Kembali ke Menu Sosial', 'callback_data' => '/social']
            ]
        ]);

        $bot->deleteMessage($chat_id, $msg_id);
        $send_result = $bot->sendMessage($chat_id, $reply);

        // Save new msg_id
        if ($send_result && isset($send_result['result']['message_id'])) {
            $new_msg_id = $send_result['result']['message_id'];
            db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

            sleep(3);

            // Show updated social menu
            $social_reply = "<b>📱 Kelola Akun Media Sosial</b>\n\n";
            $social_reply .= "Berikut adalah daftar akun yang telah Anda hubungkan:\n\n";
            $social_accounts = db_query("SELECT platform, username, account_url, status "
                ."FROM smm_social_accounts "
                ."WHERE user_id = ? AND status = 'active' "
                ."ORDER BY platform, created_at", [$user_id]);

            if (count($social_accounts) > 0) {
                $current_platform = '';
                foreach ($social_accounts as $account) {
                    if ($current_platform !== $account['platform']) {
                        if ($current_platform !== '') {
                            $social_reply .= "\n";
                        }
                        $social_reply .= ucfirst($account['platform']) . "\n\n";
                        $current_platform = $account['platform'];
                    }
                    $social_reply .= "- " . $account['account_url'] . "\n";
                }
                $social_reply .= "\n";
            } else {
                $social_reply .= "⚠️ <i>Belum ada akun terhubung.</i>\n";
                $social_reply .= "Hubungkan akun media sosial Anda untuk mulai mengambil tugas.\n\n";
            }

            $social_reply .= "👇 Gunakan menu di bawah ini:";

            $social_keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
                ],
                [
                    ['text' => '🎛️ Edit/Hapus Medsos', 'callback_data' => '/edit_medsos'],
                ],
                [
                    ['text' => '🔙 Kembali', 'callback_data' => '/start']
                ]
            ]);

            $bot->editMessage($chat_id, $new_msg_id, $social_reply, 'HTML', $social_keyboard);
        }
    } else {
        handleErrorAndRebuildList($bot, $chat_id, $msg_id, "❌ Gagal memperbarui username. Silakan coba lagi.", $user_id);
    }
}

?>
