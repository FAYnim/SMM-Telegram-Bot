<?php
require_once __DIR__ . '/../helpers/error-handler.php';

// Handle delete account callback
if($cb_data && strpos($cb_data, '/delete_account_') === 0) {
	// Get accoint id
    $account_id = str_replace('/delete_account_', '', $cb_data);

    // Get account data
    $account = db_read('smm_social_accounts', [
        'id' => $account_id,
        'user_id' => $user_id
    ]);

    if (!empty($account)) {
    	// Konfirmasi
        $account_data = $account[0];
        $platform_icons = [
            'instagram' => '📷',
            'tiktok' => '🎵'
        ];
        $icon = $platform_icons[$account_data['platform']] ?? '🌐';

        $reply = "⚠️ <b>Konfirmasi Hapus Akun</b>\n\n" .
                $icon . " " . ucfirst($account_data['platform']) . ": @" . $account_data['username'] . "\n\n" .
                "Apakah Anda yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan.";

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '✅ Ya, Hapus', 'callback_data' => '/confirm_delete_' . $account_id],
                ['text' => '❌ Batal', 'callback_data' => '/edit_account_' . $account_id]
            ]
        ]);
        $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    } else {
        $error_message = "❌ Akun media sosial tidak ditemukan atau tidak valid.";
        sendErrorWithBackButton($bot, $chat_id, $msg_id, $error_message, '/edit_medsos', '🔙 Kembali ke Daftar Akun');
    }
}

// Handle confirm delete callback
if($cb_data && strpos($cb_data, '/confirm_delete_') === 0) {
    // get account id
    $account_id = str_replace('/confirm_delete_', '', $cb_data);

    // Get account data
    $account = db_read('smm_social_accounts', [
        'id' => $account_id,
        'user_id' => $user_id
    ]);

    if (!empty($account)) {
        $account_data = $account[0];
        $platform_icons = [
            'instagram' => '📷',
            'tiktok' => '🎵'
        ];
        $icon = $platform_icons[$account_data['platform']] ?? '🌐';

        $delete_result = db_update('smm_social_accounts', ['status' => 'disabled'], ['id' => $account_id, 'user_id' => $user_id]);

        if ($delete_result) {
            $reply = "✅ <b>Akun Berhasil Dihapus!</b>\n\n" .
                    $icon . " " . ucfirst($account_data['platform']) . ": @" . $account_data['username'] . "\n\n" .
                    "Akun media sosial telah dihapus.";

            $keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '🔙 Kembali ke Menu Sosial', 'callback_data' => '/social']
                ]
            ]);

            $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
        } else {
            $error_message = "❌ Gagal menghapus akun. Silakan coba lagi.";
            $error_buttons = [
                [
                    ['text' => '🔙 Kembali', 'callback_data' => '/edit_account_' . $account_id]
                ]
            ];
            editErrorWithCustomButtons($bot, $chat_id, $msg_id, $error_message, $error_buttons);
        }
    } else {
        $error_message = "❌ Akun media sosial tidak ditemukan atau tidak valid.";
        sendErrorWithBackButton($bot, $chat_id, $msg_id, $error_message, '/edit_medsos', '🔙 Kembali ke Daftar Akun');
    }
}

?>
