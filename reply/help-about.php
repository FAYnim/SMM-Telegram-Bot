<?php

$reply = "📖 <b>Tentang SMM Bot Marketplace</b>\n\n"
    . "<b>Apa itu SMM Bot Marketplace?</b>\n"
    . "Bot ini adalah platform penghubung antara <b>Advertiser</b> (klien) dan <b>Worker</b> (pekerja).\n"
    . "Advertiser bisa membuat campaign untuk mendapatkan engagement media sosial, sedangkan Worker mendapatkan bayaran untuk menyelesaikan tugas.\n\n"
    . "<b>Jenis Campaign yang Tersedia:</b>\n"
    . "• View - Menambahkan penonton video\n"
    . "• Like - Memberikan like pada postingan\n"
    . "• Comment - Memberikan komentar\n"
    . "• Share - Membagikan konten\n"
    . "• Follow - Mengikuti akun\n\n"
    . "<b>Platform yang Didukung:</b>\n"
    . "• Instagram\n"
    . "• TikTok\n"
    . "• Dan platform lainnya\n\n"
    . "<b>Cara Kerja:</b>\n"
    . "1. Worker mengambil tugas yang tersedia\n"
    . "2. Worker mengerjakan tugas (like/follow/comment)\n"
    . "3. Worker upload bukti screenshot\n"
    . "4. Admin memverifikasi bukti\n"
    . "5. Worker menerima reward setelah disetujui\n\n"
    . "💡 <i>Untuk informasi lebih detail, silakan pilih menu bantuan lainnya.</i>";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali ke Bantuan', 'callback_data' => '/help']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
