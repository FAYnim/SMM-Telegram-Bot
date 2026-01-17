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

$update_result = updateUserPosition($chat_id, 'buat_campaign_akun');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Include the account selection screen
require_once 'reply/buat-campaign-akun.php';

?>