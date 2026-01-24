<?php
$payment_method = '';

if($cb_data == "settings_edit_dana") {
	$payment_method = 'dana';
	$update_result = updateUserPosition($chat_id, 'settings_payment', 'edit_dana');
} elseif($cb_data == "settings_edit_shopeepay") {
	$payment_method = 'shopeepay';
	$update_result = updateUserPosition($chat_id, 'settings_payment', 'edit_shopeepay');
} else {
	$bot->sendMessage($chat_id, '❌ Perintah tidak dikenali');
	return;
}

if(!$update_result) {
	$bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
	return;
}

if($payment_method == 'dana') {
	$method_label = 'DANA';
	$reply = "📱 <b>Ubah Pengaturan DANA</b>\n\n";
	$reply .= "Silakan masukkan data DANA baru dengan format:\n\n";
	$reply .= "<code>nomor/nama</code>\n\n";
	$reply .= "<i>Contoh:</i> <code>0812-3456-7890/Ahmad Faisal</code>\n\n";
	$reply .= "<i>Pisahkan nomor dan nama dengan tanda / (slash)</i>";
} else {
	$method_label = 'ShopeePay';
	$reply = "🛍️ <b>Ubah Pengaturan ShopeePay</b>\n\n";
	$reply .= "Silakan masukkan data ShopeePay baru dengan format:\n\n";
	$reply .= "<code>nomor/nama</code>\n\n";
	$reply .= "<i>Contoh:</i> <code>0812-3456-7890/Ahmad Faisal</code>\n\n";
	$reply .= "<i>Pisahkan nomor dan nama dengan tanda / (slash)</i>";
}

$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '🔙 Batal', 'callback_data' => 'settings_payment']
	]
]);

$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, 'HTML', $keyboard);

updateUserPosition($chat_id, 'settings_edit_' . $payment_method);
?>
