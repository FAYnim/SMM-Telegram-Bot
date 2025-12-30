<?php
require_once __DIR__ . '/../helpers/username-validator.php';

$update_result = updateUserPosition($chat_id, 'social');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

$reply = "<b>📱 Kelola Akun Media Sosial</b>\n\n";
$reply .= "Berikut adalah daftar akun yang telah Anda hubungkan:\n\n";

// System Logic
// Get user's social media accounts
$social_accounts = db_query("SELECT platform, username, account_url, status "
    ."FROM smm_social_accounts "
    ."WHERE user_id = ? AND status = 'active' "
	."ORDER BY platform, created_at", [$user_id]);

if (count($social_accounts) > 0) {
    foreach ($social_accounts as $account) {
        $icon = getPlatformIcon($account['platform']);
        $reply .= $icon . " " . ucfirst($account['platform']) . ": @" . $account['username'] . "\n";
    }
    $reply .= "\n";
} else {
    $reply .= "⚠️ <i>Belum ada akun terhubung.</i>\n";
    $reply .= "Hubungkan akun media sosial Anda untuk mulai mengambil tugas.\n\n";
}

$reply .= "👇 Gunakan menu di bawah ini:";

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '➕ Tambah Medsos', 'callback_data' => '/tambah_medsos'],
    ],
    [
        ['text' => '🎛️ Edit/Hapus Medsos', 'callback_data' => '/edit_medsos'],
    ],
    [
        ['text' => '🔙 Kembali', 'callback_data' => '/start']
    ]
]);

$bot->editMessage($chat_id, $msg_id, $reply, 'HTML', $keyboard);

?>
