<?php

$reply = "💼 <b>Cara Kerjakan Tugas</b>\n\n"
    . "<b>Langkah-langkah:</b>\n\n"
    . "1️⃣ Masuk ke menu <b>Cari Cuan</b>\n"
    . "2️⃣ Pilih tugas yang tersedia\n"
    . "3️⃣ Klik tombol <b>Ambil Tugas</b>\n"
    . "4️⃣ Kerjakan tugas sesuai instruksi (like/follow/comment dll)\n"
    . "5️⃣ Klik tombol <b>Upload Bukti</b>\n"
    . "6️⃣ Kirim screenshot sebagai bukti\n"
    . "7️⃣ Tunggu verifikasi dari admin\n"
    . "8️⃣ Saldo akan bertambah jika disetujui\n\n"
    . "<b>Hal yang Perlu Diperhatikan:</b>\n"
    . "• Setiap tugas hanya bisa diambil oleh satu worker\n"
    . "• Tugas harus selesai dalam waktu yang ditentukan\n"
    . "• Bukti harus jelas dan sesuai dengan instruksi\n"
    . "• Jangan mengambil tugas jika tidak bisa mengerjakannya\n"
    . "• Admin akan menolak bukti jika tidak valid\n\n"
    . "<b>Status Tugas:</b>\n"
    . "• <b>Available</b> - Tugas tersedia untuk diambil\n"
    . "• <b>Taken</b> - Tugas sedang dikerjakan\n"
    . "• <b>Pending Review</b> - Menunggu verifikasi admin\n"
    . "• <b>Approved</b> - Tugas disetujui, reward diterima\n"
    . "• <b>Rejected</b> - Tugas ditolak, tidak ada reward\n\n"
    . "<b>Tips:</b>\n"
    . "💡 Ambil tugas sesuai kemampuan dan waktu Anda\n"
    . "💡 Pastikan screenshot jelas dan menunjukkan bukti yang diminta";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali ke Bantuan', 'callback_data' => '/help']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
