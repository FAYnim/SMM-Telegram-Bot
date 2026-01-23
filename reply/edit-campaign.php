<?php
$update_result = updateUserPosition($chat_id, 'edit_campaign');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Get user's campaigns
$campaigns = db_query("SELECT id, campaign_title, status "
    ."FROM smm_campaigns "
    ."WHERE client_id = ? AND status NOT IN ('deleted', 'creating') "
    ."ORDER BY created_at DESC LIMIT 0,5", [$user_id]);

if (count($campaigns) > 0) {
    $keyboard_buttons = [];

    foreach ($campaigns as $campaign) {
        $display_text = "ID " . $campaign['id'] . " | " . $campaign['campaign_title'];
        $callback_data = '/select_campaign_' . $campaign['id'];

        $keyboard_buttons[] = [$display_text, $callback_data];
    }

    // back button
    $keyboard_buttons[] = ['🔙 Kembali', '/cek_campaign'];

    $keyboard = [];
    foreach ($keyboard_buttons as $button) {
        $keyboard[] = [
            ['text' => $button[0], 'callback_data' => $button[1]]
        ];
    }
    $keyboard = $bot->buildInlineKeyboard($keyboard);

    $reply = "📋 <b>Kelola Campaign</b>\n\nSilakan pilih campaign yang ingin Anda ubah:";
} else {
    $reply = "⚠️ <b>Tidak ada campaign.</b>\n\nAnda belum membuat campaign apapun.";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/cek_campaign']
        ]
    ]);
}

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
