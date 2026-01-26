<?php

$update_result = updateUserPosition($chat_id, 'create_custom_code');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
    return;
}

$reply = "<b>➕ Buat Kode Referral Custom</b>\n\n";
$reply .= "Silakan kirim kode referral custom yang ingin Anda buat.\n\n";
$reply .= "📝 <b>Ketentuan:</b>\n";
$reply .= "• Minimal 3 karakter, maksimal 20 karakter\n";
$reply .= "• Hanya huruf (A-Z) dan angka (0-9)\n";
$reply .= "• Dilarang menggunakan karakter I L O S Z\n";
$reply .= "• Tidak boleh menggunakan kode yang sudah ada\n";
$reply .= "• Akan diubah ke huruf kapital otomatis\n\n";
$reply .= "<i>💡 Contoh: <code>2026</code>, <code>Reward</code></i>";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/referral']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
