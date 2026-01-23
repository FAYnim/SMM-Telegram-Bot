<?php

$reply = "💸 <b>Withdraw & Transfer</b>\n\n"
    . "<b>Opsi Penarikan:</b>\n\n"
    . "1️⃣ <b>Withdraw ke E-Wallet</b>\n"
    . "• Tarik Saldo Penghasilan ke DANA/OVO/GoPay\n"
    . "• Minimal withdraw: 50.000\n"
    . "• Proses: 1-24 jam setelah disetujui admin\n\n"
    . "2️⃣ <b>Transfer ke Saldo Campaign</b>\n"
    . "• Pindahkan Saldo Penghasilan ke Saldo Campaign\n"
    . "• Minimal transfer: 1.000\n"
    . "• Instan, tidak butuh verifikasi admin\n"
    . "• Digunakan untuk membuat campaign\n\n"
    . "<b>Cara Withdraw ke E-Wallet:</b>\n"
    . "1. Masuk menu <b>Tarik Dana</b>\n"
    . "2. Pilih <b>E-Wallet</b>\n"
    . "3. Masukkan nominal (min. 50.000)\n"
    . "4. Masukkan nomor E-Wallet\n"
    . "5. Tunggu proses verifikasi\n"
    . "6. Dana akan ditransfer setelah disetujui\n\n"
    . "<b>Cara Transfer ke Saldo Campaign:</b>\n"
    . "1. Masuk menu <b>Tarik Dana</b>\n"
    . "2. Pilih <b>Saldo Campaign</b>\n"
    . "3. Masukkan nominal (min. 1.000)\n"
    . "4. Saldo akan langsung bertambah\n\n"
    . "<b>Hal yang Perlu Diperhatikan:</b>\n"
    . "• Pastikan nomor E-Wallet valid\n"
    . "• Tidak ada biaya admin ( GRATIS )\n"
    . "• Admin akan memverifikasi setiap permintaan withdraw\n"
    . "• Withdraw yang ditolak akan dikembalikan ke Saldo Penghasilan\n\n"
    . "<b>Tips:</b>\n"
    . "💡 Transfer ke Saldo Campaign untuk membuat campaign baru\n"
    . "💡 Withdraw hanya jika benar-benar butuh dana\n"
    . "💡 Cek riwayat withdraw di menu Saldo Campaign";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali ke Bantuan', 'callback_data' => '/help']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
