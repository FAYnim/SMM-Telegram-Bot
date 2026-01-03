<?php
require_once 'TelegramBot.php';
require_once 'db.php';
require_once 'config/config.php';

// Fungsi handle posisi user
function updateUserPosition($chatid, $menu, $submenu = '') {
    $sql = "UPDATE smm_users SET menu = ?, submenu = ? WHERE chatid = ?";
    $params = [$menu, $submenu, $chatid];
    
    $result = db_execute($sql, $params);
    
    // Log position update result
    $position_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => 'update_position',
        'chatid' => $chatid,
        'menu' => $menu,
        'submenu' => $submenu,
        'result' => $result
    ];
    file_put_contents('log/position.log', json_encode($position_log));
    if($menu === "main") {
    	$result = 1;
    }
    return $result;
}

// Inisialisasi bot
$bot = new TelegramBot($bot_token);

// Ambil data dari Telegram
$chat_id = $bot->getChatId();
$message = $bot->getMessage();
$username = $bot->getUsername();
$first_name = $bot->getFirstName();
$last_name = $bot->getLastName();
$cb_data = $bot->getCallbackData();

// Cek apakah user mengirim file (foto atau dokumen)
$photo = $bot->getPhoto();
$document = $bot->getDocument();
$caption = $bot->getCaption();
$file_type = null;

// Log file detection
$file_log = [
    'timestamp' => date('Y-m-d H:i:s'),
    'chat_id' => $chat_id,
    'file_type' => $file_type,
    'file_id' => $file_id ?? null,
    'caption' => $caption,
    'has_photo' => $photo ? true : false,
    'has_document' => $document ? true : false
];
file_put_contents('log/file.log', json_encode($file_log));

// Trace log
$log_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'chat_id' => $chat_id,
    'msg_id' => $msg_id,
    'message' => $message,
    'username' => $username,
    'cb_data' => $bot->getCallbackData(),
    'update' => $bot->getUpdate()
];
file_put_contents('log/trace.log', json_encode($log_data, JSON_PRETTY_PRINT));

// Validasi input
if (!$chat_id || (!$message && !$bot->getCallbackData() && !$photo && !$document)) {
    exit();
}
// Handle file upload
$debug_log = [
	'timestamp' => date('Y-m-d H:i:s'),
	'chat_id' => $chat_id,
	'cb_data' => $cb_data,
	'photo_exists' => $photo ? true : false,
	'document_exists' => $document ? true : false,
	'photo_data' => $photo,
	'document_data' => $document,
	'full_update' => $bot->getUpdate()
];
file_put_contents('log/debug.log', json_encode($debug_log, JSON_PRETTY_PRINT));

// Check atau insert user ke database
$user = db_read('smm_users', ['chatid' => $chat_id]);

if (empty($user)) {
    // Insert user baru
    $full_name = trim($first_name . ' ' . $last_name);
    $user_data = [
        'chatid' => $chat_id,
        'username' => $username,
        'full_name' => $full_name,
        'role' => 'user', // default role
        'status' => 'active'
    ];
    $user_id = db_create('smm_users', $user_data);
}

$user = db_read('smm_users', ['chatid' => $chat_id]);
$user_id = $user[0]['id'];
$role = $user[0]['role'];
$menu = $user[0]['menu'];
$submenu = $user[0]['submenu'];
$msg_id = $user[0]['msg_id'] ?? null;

// Cek apakah user adalah admin
$admin = db_read('smm_admins', ['chatid' => $chat_id]);
if (!empty($admin)) {
    $role = 'admin';
}

//	FOR DEBUGGING ONLY:
//	$reply .= "<pre>".json_encode($user)."</pre>";

