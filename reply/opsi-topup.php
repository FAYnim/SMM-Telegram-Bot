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

$payment_settings = db_read('smm_settings', ['category' => 'payment']);

$payment_data = [];
if(!empty($payment_settings)) {
	foreach($payment_settings as $setting) {
		$payment_data[$setting['setting_key']] = $setting['setting_value'];
	}
}

if($platform == "dana") {
	$dana_number = $payment_data['dana_number'] ?? 'Belum diatur';
	$dana_name = $payment_data['dana_name'] ?? 'Belum diatur';
	$reply .= "📞 <b>" . $dana_number . "</b>\n";
	$reply .= "A/N: <b>" . $dana_name . "</b>\n";
} elseif ($platform == "shopeepay") {
	$shopeepay_number = $payment_data['shopeepay_number'] ?? 'Belum diatur';
	$shopeepay_name = $payment_data['shopeepay_name'] ?? 'Belum diatur';
	$reply .= "📞 <b>" . $shopeepay_number . "</b>\n";
	$reply .= "A/N: <b>" . $shopeepay_name . "</b>\n";
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
