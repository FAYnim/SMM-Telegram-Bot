<?php

$reply = "👤 <b>Akun Media Sosial</b>\n\n"
    . "<b>Kenapa Perlu Daftar Akun Medsos?</b>\n"
    . "• Untuk validasi tugas yang Anda ambil\n"
    . "• Admin memverifikasi bukti dengan akun yang terdaftar\n"
    . "• Mencegah penggunaan akun orang lain\n\n"
    . "<b>Platform yang Didukung:</b>\n"
    . "• Instagram\n"
    . "• TikTok\n\n"
    . "<b>Cara Menambah Akun:</b>\n"
    . "1. Masuk menu <b>Akun Medsos</b>\n"
    . "2. Pilih <b>+ Tambah Akun</b>\n"
    . "3. Pilih platform (Instagram/TikTok)\n"
    . "4. Masukkan username akun\n"
    . "5. Akun akan ditambahkan ke daftar\n\n"
    . "<b>Cara Edit Akun:</b>\n"
    . "1. Masuk menu <b>Akun Medsos</b>\n"
    . "2. Pilih akun yang ingin diedit\n"
    . "3. Klik tombol edit\n"
    . "4. Masukkan username baru\n"
    . "5. Perubahan akan disimpan\n\n"
    . "<b>Cara Hapus Akun:</b>\n"
    . "1. Masuk menu <b>Akun Medsos</b>\n"
    . "2. Pilih akun yang ingin dihapus\n"
    . "3. Klik tombol hapus\n"
    . "4. Konfirmasi penghapusan\n\n"
    . "<b>Hal yang Perlu Diperhatikan:</b>\n"
    . "• Username harus sesuai dengan akun asli\n"
    . "• Jangan menggunakan akun palsu/invalid\n"
    . "• Akun yang tidak valid akan ditolak oleh admin\n"
    . "• Bisa mendaftarkan lebih dari satu akun\n\n"
    . "<b>Tips:</b>\n"
    . "💡 Pastikan akun dalam kondisi baik dan tidak terblokir\n"
    . "💡 Update username jika ada perubahan\n"
    . "💡 Hapus akun yang sudah tidak aktif";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali ke Bantuan', 'callback_data' => '/help']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
