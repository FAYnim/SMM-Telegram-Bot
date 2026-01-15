<?php
/**
 * Error Handler Helper
 * Fungsi-fungsi untuk menangani error dengan pattern yang konsisten
 */

/**
 * Kirim pesan error dengan tombol kembali/batal
 * 
 * Pattern:
 * 1. Hapus pesan lama (jika ada)
 * 2. Kirim pesan error dengan keyboard
 * 3. Update msg_id baru ke database
 * 
 * @param object $bot Instance TelegramBot
 * @param int $chat_id Chat ID user
 * @param int|null $msg_id Message ID untuk dihapus (optional)
 * @param string $error_message Pesan error yang ditampilkan
 * @param string $callback_data Callback data untuk tombol (default: '/cek_campaign')
 * @param string $button_text Text tombol
 * @param string $parse_mode Parse mode untuk message (default: 'HTML')
 * @return bool True jika berhasil, false jika gagal
 */
function sendErrorWithBackButton($bot, $chat_id, $msg_id, $error_message, $callback_data = '/start', $button_text = '🔙 Kembali', $parse_mode = 'HTML') {
    // Hapus pesan lama jika ada
    if ($msg_id) {
        $bot->deleteMessage($chat_id, $msg_id);
    }
    
    // Buat keyboard dengan tombol kembali
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => $button_text, 'callback_data' => $callback_data]
        ]
    ]);
    
    // Kirim pesan error dengan keyboard
    $result = $bot->sendMessageWithKeyboard($chat_id, $error_message, $keyboard, null, $parse_mode);
    $new_msg_id = $result['result']['message_id'] ?? null;
    
    // Update msg_id baru ke database
    if ($new_msg_id) {
        db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
        return true;
    }
    
    return false;
}

/**
 * Edit pesan error dengan keyboard custom (support multiple buttons)
 * 
 * Pattern:
 * 1. Edit pesan yang ada dengan error message + keyboard
 * 2. Tidak perlu update msg_id karena pesan yang sama
 * 
 * @param object $bot Instance TelegramBot
 * @param int $chat_id Chat ID user
 * @param int $msg_id Message ID untuk diedit
 * @param string $error_message Pesan error yang ditampilkan
 * @param array $buttons Array of buttons [[text, callback_data], ...]
 * @param string $parse_mode Parse mode untuk message (default: 'HTML')
 * @return bool True jika berhasil, false jika gagal
 */
function editErrorWithCustomButtons($bot, $chat_id, $msg_id, $error_message, $buttons, $parse_mode = 'HTML') {
    // Buat keyboard dari array buttons
    $keyboard = $bot->buildInlineKeyboard($buttons);
    
    // Edit pesan dengan keyboard
    $result = $bot->editMessage($chat_id, $msg_id, $error_message, $parse_mode, $keyboard);
    
    return $result ? true : false;
}

/**
 * Kirim pesan error sederhana tanpa keyboard
 * 
 * @param object $bot Instance TelegramBot
 * @param int $chat_id Chat ID user
 * @param string $error_message Pesan error
 * @param string $parse_mode Parse mode (default: 'HTML')
 * @return void
 */
function sendSimpleError($bot, $chat_id, $error_message, $parse_mode = 'HTML') {
    $bot->sendMessage($chat_id, $error_message, $parse_mode);
}

?>
