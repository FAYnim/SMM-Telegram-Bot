<?php
require_once __DIR__ . '/../helpers/error-handler.php';

// Handle pause campaign callback
if($cb_data && strpos($cb_data, '/pause_campaign_') === 0) {
    // get campaign id
    $campaign_id = str_replace('/pause_campaign_', '', $cb_data);

    // Get campaign data from db
    $campaign = db_query("SELECT id, campaign_title, status, completed_count, campaign_balance, target_total, price_per_task "
        ."FROM smm_campaigns "
        ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

    if (!empty($campaign)) {
        $campaign_data = $campaign[0];

        // Update campaign status to paused
        $update_result = db_update('smm_campaigns', ['status' => 'paused'], ['id' => $campaign_id, 'client_id' => $user_id]);

        if (!$update_result) {
            sendErrorWithBackButton(
                $bot, 
                $chat_id, 
                null, 
                "❌ <b>Gagal Pause Campaign</b>\n\nTerjadi kesalahan saat mengpause campaign. Silakan coba lagi.", 
                '/select_campaign_' . $campaign_id,
                '🔙 Kembali'
            );
            return;
        }

        // Update position
/*        $position_result = updateUserPosition($chat_id, 'edit_campaign_detail');

        if (!$position_result) {
            $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!

Ketik /start untuk memulai ulang bot.");
            return;
        }*/

        // Get updated campaign data
        $updated_campaign = db_query("SELECT id, campaign_title, status, completed_count, campaign_balance, target_total, price_per_task "
            ."FROM smm_campaigns "
            ."WHERE id = ? AND client_id = ?", [$campaign_id, $user_id]);

        if (!empty($updated_campaign)) {
            $campaign_data = $updated_campaign[0];

            // Status icons
            $status_icons = [
                'creating' => '📝',
                'active' => '✅',
                'paused' => '⏸️',
                'completed' => '✅',
                'deleted' => '🗑️'
            ];

            $status_icon = $status_icons[$campaign_data['status']] ?? '❓';

            // Show updated campaign details
            $reply = "✏️ <b>Edit Campaign</b>\n\n" .
                "📝 <b>" . $campaign_data['campaign_title'] . "</b>\n" .
                "ID: <code>" . $campaign_data['id'] . "</code>\n" .
                "✅ Selesai: " . number_format($campaign_data['completed_count']) . "/" . number_format($campaign_data['target_total']) . " tugas\n" .
                "💰 Harga/Tugas: " . number_format($campaign_data['price_per_task'], 0, ',', '.') . "\n" .
                "💸 Saldo Campaign: " . number_format($campaign_data['campaign_balance'], 0, ',', '.') . "\n" .
                $status_icon . " Status: " . ucfirst($campaign_data['status']) . "\n\n" .
                "✅ Campaign berhasil di-pause!\n\n" .
                "Pilih yang ingin Anda ubah:";

            // Build keyboard (show Resume option since now paused)
            $keyboard_buttons = [];

            // Edit options
            $keyboard_buttons[] = [
                ['text' => '📝 Edit Judul', 'callback_data' => '/edit_campaign_title_' . $campaign_id],
                ['text' => '🎯 Edit Target', 'callback_data' => '/edit_campaign_target_' . $campaign_id]
            ];

            $keyboard_buttons[] = [
                ['text' => '💰 Tambah Saldo', 'callback_data' => '/add_campaign_balance_' . $campaign_id]
            ];

            // Show Resume option since campaign is now paused
            $keyboard_buttons[] = [
                ['text' => '▶️ Resume Campaign', 'callback_data' => '/resume_campaign_' . $campaign_id]
            ];

            // Back button
            $keyboard_buttons[] = [
                ['text' => '🔙 Kembali', 'callback_data' => '/select_campaign_' . $campaign_id]
            ];

            $keyboard = $bot->buildInlineKeyboard($keyboard_buttons);

            $bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);
        }
    } else {
        // Campaign not found
        sendErrorWithBackButton(
            $bot,
            $chat_id,
            $msg_id,
            "❌ <b>Campaign Tidak Ditemukan</b>\n\nCampaign tidak ditemukan atau tidak valid.\n\n<i>Silakan pilih campaign lain dari daftar.</i>",
            '/edit_campaign',
            '🔙 Kembali ke Daftar Campaign'
        );
    }
}
?>
