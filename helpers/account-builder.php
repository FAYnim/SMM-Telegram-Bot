<?php

// Load username validation helper for getPlatformIcon function
require_once __DIR__ . '/username-validator.php';

/**
 * Build edit account keyboard with all user's social media accounts
 * 
 * @param int $user_id User ID
 * @param string $back_callback Callback data for back button
 * @return array Array containing reply text and keyboard
 */
function buildEditAccountKeyboard($user_id, $back_callback = '/social') {
    // Get user's social accounts
    $social_accounts = db_query("SELECT id, platform, username, account_url, status "
        ."FROM smm_social_accounts "
        ."WHERE user_id = ? AND status = 'active' "
        ."ORDER BY platform, created_at", [$user_id]);
    
    $list_reply = "Media sosial mana yang mau di-edit?";
    $keyboard_buttons = [];
    
    if (count($social_accounts) > 0) {
        foreach ($social_accounts as $account) {
            $icon = getPlatformIcon($account['platform']);
            $display_text = $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'];
            $callback_data = '/edit_account_' . $account['id'];
            
            $keyboard_buttons[] = [$display_text, $callback_data];
        }
        
        // Add back button
        $keyboard_buttons[] = ['🔙 Kembali', $back_callback];
        
        // Build keyboard with all accounts
        $list_keyboard = [];
        foreach ($keyboard_buttons as $button) {
            $list_keyboard[] = [
                ['text' => $button[0], 'callback_data' => $button[1]]
            ];
        }
    } else {
        $list_reply .= "\n\n📝 Belum ada akun media sosial yang ditambahkan";
        
        $list_keyboard = [
            [
                ['text' => '🔙 Kembali', 'callback_data' => $back_callback]
            ]
        ];
    }
    
    return [
        'reply' => $list_reply,
        'keyboard' => $list_keyboard
    ];
}

/**
 * Show edit account list to user
 * 
 * @param object $bot Telegram bot instance
 * @param int $chat_id Chat ID
 * @param int $msg_id Message ID to edit
 * @param int $user_id User ID
 * @param string $back_callback Callback data for back button
 * @return void
 */
function showEditAccountList($bot, $chat_id, $msg_id, $user_id, $back_callback = '/social') {
    $account_data = buildEditAccountKeyboard($user_id, $back_callback);
    
    $bot->editMessage($chat_id, $msg_id, $account_data['reply'], 'HTML', $account_data['keyboard']);
}

?>