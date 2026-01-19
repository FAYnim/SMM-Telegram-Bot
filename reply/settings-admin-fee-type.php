<?php
// Handler untuk callback pemilihan tipe admin fee (flat atau percentage)

if($cb_data == "settings_admin_fee_flat") {
	$fee_type = 'flat';
	$update_result = updateUserPosition($chat_id, 'settings_input_admin_fee_flat', '');
} elseif($cb_data == "settings_admin_fee_percentage") {
	$fee_type = 'percentage';
	$update_result = updateUserPosition($chat_id, 'settings_input_admin_fee_percentage', '');
} else {
	$bot->sendMessage($chat_id, '❌ Perintah tidak dikenali');
	return;
}

if(!$update_result) {
	$bot->sendMessage($chat_id, '❌ Terjadi kesalahan sistem');
	return;
}

if($fee_type == 'flat') {
	$reply = "💵 <b>Biaya Admin - Flat (Nominal)</b>\n\n";
	$reply .= "Silakan masukkan biaya admin dalam Rupiah:\n\n";
	$reply .= "<i>Contoh: 5000</i>\n\n";
	$reply .= "<i>Nilai harus berupa angka bulat positif tanpa titik atau koma</i>";
} else {
	$reply = "📊 <b>Biaya Admin - Persentase</b>\n\n";
	$reply .= "Silakan masukkan persentase biaya admin:\n\n";
	$reply .= "<i>Contoh: 2.5 atau 5</i>\n";
	$reply .= "<i>Range: 0.1% - 100%</i>\n";
	$reply .= "<i>Gunakan titik (.) untuk desimal</i>";
}

$keyboard = $bot->buildInlineKeyboard([
	[
		['text' => '🔙 Batal', 'callback_data' => 'settings_withdraw']
	]
]);

$bot->editMessage($chat_id, $bot->getCallbackMessageId(), $reply, 'HTML', $keyboard);
?>
