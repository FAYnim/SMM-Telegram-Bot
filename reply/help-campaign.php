<?php

$reply = "📢 <b>Cara Membuat Campaign</b>\n\n"
    . "<b>Langkah-langkah:</b>\n\n"
    . "1️⃣ Masuk ke menu <b>Campaignku</b>\n"
    . "2️⃣ Pilih tombol <b>+ Buat Campaign</b>\n"
    . "3️⃣ Pilih jenis engagement (View/Like/Comment/Share/Follow)\n"
    . "4️⃣ Masukkan judul campaign\n"
    . "5️⃣ Masukkan link target postingan/akun\n"
    . "6️⃣ Tentukan harga per tugas (min. 50)\n"
    . "7️⃣ Tentukan total target jumlah\n"
    . "8️⃣ Konfirmasi dan simpan campaign\n\n"
    . "<b>Hal yang Perlu Diperhatikan:</b>\n"
    . "• Saldo Campaign akan dipotong sebesar <i>(harga x jumlah)</i> saat campaign disimpan\n"
    . "• Pastikan Saldo Campaign mencukupi sebelum membuat campaign\n"
    . "• Campaign dengan status <b>Active</b> dapat dikerjakan oleh worker\n"
    . "• Anda bisa Pause/Resume campaign kapan saja\n"
    . "• Anda bisa menambah saldo campaign jika saldo habis\n\n"
    . "<b>Tips:</b>\n"
    . "💡 Berikan reward yang kompetitif agar lebih cepat selesai\n"
    . "💡 Periksa campaign secara berkala untuk monitoring progress";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Kembali ke Bantuan', 'callback_data' => '/help']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
