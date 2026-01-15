<?php
require_once 'helpers/error-handler.php';

// Validasi input reward
$reward = trim($message);
if (empty($reward)) {
    $error_reply = "❌ Total reward tidak boleh kosong!\n\nSilakan masukkan total reward atau batal untuk membatalkan pembuatan campaign:";
    sendErrorWithBackButton(
        $bot, 
        $chat_id, 
        $msg_id,
        $error_reply,
        "/cek_campaign"
    );
    return;
}

// Validasi numeric
if (!is_numeric($reward) || $reward <= 0) {
    $error_reply = "❌ Total reward harus berupa angka positif!\n\nSilakan masukkan total reward atau batal untuk membatalkan pembuatan campaign:";
    sendErrorWithBackButton(
        $bot, 
        $chat_id, 
        $msg_id,
        $error_reply,
        "/cek_campaign"
    );
    return;
}

// Get saldo user
/*
$wallet = db_read("smm_wallets", ["user_id" => $user_id], 'balance');
$balance = 0;
if(empty($wallet)) {
	// buat wallet
	$wallet_data = [
		"user_id" => $user_id,
	];
	db_create("smm_wallets", $wallet_data);
} else {
	$balance = $wallet[0]['balance'];
}

// Cek saldo user
if($balance < $reward) {
    $bot->sendMessage($chat_id, "❌ Saldo tidak cukup");
    return;
}
*/

// cek minimal reward
if($reward < 15000) {
    $error_reply = "❌ <b>Total Budget Terlalu Kecil</b>\n\n";
    $error_reply .= "Minimal pembuatan campaign adalah Rp 15.000\n\n";
    $error_reply .= "Silakan masukkan total reward yang lebih besar atau batal untuk membatalkan pembuatan campaign:";
    sendErrorWithBackButton(
        $bot, 
        $chat_id, 
        $msg_id,
        $error_reply,
        "/cek_campaign"
    );
    return;
}

// Update reward campaign di database
db_execute("UPDATE smm_campaigns SET campaign_balance = ? WHERE client_id = ? AND status = 'creating'", [$reward, $user_id]);

// Hapus pesan lama dengan msg_id
if ($msg_id) {
    $bot->deleteMessage($chat_id, $msg_id);
}

$update_result = updateUserPosition($chat_id, 'buat_campaign_target');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

$reply = "<b>📝 Buat Campaign - Target Total</b>\n\n";
$reply .= "Silakan masukkan jumlah target total untuk campaign ini:\n\n";
$reply .= "💡 <i>Contoh: 100</i>\n\n";
$reply .= "🎯 Ketik jumlah target total:";

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
