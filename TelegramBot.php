<?php
class TelegramBot {
    private $token;
    private $apiUrl;
    private $update;
    
    public function __construct($token) {
        $this->token = $token;
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}/";
        $this->update = json_decode(file_get_contents('php://input'), true);
    }
    
    /**
     * Mendapatkan update dari webhook
     */
    public function getUpdate() {
        return $this->update;
    }
    
    /**
     * Mendapatkan ID chat dari update terbaru
     */
    public function getChatId() {
        if (isset($this->update['message']['chat']['id'])) {
            return $this->update['message']['chat']['id'];
        } elseif (isset($this->update['callback_query']['message']['chat']['id'])) {
            return $this->update['callback_query']['message']['chat']['id'];
        }
        return null;
    }
    
    /**
     * Mendapatkan pesan yang diterima
     */
    public function getMessage() {
        return isset($this->update['message']['text']) ? $this->update['message']['text'] : null;
    }
    
    /**
     * Mendapatkan ID pesan yang diterima
     */
    public function getMessageId() {
        return isset($this->update['message']['message_id']) ? $this->update['message']['message_id'] : null;
    }
    
    /**
     * Mendapatkan username dari pengirim
     */
    public function getUsername() {
        if (isset($this->update['message']['from']['username'])) {
            return $this->update['message']['from']['username'];
        } elseif (isset($this->update['callback_query']['from']['username'])) {
            return $this->update['callback_query']['from']['username'];
        }
        return null;
    }
    
    /**
     * Mendapatkan first name dari pengirim
     */
    public function getFirstName() {
        if (isset($this->update['message']['from']['first_name'])) {
            return $this->update['message']['from']['first_name'];
        } elseif (isset($this->update['callback_query']['from']['first_name'])) {
            return $this->update['callback_query']['from']['first_name'];
        }
        return null;
    }
    
    /**
     * Mendapatkan last name dari pengirim
     */
    public function getLastName() {
        if (isset($this->update['message']['from']['last_name'])) {
            return $this->update['message']['from']['last_name'];
        } elseif (isset($this->update['callback_query']['from']['last_name'])) {
            return $this->update['callback_query']['from']['last_name'];
        }
        return null;
    }
    
    /**
     * Mengirim pesan
     */
    public function sendMessage($chatId, $text, $replyTo = null, $parseMode = 'HTML', $disableWebPreview = false) {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'disable_web_page_preview' => $disableWebPreview
        ];
        
        if ($replyTo) {
            $params['reply_to_message_id'] = $replyTo;
        }
        
        return $this->request('sendMessage', $params);
    }
    
    /**
     * Membalas pesan
     */
    public function replyMessage($text, $parseMode = 'HTML', $disableWebPreview = false) {
        $chatId = $this->getChatId();
        $messageId = $this->getMessageId();
        
        if ($chatId && $messageId) {
            return $this->sendMessage($chatId, $text, $messageId, $parseMode, $disableWebPreview);
        }
        
        return false;
    }
    
    /**
     * Mengirim aksi "typing" atau lainnya
     */
    public function sendChatAction($chatId, $action = 'typing') {
        $allowedActions = [
            'typing', 'upload_photo', 'record_video', 'upload_video',
            'record_voice', 'upload_voice', 'upload_document',
            'choose_sticker', 'find_location', 'record_video_note',
            'upload_video_note'
        ];
        
        if (in_array($action, $allowedActions)) {
            return $this->request('sendChatAction', [
                'chat_id' => $chatId,
                'action' => $action
            ]);
        }
        
        return false;
    }
    
    /**
     * Mengirim pesan dengan keyboard
     */
    public function sendMessageWithKeyboard($chatId, $text, $keyboard, $replyTo = null, $parseMode = 'HTML') {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => $parseMode
        ];
        
        if ($replyTo) {
            $params['reply_to_message_id'] = $replyTo;
        }
        
        return $this->request('sendMessage', $params);
    }
    
    /**
     * Mengedit pesan
     */
    public function editMessage($chatId, $messageId, $text, $parseMode = 'HTML', $keyboard = null) {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => $parseMode
        ];
        
        if ($keyboard) {
            $params['reply_markup'] = json_encode($keyboard);
        }
        
        return $this->request('editMessageText', $params);
    }
    
    /**
     * Menghapus pesan
     */
    public function deleteMessage($chatId, $messageId) {
        return $this->request('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId
        ]);
    }
    
    /**
     * Membuat inline keyboard
     */
    public function buildInlineKeyboard($buttons) {
        return ['inline_keyboard' => $buttons];
    }
    
    /**
     * Membuat reply keyboard
     */
    public function buildReplyKeyboard($buttons, $resize = true, $oneTime = false, $selective = false) {
        return [
            'keyboard' => $buttons,
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $oneTime,
            'selective' => $selective
        ];
    }
    
    /**
     * Menghapus reply keyboard
     */
    public function removeKeyboard($text, $selective = false) {
        return [
            'remove_keyboard' => true,
            'selective' => $selective
        ];
    }
    
    /**
     * Mengirim dokumen
     */
    public function sendDocument($chatId, $document, $caption = null, $replyTo = null, $parseMode = 'HTML') {
        $params = [
            'chat_id' => $chatId,
            'document' => $document,
            'parse_mode' => $parseMode
        ];
        
        if ($caption) {
            $params['caption'] = $caption;
        }
        
        if ($replyTo) {
            $params['reply_to_message_id'] = $replyTo;
        }
        
        return $this->request('sendDocument', $params);
    }
    
    /**
     * Eksekusi request ke API Telegram
     */
    private function request($method, $params = []) {
        $url = $this->apiUrl . $method;
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($result, true);
    }
    
    /**
     * Mengatur webhook
     */
    public function setWebhook($url, $maxConnections = 40, $allowedUpdates = []) {
        $params = [
            'url' => $url,
            'max_connections' => $maxConnections
        ];
        
        if (!empty($allowedUpdates)) {
            $params['allowed_updates'] = json_encode($allowedUpdates);
        }
        
        return $this->request('setWebhook', $params);
    }
    
    /**
     * Menghapus webhook
     */
    public function deleteWebhook() {
        return $this->request('deleteWebhook');
    }
    
    /**
     * Mendapatkan info webhook
     */
    public function getWebhookInfo() {
        return $this->request('getWebhookInfo');
    }
}

// Contoh penggunaan:
// $botToken = 'TOKEN_BOT_ANDA';
// $bot = new TelegramBot($botToken);

// $update = $bot->getUpdate();
// $chatId = $bot->getChatId();
// $message = $bot->getMessage();

// if ($message == '/start') {
//     $bot->sendMessage($chatId, 'Halo! Selamat datang di bot Telegram.');
// }
?>
