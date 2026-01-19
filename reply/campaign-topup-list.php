<?php
$update_result = updateUserPosition($chat_id, 'campaign_topup');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Something Error!");
    return;
}

// Get user's campaigns
$campaigns = db_query("SELECT id, campaign_title, status "
    ."FROM smm_campaigns "
    ."WHERE client_id = ? AND status NOT IN ('deleted', 'creating', 'draft') "
    ."ORDER BY created_at DESC LIMIT 0,5", [$user_id]);

if (count($campaigns) > 0) {
    $keyboard_buttons = [];

    foreach ($campaigns as $campaign) {
        $display_text = "ID " . $campaign['id'] . " | " . $campaign['campaign_title'];
        $callback_data = '/topup_campaign_balance_' . $campaign['id'];

        $keyboard_buttons[] = [$display_text, $callback_data];
    }

    // back button
    $keyboard_buttons[] = ['🔙 Kembali', '/cek_saldo'];

    $keyboard = [];
    foreach ($keyboard_buttons as $button) {
        $keyboard[] = [
            ['text' => $button[0], 'callback_data' => $button[1]]
        ];
    }
    $keyboard = $bot->buildInlineKeyboard($keyboard);

    $reply = "💰 <b>Isi Saldo Campaign</b>\n\nSilakan pilih campaign yang ingin Anda isi saldo:";
} else {
    $reply = "⚠️ <b>Tidak ada campaign.</b>\n\nAnda belum membuat campaign apapun.";

    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/cek_saldo']
        ]
    ]);
}

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
