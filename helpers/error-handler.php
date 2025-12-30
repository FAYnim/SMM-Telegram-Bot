<?php

/**
 * Handle error message and rebuild account list
 *
 * @param object $bot Telegram bot instance
 * @param int $chat_id Chat ID
 * @param int $msg_id Message ID to delete
 * @param string $error_message Error message to display
 * @param int $user_id User ID for rebuilding account list
 * @param string $list_title Title for the account list
 * @return void
 */
function handleErrorAndRebuildList($bot, $chat_id, $msg_id, $error_message, $user_id, $list_title = "Media sosial mana yang mau di-edit?") {
    $bot->deleteMessage($chat_id, $msg_id);
    $send_result = $bot->sendMessage($chat_id, $error_message);

    // Save msg_id and show account list after 3 seconds
    if ($send_result && isset($send_result['result']['message_id'])) {
        $new_msg_id = $send_result['result']['message_id'];
        db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

        sleep(3);

        // Rebuild account list
        $social_accounts = db_query("SELECT id, platform, username, account_url, status "
            ."FROM smm_social_accounts "
            ."WHERE user_id = ? AND status = 'active' "
            ."ORDER BY platform, created_at", [$user_id]);

        $list_reply = $list_title;

        if (count($social_accounts) > 0) {
            $keyboard_buttons = [];

            foreach ($social_accounts as $account) {
                $icon = getPlatformIcon($account['platform']);
                $display_text = $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'];
                $callback_data = '/edit_account_' . $account['id'];

                $keyboard_buttons[] = [$display_text, $callback_data];
            }

            // Add back button
            $keyboard_buttons[] = ['🔙 Kembali', '/social'];

            // Build keyboard with all accounts
            $list_keyboard = [];
            foreach ($keyboard_buttons as $button) {
                $list_keyboard[] = [
                    ['text' => $button[0], 'callback_data' => $button[1]]
                ];
            }
        } else {
            $list_reply .= "\n\n📝 Belum ada akun media sosial yang ditambahkan";

            $list_keyboard = $bot->buildInlineKeyboard([
                [
                    ['text' => '🔙 Kembali', 'callback_data' => '/social']
                ]
            ]);
        }
        $bot->editMessage($chat_id, $new_msg_id, $list_reply, 'HTML', $list_keyboard);
    }
}

?>
