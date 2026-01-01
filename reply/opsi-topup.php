<?php

$platform = str_replace('/topup_', '', $cb_data);

$update_result = updateUserPosition($chat_id, "opsi_topup", $platform);

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

if($platform == "shopeepay") $display_platform = "ShopeePay"; // Fix capitalization
if($platform == "dana") $display_platform = "DANA"; // Fix capitalization

$reply = "💎 <b>Topup via " . $display_platform . "</b>\n\n";
$reply .= "Silakan transfer dana deposit Anda ke nomor berikut:\n\n";

if($platform == "dana") {
	$reply .= "📞 <b>0812-3456-7890</b>\n";
	$reply .= "A/N: <b>Admin SMM</b>\n";
} elseif ($platform == "shopeepay") {
	$reply .= "📞 <b>0812-3456-7890</b>\n";
	$reply .= "A/N: <b>Admin SMM</b>\n";
}

$reply .= "\n⚠️ <b>Penting:</b>\n";
$reply .= "Setelah berhasil melakukan transfer, mohon tekan tombol <b>✅ Konfirmasi</b> di bawah untuk mengunggah bukti pembayaran.";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '✅ Konfirmasi', 'callback_data' => '/konfirmasi_topup'],
        ['text' => '🔙 Kembali', 'callback_data' => '/topup']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