// Include reply handlers
if(!$cb_data){
	if ($message == "/start") {
		require_once 'reply/start.php';
	}
	// ADMIN
	if($role == "admin") {
		if(strpos($submenu, 'topup_approve_') === 0) {
			require_once 'reply/admin-topup-approve.php';
		}
		if(strpos($submenu, 'topup_reject_') === 0) {
			require_once 'reply/admin-topup-reject.php';
		}
	}

	// USER
	if ($message == "/social") {
		require_once 'reply/social.php';
	}

	if ($user[0]['menu'] == 'add_instagram' || $user[0]['menu'] == 'add_tiktok') {
		require_once 'reply/tambah-medsos.php';
	}
	if ($user[0]['menu'] == 'edit_username') {
		require_once 'reply/edit-username.php';
	}

	// campaign
	if ($user[0]['menu'] == 'buat_campaign_type') {
		require_once 'reply/buat-campaign-judul.php';
	}
	if ($user[0]['menu'] == 'buat_campaign_link') {
		require_once 'reply/buat-campaign-link.php';
	}
	if ($user[0]['menu'] == 'buat_campaign_reward') {
		require_once 'reply/buat-campaign-reward.php';
	}
	if ($user[0]['menu'] == 'buat_campaign_target') {
		require_once 'reply/buat-campaign-target.php';
	}


	if ($photo) {
		// Get File Data
		$file_id = $bot->getPhotoFileId();
		$file_info = $bot->getFile($file_id);
		$file_url = null;

		// Get download URL
		if ($file_info && isset($file_info['result']['file_path'])) {
			$file_url = $bot->getFileUrl($file_info['result']['file_path']);
		}

		// Log
		$file_info_log = [
			'timestamp' => date('Y-m-d H:i:s'),
			'chat_id' => $chat_id,
			'file_id' => $file_id,
			'file_info' => $file_info,
			'file_url' => $file_url,
			'caption' => $caption
		];
		file_put_contents('log/file_info.log', json_encode($file_info_log, JSON_PRETTY_PRINT));

		if($user[0]['menu'] == "confirm_topup") {
			include "reply/topup-proof.php";
		}
//		$bot->sendPhoto($chat_id, $file_id);

		// DEBUGGING ONLY:
/*		$reply = "📷 Foto terdeteksi!\nFile ID: " . $file_id;
		if ($caption) {
			$reply .= "\nCaption: " . $caption;
		}
		if ($file_url) {
			$reply .= "\n📥 Download URL: " . $file_url;
		}
		$reply .= "\n<pre>".json_encode($file_info, JSON_PRETTY_PRINT)."</pre>";
		$bot->sendMessage($chat_id, $reply);*/
	}

	if ($document) {
		// DEBUGGING ONLY:
/*		$file_id = $bot->getDocumentFileId();
		$reply = "📄 Dokumen terdeteksi!\nFile ID: " . $file_id;
		if ($caption) {
			$reply .= "\nCaption: " . $caption;
		}
		$bot->sendMessage($chat_id, $reply);*/
	}
} else {
	// ADMIN
	if(strpos($cb_data, 'admin_approve_topup_') === 0) {
		require_once 'reply/admin-topup.php';
	}
	if(strpos($cb_data, 'admin_reject_topup_') === 0) {
		require_once 'reply/admin-topup.php';
	}

	// USER
	if($cb_data == "/start") {
		require_once 'reply/start.php';
	}
	if($cb_data == "/social") {
		require_once 'reply/social.php';
	}
	// Cek Saldo
	if($cb_data == "/cek_saldo") {
		require_once 'reply/cek-saldo.php';
	}
	if($cb_data == "/riwayat_topup") {
		require_once 'reply/riwayat-topup.php';
	}
	// Topup
	if($cb_data == "/topup") {
		require_once 'reply/topup.php';
	}
	if(strpos($cb_data, '/topup_') === 0) {
		require_once 'reply/opsi-topup.php';
	}
	if($cb_data == "/konfirmasi_topup") {
		require_once 'reply/konfirmasi-topup.php';
	}

	// Add New Account
	if($cb_data == "/tambah_medsos") {
		require_once 'reply/tambah-medsos.php';
	}
	if($cb_data == "/add_instagram") {
		require_once 'reply/tambah-medsos.php';
	}
	if($cb_data == "/add_tiktok") {
		require_once 'reply/tambah-medsos.php';
	}

	// Edit Account
	if($cb_data == "/edit_medsos") {
		require_once 'reply/edit-medsos.php';
	}
	if(strpos($cb_data, '/edit_account_') === 0) {
		require_once 'reply/edit-medsos.php';
	}

	// Delete Account
	if(strpos($cb_data, '/delete_account_') === 0) {
		require_once 'reply/delete-medsos.php';
	}
	if(strpos($cb_data, '/confirm_delete_') === 0) {
		require_once 'reply/delete-medsos.php';
	}
	// Edit Username
	if(strpos($cb_data, '/edit_username_') === 0) {
		require_once 'reply/edit-username.php';
	}

	// Campaign
	if($cb_data == "/cek_campaign") {
		require_once 'reply/cek-campaign.php';
	}
	if($cb_data == "/buat_campaign") {
		require_once 'reply/buat-campaign.php';
	}
	if(strpos($cb_data, '/buat_campaign_') === 0) {
		require_once 'reply/buat-campaign-type.php';
	}
	if($cb_data == "/simpan_campaign") {
		require_once 'reply/buat-campaign-simpan.php';
	}

	// edit campaign
	if($cb_data == "/edit_campaign") {
		require_once 'reply/edit-campaign.php';
	}
	if(strpos($cb_data, '/select_campaign_') === 0) {
		require_once 'reply/select-campaign.php';
	}
	if(strpos($cb_data, '/edit_campaign_detail_') === 0) {
		require_once 'reply/edit-campaign-detail.php';
	}

	// delete campaign
	if(strpos($cb_data, '/delete_campaign_confirm_') === 0) {
		require_once 'reply/delete-campaign-confirm.php';
	} elseif (strpos($cb_data, '/delete_campaign_') === 0) {
		require_once 'reply/delete-campaign.php';
	}
}

// Trace keyboard structure
$keyboard_trace = [
    'timestamp' => date('Y-m-d H:i:s'),
    'chat_id' => $chat_id,
    'cb_data' => $cb_data,
    'message' => $message,
    'user_position' => [
        'menu' => $user[0]['menu'] ?? 'unknown',
        'submenu' => $user[0]['submenu'] ?? ''
    ],
    'keyboard_structure' => isset($keyboard) ? $keyboard : 'not_set'
];

file_put_contents('log/keyboard.log', json_encode($keyboard_trace, JSON_PRETTY_PRINT));

?>
