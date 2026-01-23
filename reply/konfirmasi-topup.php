<?php

$update_result = updateUserPosition($chat_id, 'confirm_topup', '');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

$reply = "🧾 <b>Konfirmasi Pembayaran</b>\n\n";
$reply .= "Silakan kirimkan foto atau screenshot <b>bukti transfer</b> Anda di sini.\n\n";
$reply .= "<i>Pastikan foto terlihat jelas agar proses verifikasi dapat berjalan lancar.</i>";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/topup']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
