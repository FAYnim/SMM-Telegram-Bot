<?php
require_once 'helpers/error-handler.php';

// Validasi input judul
$judul = trim($message);
if (empty($judul)) {
    $error_reply = "❌ Judul campaign tidak boleh kosong!\n\nSilakan masukkan judul campaign atau batal untuk membatalkan pembuatan campaign:";
    sendErrorWithBackButton(
        $bot, 
        $chat_id, 
        $msg_id,
        $error_reply,
        "/cek_campaign"
    );
    return;
}

// Update judul campaign di database
db_execute("UPDATE smm_campaigns SET campaign_title = ? WHERE client_id = ? AND status = 'creating'", [$judul, $user_id]);

// Hapus pesan lama dengan msg_id
if ($msg_id) {
    $bot->deleteMessage($chat_id, $msg_id);
}

$update_result = updateUserPosition($chat_id, 'buat_campaign_link');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

$reply = "<b>📝 Buat Campaign - Link Target</b>\n\n";
$reply .= "Silakan masukkan link target untuk campaign:\n\n";
$reply .= "💡 <i>Contoh: https://instagram.com/p/ABC123</i>\n\n";
$reply .= "🔗 Ketik link target Anda:";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Batal', 'callback_data' => '/cek_campaign']
    ]
]);

// Kirim pesan baru dengan keyboard dan dapatkan msg_id baru
$result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);
$new_msg_id = $result['result']['message_id'] ?? null;

// Update msg_id baru di database
if ($new_msg_id) {
    db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
}

?>