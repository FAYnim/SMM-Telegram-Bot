<?php
// Handler untuk input nomor tujuan withdraw
$destination_account = trim($message);

// Validasi nomor tidak boleh kosong
if (empty($destination_account)) {
    $reply = "❌ <b>Input Tidak Valid</b>\n\n"
        . "Nomor tujuan tidak boleh kosong.\n\n"
        . "💡 <i>Contoh: 081234567890</i>";

    $bot->deleteMessage($chat_id, $msg_id);
    $bot->sendMessage($chat_id, $reply);
    return;
}

// Validasi nomor harus mengandung angka
if (!preg_match('/[0-9]/', $destination_account)) {
    $reply = "❌ <b>Format Nomor Tidak Valid</b>\n\n"
        . "Nomor tujuan harus mengandung angka.\n\n"
        . "💡 <i>Contoh: 081234567890</i>";

    $bot->deleteMessage($chat_id, $msg_id);
    $bot->sendMessage($chat_id, $reply);
    return;
}

// Ambil nominal dari submenu (disimpan di step sebelumnya)
$amount = (int) $submenu;

if ($amount <= 0) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan. Silakan ulangi proses withdraw.");
    updateUserPosition($chat_id, 'main', '');
    return;
}

// Cek saldo sekali lagi untuk keamanan
$wallet_result = db_read('smm_wallets', ['user_id' => $user_id]);
if (empty($wallet_result)) {
    $bot->sendMessage($chat_id, "❌ Wallet tidak ditemukan!");
    return;
}

$current_balance = $wallet_result[0]['balance'];

if ($amount > $current_balance) {
    $reply = "❌ <b>Saldo Tidak Mencukupi</b>\n\n"
        . "Terjadi perubahan saldo saat proses withdraw.\n"
        . "Saldo Anda saat ini: Rp " . number_format($current_balance, 0, ',', '.') . "\n\n"
        . "Silakan ulangi proses withdraw dengan nominal yang sesuai.";

    updateUserPosition($chat_id, 'main', '');
    $bot->deleteMessage($chat_id, $msg_id);
    $bot->sendMessage($chat_id, $reply);
    return;
}

// Reset posisi user
updateUserPosition($chat_id, 'main', '');

// Hapus pesan lama dan kirim pesan waiting
$bot->deleteMessage($chat_id, $msg_id);
$waiting_result = $bot->sendMessage($chat_id, "⏳ Sedang memproses permintaan withdraw...");
$waiting_msg_id = $waiting_result['result']['message_id'];

// Simpan msg_id waiting
if ($waiting_msg_id) {
    db_update('smm_users', ['msg_id' => $waiting_msg_id], ['chatid' => $chat_id]);
}

// Simpan data withdraw ke database dengan status pending
$withdraw_data = [
    'user_id' => $user_id,
    'amount' => $amount,
    'destination_account' => $destination_account,
    'fee' => 0, // MVP: no fee
    'status' => 'pending'
];

$withdraw_id = db_create('smm_withdrawals', $withdraw_data);

if (!$withdraw_id) {
    $bot->editMessage($chat_id, $waiting_msg_id, "❌ Gagal membuat permintaan withdraw. Silakan coba lagi.", 'HTML');
    return;
}

// Notifikasi ke Admin
$admins = db_read("smm_admins");

if ($admins) {
    foreach ($admins as $admin) {
        $admin_id = $admin["chatid"];

        // Siapkan data user untuk display
        $sender_name = $username ? "@" . $username : $first_name;

        $reply_admin = "💸 <b>WITHDRAW BARU!</b>\n\n"
            . "User: " . $sender_name . " (ID: " . $chat_id . ")\n"
            . "💰 Nominal: Rp " . number_format($amount, 0, ',', '.') . "\n"
            . "💳 Tujuan: " . $destination_account . "\n"
            . "📅 Waktu: " . date('d M Y, H:i') . "\n\n"
            . "Silakan proses permintaan withdraw ini.";

        $keyboard_admin = $bot->buildInlineKeyboard([
            [
                ['text' => '✅ Terima', 'callback_data' => 'admin_approve_withdraw_' . $withdraw_id],
                ['text' => '❌ Tolak', 'callback_data' => 'admin_reject_withdraw_' . $withdraw_id]
            ]
        ]);

        $bot->sendMessageWithKeyboard($admin_id, $reply_admin, $keyboard_admin);
        sleep(1); // Mencegah rate limit
    }
}

// Update pesan user jadi Final
$reply_user = "✅ <b>Permintaan Withdraw Terkirim</b>\n\n"
    . "💰 Nominal: Rp " . number_format($amount, 0, ',', '.') . "\n"
    . "💳 Tujuan: " . $destination_account . "\n\n"
    . "⏳ <b>Status: Menunggu Verifikasi</b>\n"
    . "Admin kami akan memproses permintaan withdraw Anda. Dana akan ditransfer setelah disetujui (Estimasi 1-24 jam).";

$keyboard_user = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali ke Menu Utama', 'callback_data' => '/start']
    ]
]);

$bot->editMessage($chat_id, $waiting_msg_id, $reply_user, 'HTML', $keyboard_user);
?>
