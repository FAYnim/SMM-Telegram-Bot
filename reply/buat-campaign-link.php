<?php
// Validasi input
$link = trim($message);
if (empty($link)) {
    if ($msg_id) {
        $bot->deleteMessage($chat_id, $msg_id);
    }
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/cek_campaign']
        ]
    ]);
    $result = $bot->sendMessageWithKeyboard($chat_id, "❌ Link tidak boleh kosong!\n\nSilakan masukkan link target atau batal untuk membatalkan pembuatan campaign:", $keyboard);
    $new_msg_id = $result['result']['message_id'] ?? null;
    if ($new_msg_id) {
        db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
    }
    return;
}

// Validasi link Instagram atau TikTok
$is_instagram = (strpos($link, 'instagram.com') !== false || strpos($link, 'instagr.am') !== false);
$is_tiktok = strpos($link, 'tiktok.com') !== false;

if (!$is_instagram && !$is_tiktok) {
    if ($msg_id) {
        $bot->deleteMessage($chat_id, $msg_id);
    }
    $keyboard = $bot->buildInlineKeyboard([
        [
            ['text' => '🔙 Kembali', 'callback_data' => '/cek_campaign']
        ]
    ]);
    $result = $bot->sendMessageWithKeyboard($chat_id, "❌ Link tidak valid!\n\nHanya link Instagram atau TikTok yang diperbolehkan.\n\nFormat yang benar:\n• Instagram: https://www.instagram.com/p/xxx/\n• TikTok: https://www.tiktok.com/@username/video/xxx\n\nSilakan masukkan link kembali atau batal untuk membatalkan pembuatan campaign:", $keyboard);
    $new_msg_id = $result['result']['message_id'] ?? null;
    if ($new_msg_id) {
        db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
    }
    return;
}

// Update judul campaign di database
db_execute("UPDATE smm_campaigns SET link_target = ? WHERE client_id = ? AND status = 'creating'", [$link, $user_id]);

// Hapus pesan lama dengan msg_id
if ($msg_id) {
    $bot->deleteMessage($chat_id, $msg_id);
}

$update_result = updateUserPosition($chat_id, 'buat_campaign_reward');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

$reply = "<b>📝 Buat Campaign - Reward</b>\n\n";
$reply .= "Silakan masukkan total reward untuk campaign ini:\n\n";
$reply .= "💡 <i>Contoh: 25000</i>\n\n";
$reply .= "💰 Ketik total reward:";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '🔙 Batal', 'callback_data' => '/cek_campaign']
    ]
]);

// Kirim pesan baru dengan keyboard dan dapatkan msg_id baru
$result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);
$new_msg_id = $result['result']['message_id'] ?? null;

// Update msg_id baru di database
if ($new_msg_id) {
    db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
}

?>
