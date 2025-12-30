<?php

// Load account builder helper
require_once __DIR__ . '/account-builder.php';

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
function handleErrorAndRebuildList($bot, $chat_id, $msg_id, $error_message, $user_id, $back_callback = '/social') {
    $bot->deleteMessage($chat_id, $msg_id);
    $send_result = $bot->sendMessage($chat_id, $error_message);

    // Save msg_id
    if ($send_result && isset($send_result['result']['message_id'])) {
        $new_msg_id = $send_result['result']['message_id'];
        db_update('smm_users', ['msg_id' => $new_msg_id], ['chatid' => $chat_id]);

        sleep(3);

        // Use account builder helper
        showEditAccountList($bot, $chat_id, $new_msg_id, $user_id, $back_callback);
    }
}

?>
