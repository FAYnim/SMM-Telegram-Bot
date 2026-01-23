<?php

$update_result = updateUserPosition($chat_id, 'riwayat-topup');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

// Get 5 latest deposit history for this user
$deposits = db_query("SELECT * FROM smm_deposits WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$user_id]);

if (!$deposits) {
    $reply = "📋 <b>Riwayat Topup</b>\n\n";
    $reply .= "Belum ada riwayat topup.\n\n";
    $reply .= "<i>Untuk melakukan topup, gunakan tombol di bawah.</i>";
} else {
    $reply = "📋 <b>Riwayat Topup</b>\n\n";

    foreach ($deposits as $deposit) {
        if ($deposit['status'] == 'approved') {
            $status = '✅ Disetujui';
        } elseif ($deposit['status'] == 'rejected') {
            $status = '❌ Ditolak';
        } elseif ($deposit['status'] == 'canceled') {
            $status = '🚫 Dibatalkan';
        } else {
            $status = '⏳ Menunggu Verifikasi';
        }

        $reply .= "💰 <b>Rp " . number_format($deposit['amount'], 0, ',', '.') . "</b>\n";
        $reply .= "📅 " . date('d/m/Y H:i', strtotime($deposit['created_at'])) . "\n";
        $reply .= "Status: " . $status . "\n";
        
        if ($deposit['processed_at']) {
            $reply .= "✓ Diproses: " . date('d/m/Y H:i', strtotime($deposit['processed_at'])) . "\n";
        }
        
        if ($deposit['admin_notes']) {
            $reply .= "📝 Catatan: " . $deposit['admin_notes'] . "\n";
        }
        
        $reply .= "\n";
    }
}

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '💰 Topup', 'callback_data' => '/topup']
    ],
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/cek_saldo']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
