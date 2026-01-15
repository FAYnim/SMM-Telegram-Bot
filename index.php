<?php
require_once 'TelegramBot.php';
require_once 'db.php';
require_once 'config/config.php';
require_once 'helpers/index-helper.php';

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
logMessage('file_detection', [
    'chat_id' => $chat_id,
    'file_type' => $file_type,
    'caption' => $caption,
    'has_photo' => $photo ? true : false,
    'has_document' => $document ? true : false
], 'debug');

// Trace log
logMessage('trace', [
    'chat_id' => $chat_id,
    'message' => $message,
    'username' => $username,
    'cb_data' => $bot->getCallbackData(),
    'update' => $bot->getUpdate()
], 'debug');

// Validasi input
if (!$chat_id || (!$message && !$bot->getCallbackData() && !$photo && !$document)) {
    exit();
}

// Debug log
logMessage('debug', [
    'chat_id' => $chat_id,
    'cb_data' => $cb_data,
    'photo_exists' => $photo ? true : false,
    'document_exists' => $document ? true : false,
    'photo_data' => $photo,
    'document_data' => $document,
    'full_update' => $bot->getUpdate()
], 'debug');

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
		if(strpos($submenu, 'task_approve_') === 0) {
			require_once 'reply/admin-task-approve.php';
		}
		if(strpos($submenu, 'task_reject_') === 0) {
			require_once 'reply/admin-task-reject.php';
		}
		if(strpos($submenu, 'withdraw_approve_') === 0) {
			require_once 'reply/admin-withdraw-approve.php';
		}
		if(strpos($submenu, 'withdraw_reject_') === 0) {
			require_once 'reply/admin-withdraw-reject.php';
		}
		if(strpos($submenu, 'campaign_reject_') === 0) {
			require_once 'reply/admin-campaign-reject.php';
		}
	}

	// USER
	if ($menu == 'add_instagram' || $menu == 'add_tiktok') {
		require_once 'reply/tambah-medsos.php';
	}
	if ($menu == 'edit_username') {
		require_once 'reply/edit-username.php';
	}
	if ($menu == 'settings_edit_dana' || $menu == 'settings_edit_shopeepay') {
		require_once 'reply/settings-process-payment.php';
	}
	if ($menu == 'settings_edit_min_withdraw' || $menu == 'settings_edit_admin_fee') {
		require_once 'reply/settings-process-withdraw.php';
	}
	if ($menu == 'settings_edit_min_price_per_task') {
		require_once 'reply/settings-process-campaign.php';
	}

	// Withdraw
	if ($menu == 'withdraw_amount') {
		require_once 'reply/withdraw-amount.php';
	}
	if ($menu == 'withdraw_destination') {
		require_once 'reply/withdraw-destination.php';
	}
	if ($menu == 'withdraw_campaign_amount') {
		require_once 'reply/withdraw-campaign-amount.php';
	}

	// campaign
	if ($menu == 'buat_campaign_type') {
		require_once 'reply/buat-campaign-judul.php';
	}
	if ($menu == 'buat_campaign_link') {
		require_once 'reply/buat-campaign-link.php';
	}
	if ($menu == 'buat_campaign_reward') {
		require_once 'reply/buat-campaign-reward.php';
	}
	if ($menu == 'buat_campaign_target') {
		require_once 'reply/buat-campaign-target.php';
	}

	// Handle campaign edit inputs
	if ($menu == 'edit_campaign_title') {
		require_once 'reply/process-edit-campaign-title.php';
	}
	if ($menu == 'edit_campaign_target') {
		require_once 'reply/process-edit-campaign-target.php';
	}
	if ($menu == 'add_campaign_balance') {
		require_once 'reply/process-add-campaign-balance.php';
	}
	// edit campaign


	if ($photo) {
		// Get File Data
		$file_id = $bot->getPhotoFileId();
		$file_info = $bot->getFile($file_id);
		$file_url = null;

		// Get download URL
		if ($file_info && isset($file_info['result']['file_path'])) {
			$file_url = $bot->getFileUrl($file_info['result']['file_path']);
		}

		// Log file info
		logMessage('file_info', [
			'chat_id' => $chat_id,
			'file_id' => $file_id,
			'file_info' => $file_info,
			'file_url' => $file_url,
			'caption' => $caption
		], 'debug');

		if($menu == "confirm_topup") {
			include "reply/topup-proof.php";
		}

		if($menu == "upload_proof") {
			include "reply/task-proof.php";
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
	
	// campaign
	if($cb_data == "campaign_admin") {
		require_once 'reply/campaign-admin.php';
	}
	
	// topup
	if($cb_data == "deposit_admin") {
		require_once 'reply/deposit-admin.php';
	}
	if(strpos($cb_data, 'admin_approve_topup_') === 0) {
		require_once 'reply/admin-topup.php';
	}
	if(strpos($cb_data, 'admin_reject_topup_') === 0) {
		require_once 'reply/admin-topup.php';
	}

	// withdraw
	if(strpos($cb_data, 'admin_approve_withdraw_') === 0) {
		require_once 'reply/admin-withdraw.php';
	}
	if(strpos($cb_data, 'admin_reject_withdraw_') === 0) {
		require_once 'reply/admin-withdraw.php';
	}

	// task
	if(strpos($cb_data, 'admin_approve_task_') === 0) {
		require_once 'reply/admin-task.php';
	}
	if(strpos($cb_data, 'admin_reject_task_') === 0) {
		require_once 'reply/admin-task.php';
	}

	// campaign
	if(strpos($cb_data, 'admin_approve_campaign_') === 0) {
		require_once 'reply/admin-campaign.php';
	}
	if(strpos($cb_data, 'admin_reject_campaign_') === 0) {
		require_once 'reply/admin-campaign.php';
	}

	// task
	if($cb_data == "verifikasi") {
		require_once 'reply/task-admin.php';
	}

	// withdraw
	if($cb_data == "withdraw_admin") {
		require_once 'reply/withdraw-admin.php';
	}

	// settings
	if($cb_data == "settings") {
		require_once 'reply/settings.php';
	}
	if($cb_data == "settings_payment") {
		require_once 'reply/settings-payment.php';
	}
	if($cb_data == "settings_withdraw") {
		require_once 'reply/settings-withdraw.php';
	}
	if($cb_data == "settings_campaign") {
		require_once 'reply/settings-campaign.php';
	}
	if($cb_data == "settings_edit_dana" || $cb_data == "settings_edit_shopeepay") {
		require_once 'reply/settings-edit-payment.php';
	}
	if($cb_data == "settings_edit_min_withdraw" || $cb_data == "settings_edit_admin_fee") {
		require_once 'reply/settings-edit-withdraw.php';
	}
	if($cb_data == "settings_edit_min_price_per_task") {
		require_once 'reply/settings-edit-campaign.php';
	}

	// Close Notification
	if($cb_data == "close_notif") {
		require_once 'reply/close-notif.php';
	}

	// USER
	if($cb_data == "/start") {
		require_once 'reply/start.php';
	}
	if($cb_data == "/help") {
		require_once 'reply/help.php';
	}
	if($cb_data == "/help_about") {
		require_once 'reply/help-about.php';
	}
	if($cb_data == "/help_campaign") {
		require_once 'reply/help-campaign.php';
	}
	if($cb_data == "/help_task") {
		require_once 'reply/help-task.php';
	}
	if($cb_data == "/help_saldo") {
		require_once 'reply/help-saldo.php';
	}
	if($cb_data == "/help_withdraw") {
		require_once 'reply/help-withdraw.php';
	}
	if($cb_data == "/help_medsos") {
		require_once 'reply/help-medsos.php';
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
	// Withdraw
	if($cb_data == "/withdraw") {
		require_once 'reply/withdraw.php';
	}
	if($cb_data == "/withdraw_wallet") {
		require_once 'reply/withdraw-wallet.php';
	}
	if($cb_data == "/withdraw_campaign") {
		require_once 'reply/withdraw-campaign.php';
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
	if($cb_data == "/campaign_topup") {
		require_once 'reply/campaign-topup-list.php';
	}
	if(strpos($cb_data, '/select_campaign_') === 0) {
		require_once 'reply/select-campaign.php';
	}
	if(strpos($cb_data, '/edit_campaign_detail_') === 0) {
		require_once 'reply/edit-campaign-detail.php';
	}
	if(strpos($cb_data, '/pause_campaign_') === 0) {
		require_once 'reply/pause-campaign.php';
	}
	if(strpos($cb_data, '/resume_campaign_') === 0) {
		require_once 'reply/resume-campaign.php';
	}
	if(strpos($cb_data, '/add_campaign_balance_') === 0) {
		require_once 'reply/add-campaign-balance.php';
	}
	if(strpos($cb_data, '/edit_campaign_title_') === 0) {
		require_once 'reply/edit-campaign-title.php';
	}
	if(strpos($cb_data, '/edit_campaign_target_') === 0) {
		require_once 'reply/edit-campaign-target.php';
	}

	// delete campaign
	if(strpos($cb_data, '/delete_campaign_confirm_') === 0) {
		require_once 'reply/delete-campaign-confirm.php';
	} elseif (strpos($cb_data, '/delete_campaign_') === 0) {
		require_once 'reply/delete-campaign.php';
	}

	// tugas
	if($cb_data == "/task") {
		require_once 'reply/task.php';
	}
	if($cb_data == "/task_refresh") {
		require_once 'reply/task.php';
	}
	if(strpos($cb_data, '/take_task_') === 0) {
		require_once 'reply/take-task.php';
	}
	if(strpos($cb_data, '/cancel_task_') === 0) {
		require_once 'reply/cancel-task.php';
	}
}

// Trace keyboard structure
logMessage('keyboard', [
    'chat_id' => $chat_id,
    'cb_data' => $cb_data,
    'message' => $message,
    'user_position' => [
        'menu' => $menu ?? 'unknown',
        'submenu' => $submenu ?? ''
    ],
    'keyboard_structure' => isset($keyboard) ? $keyboard : 'not_set'
]);

?>
