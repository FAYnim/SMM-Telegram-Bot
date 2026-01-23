<?php

$reply = "💰 <b>Topup & Saldo</b>\n\n"
    . "<b>Jenis Saldo:</b>\n\n"
    . "1️⃣ <b>Saldo Campaign</b>\n"
    . "• Digunakan untuk membuat campaign\n"
    . "• Ditopup melalui menu Topup\n"
    . "• Saldo dikurangi saat campaign dibuat\n"
    . "• Bisa diisi ulang kapan saja\n\n"
    . "2️⃣ <b>Saldo Penghasilan</b>\n"
    . "• Hasil dari menyelesaikan tugas\n"
    . "• Dapat ditarik ke E-Wallet\n"
    . "• Dapat ditransfer ke Saldo Campaign\n"
    . "• Minimal withdraw 50.000\n\n"
    . "<b>Cara Topup Saldo Campaign:</b>\n"
    . "1. Masuk menu <b>Saldo Campaign</b>\n"
    . "2. Pilih tombol <b>Topup</b>\n"
    . "3. Pilih nominal topup\n"
    . "4. Transfer ke rekening yang ditampilkan\n"
    . "5. Kirim bukti transfer via bot\n"
    . "6. Tunggu verifikasi dari admin (1-24 jam)\n"
    . "7. Saldo akan bertambah setelah disetujui\n\n"
    . "<b>Minimal Topup:</b> 10.000\n\n"
    . "<b>Metode Pembayaran:</b>\n"
    . "• Transfer Bank (BCA, Mandiri, BNI, dll)\n"
    . "• E-Wallet (DANA, OVO, GoPay, ShopeePay)\n\n"
    . "<b>Tips:</b>\n"
    . "💡 Simpan bukti transfer untuk konfirmasi\n"
    . "💡 Pastikan nominal transfer sesuai\n"
    . "💡 Hubungi admin jika topup belum masuk > 24 jam";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali ke Bantuan', 'callback_data' => '/help']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
