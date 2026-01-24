<?php
require_once 'helpers/error-handler.php';

if($cb_data && strpos($cb_data, '/add_campaign_balance_') === 0) {
    $campaign_id = str_replace('/add_campaign_balance_', '', $cb_data);

    $campaign = db_query("SELECT id, campaign_title, status, completed_count, campaign_balance, target_total, price_per_task "
        ."FROM smm_campaigns "
        ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

    if (!empty($campaign)) {
        $campaign_data = $campaign[0];

        $update_result = updateUserPosition($chat_id, 'add_campaign_balance', $campaign_id);

        if (!$update_result) {
            $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!\n\nKetik /start untuk memulai ulang bot.");
            return;
        }

        $reply = "💰 <b>Tambah Saldo Campaign</b>\n\n" .
            "📝 <b>" . $campaign_data['campaign_title'] . "</b>\n" .
            "ID: <code>" . $campaign_data['id'] . "</code>\n" .
            "✅ Selesai: " . number_format($campaign_data['completed_count']) . "/" . number_format($campaign_data['target_total']) . " tugas\n" .
            "💰 Harga/Tugas: " . number_format($campaign_data['price_per_task'], 0, ',', '.') . "\n" .
            "💸 Saldo Saat Ini: " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n\n" .
            "💵 <b>Masukkan jumlah saldo yang ingin ditambahkan:</b>\n\n" .
            "Contoh: <code>50000</code>\n" .
            "Minimal: 10.000\n\n";

        $keyboard = $bot->buildInlineKeyboard([
            [
                ['text' => '🔙 Kembali', 'callback_data' => '/edit_campaign_detail_' . $campaign_id]
            ]
        ]);

        $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
    } else {
        sendErrorWithBackButton(
            $bot,
            $chat_id,
            $msg_id,
            "❌ Campaign tidak ditemukan atau tidak valid.",
            "/cek_campaign"
        );
    }
}
?>
