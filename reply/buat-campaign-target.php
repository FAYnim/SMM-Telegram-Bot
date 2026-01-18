<?php
require_once 'helpers/error-handler.php';

// Validasi input target
$target = trim($message);
if (empty($target)) {
    $error_reply = "❌ Target total tidak boleh kosong!\n\nSilakan masukkan target total atau batal untuk membatalkan pembuatan campaign:";
    sendErrorWithBackButton(
        $bot, 
        $chat_id, 
        $msg_id,
        $error_reply,
        "/cek_campaign"
    );
    return;
}

// Validasi numeric
if (!is_numeric($target) || $target <= 0) {
    $error_reply = "❌ Target total harus berupa angka positif!\n\nSilakan masukkan target total atau batal untuk membatalkan pembuatan campaign:";
    sendErrorWithBackButton(
        $bot, 
        $chat_id, 
        $msg_id,
        $error_reply,
        "/cek_campaign"
    );
    return;
}

// Update target campaign di database
db_execute("UPDATE smm_campaigns SET target_total = ? WHERE client_id = ? AND status = 'creating'", [$target, $user_id]);

// Hapus pesan lama dengan msg_id
if ($msg_id) {
    $bot->deleteMessage($chat_id, $msg_id);
}

// Ambil semua data campaign yang baru dibuat dengan data akun medsos
$campaign = db_query(
    "SELECT c.id, c.campaign_title, c.type, c.link_target, c.price_per_task, c.target_total, c.campaign_balance, c.campaign_budget, c.created_at, " .
    "s.platform, s.username, s.account_url " .
    "FROM smm_campaigns c " .
    "LEFT JOIN smm_social_accounts s ON c.social_account_id = s.id " .
    "WHERE c.client_id = ? AND c.status = 'creating' " .
    "ORDER BY c.created_at DESC LIMIT 1",
    [$user_id]
);

if (!empty($campaign)) {
    $campaign_data = $campaign[0];

    /*$settings = db_read('smm_settings', ['category' => 'campaign']);
    $min_price_per_task = 100;
    if(!empty($settings)) {
        foreach($settings as $setting) {
            if($setting['setting_key'] == 'min_price_per_task') {
                $min_price_per_task = intval($setting['setting_value']);
                break;
            }
        }
    }

    $price_per_task = floor($campaign_data['campaign_balance'] / $target);
    
    // Recalculate campaign_balance
    $corrected_balance = $price_per_task * $target;

    if($price_per_task < $min_price_per_task) {
        $error_reply = "❌ <b>Harga Per Task Terlalu Rendah</b>\n\n";
        $error_reply .= "Harga per task yang kamu masukkan adalah Rp " . number_format($price_per_task, 0, ',', '.') . "\n";
        $error_reply .= "Minimum harga per task adalah Rp " . number_format($min_price_per_task, 0, ',', '.') . "\n\n";
        $error_reply .= "<b>Solusi:</b>\n";
        $error_reply .= "• Tambah total budget campaign, atau\n";
        $error_reply .= "• Kurangi jumlah target task\n\n";
        $error_reply .= "Silakan buat campaign baru!";

        sendErrorWithBackButton(
            $bot, 
            $chat_id, 
            $msg_id,
            $error_reply,
            "/cek_campaign"
        );
        return;
    }
*/
	// Calculate campaign_budget
	$campaign_price = $campaign_data['price_per_task'];
	$campaign_target = $campaign_data['target_total'];
	$campaign_balance = $campaign_data['campaign_balance'];
	$campaign_title = $campaign_data['title'];
	$campaign_type = $campaign_data['type'];
	$campaign_username = $campaign_data['username'];
	$campaign_link_target = $campaign_data['link_target'];
	$campaign_created_at = $campaign_data['created_at'];
	
	$calc_campaign_budget = $campaign_price * $campaign_target;
	
    // Update price_per_task and campaign_balance di database
    db_execute(
        "UPDATE smm_campaigns SET campaign_budget = ? WHERE id = ?", 
        [$calc_campaign_budget, $campaign_data['id']]
    );

    // Update campaign_data dengan nilai baru
    
    // Platform icons
    $platform_icons = [
        'instagram' => '📷',
        'tiktok' => '🎵',
        'youtube' => '▶️',
        'twitter' => '🐦',
        'facebook' => '👍'
    ];
    
    $platform_names = [
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'twitter' => 'Twitter',
        'facebook' => 'Facebook'
    ];
    
    $icon = $platform_icons[$campaign_data['platform']] ?? '📱';
    $platform_name = $platform_names[$campaign_data['platform']] ?? ucfirst($campaign_data['platform']);

    $reply = "<b>📋 Konfirmasi Campaign Baru</b>\n\n";
    $reply .= "Silakan periksa detail campaign Anda:\n\n";
    $reply .= "<b>📋 Detail Campaign:</b>\n";
    $reply .= "🆔 ID: #" . $campaign_data['id'] . "\n";
    $reply .= "📝 Judul: " . htmlspecialchars($campaign_title) . "\n";
    $reply .= "🎯 Tipe: " . ucfirst($campaign_type) . "s\n";
    $reply .= $icon . " Akun: <b>" . $platform_name . " - @" . $campaign_username . "</b>\n";
    $reply .= "🔗 Link: <code>" . $campaign_link_target. "</code>\n";
    $reply .= "💰 Harga/task: Rp " . number_format($campaign_price, 0, ',', '.') . "\n";
    $reply .= "🎯 Target: " . number_format($campaign_target) . " tasks\n";
    $reply .= "💰 Total Budget: Rp " . number_format($calc_campaign_budget, 0, ',', '.') . "\n";
    $reply .= "📅 Dibuat: " . date('d/m/Y H:i', strtotime($campaign_created_at)) . "\n\n";
    $reply .= "Apakah detail campaign sudah benar?";
} else {
    $reply = "<b>❓ Konfirmasi Campaign</b>\n\n";
    $reply .= "Apakah Anda ingin menyimpan campaign ini?";
}

$keyboard = $bot->buildInlineKeyboard([
    [
        ['text' => '✅ Simpan Campaign', 'callback_data' => '/simpan_campaign'],
    ],
    [
        ['text' => '❌ Batal', 'callback_data' => '/cek_campaign']
    ]
]);


$update_result = updateUserPosition($chat_id, 'buat_campaign_finish');

if (!$update_result) {
    $bot->sendMessage($chat_id, "❌ Terjadi kesalahan sistem!");
    return;
}

// Kirim pesan baru dengan keyboard dan dapatkan msg_id baru
$result = $bot->sendMessageWithKeyboard($chat_id, $reply, $keyboard);
$new_msg_id = $result['result']['message_id'] ?? null;

// Update msg_id baru di database
if ($new_msg_id) {
    db_execute("UPDATE smm_users SET msg_id = ? WHERE chatid = ?", [$new_msg_id, $chat_id]);
}

?>
