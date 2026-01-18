<?php
require_once 'TelegramBot.php';
require_once 'db.php';
require_once 'config/config.php';
require_once 'helpers/index-helper.php';
require_once 'helpers/admin-permission.php';

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

// ============================================
// MESSAGE HANDLERS (Non-callback)
// ============================================
if(!$cb_data){
	// Command: /start
	if ($message == "/start") {
		require_once 'reply/start.php';
	}
	// ============================================
	// ADMIN SUBMENU HANDLERS (State-based)
	// ============================================
	elseif(isAdmin($chat_id) && strpos($submenu, 'topup_approve_') === 0 && hasPermission($chat_id, 'deposit_verify')) {
		require_once 'reply/admin-topup-approve.php';
	}
	elseif(isAdmin($chat_id) && strpos($submenu, 'topup_reject_') === 0 && hasPermission($chat_id, 'deposit_verify')) {
		require_once 'reply/admin-topup-reject.php';
	}
	elseif(isAdmin($chat_id) && strpos($submenu, 'task_approve_') === 0 && hasPermission($chat_id, 'task_verify')) {
		require_once 'reply/admin-task-approve.php';
	}
	elseif(isAdmin($chat_id) && strpos($submenu, 'task_reject_') === 0 && hasPermission($chat_id, 'task_verify')) {
		require_once 'reply/admin-task-reject.php';
	}
	elseif(isAdmin($chat_id) && strpos($submenu, 'withdraw_approve_') === 0 && hasPermission($chat_id, 'withdraw_verify')) {
		require_once 'reply/admin-withdraw-approve.php';
	}
	elseif(isAdmin($chat_id) && strpos($submenu, 'withdraw_reject_') === 0 && hasPermission($chat_id, 'withdraw_verify')) {
		require_once 'reply/admin-withdraw-reject.php';
	}
	elseif(isAdmin($chat_id) && strpos($submenu, 'campaign_reject_') === 0 && hasPermission($chat_id, 'campaign_verify')) {
		require_once 'reply/admin-campaign-reject.php';
	}
	// ============================================
	// USER MENU HANDLERS (State-based)
	// ============================================
	// Social Media Account Management
	elseif ($menu == 'add_instagram' || $menu == 'add_tiktok') {
		require_once 'reply/tambah-medsos.php';
	}
	elseif ($menu == 'edit_username') {
		require_once 'reply/edit-username.php';
	}
	// Settings
	elseif ($menu == 'settings_edit_dana' || $menu == 'settings_edit_shopeepay') {
		require_once 'reply/settings-process-payment.php';
	}
	elseif ($menu == 'settings_edit_min_withdraw' || $menu == 'settings_edit_admin_fee') {
		require_once 'reply/settings-process-withdraw.php';
	}
	elseif ($menu == 'settings_edit_min_price_per_task') {
		require_once 'reply/settings-process-campaign.php';
	}
	// Withdraw
	elseif ($menu == 'withdraw_amount') {
		require_once 'reply/withdraw-amount.php';
	}
	elseif ($menu == 'withdraw_destination') {
		require_once 'reply/withdraw-destination.php';
	}
	elseif ($menu == 'withdraw_campaign_amount') {
		require_once 'reply/withdraw-campaign-amount.php';
	}
	// Campaign Creation
	elseif ($menu == 'buat_campaign_type') {
		require_once 'reply/buat-campaign-judul.php';
	}
	elseif ($menu == 'buat_campaign_akun') {
		require_once 'reply/buat-campaign-akun.php';
	}
	elseif ($menu == 'buat_campaign_link') {
		require_once 'reply/buat-campaign-link.php';
	}
	elseif ($menu == 'buat_campaign_price') {
		require_once 'reply/buat-campaign-price.php';
	}
	
	elseif ($menu == 'buat_campaign_reward') { // Catatan, kemungkinan nggak dipakai
		require_once 'reply/buat-campaign-reward.php';
	}

	elseif ($menu == 'buat_campaign_target') {
		require_once 'reply/buat-campaign-target.php';
	}
	// Campaign Editing
	elseif ($menu == 'edit_campaign_title') {
		require_once 'reply/process-edit-campaign-title.php';
	}
	elseif ($menu == 'edit_campaign_target') {
		require_once 'reply/process-edit-campaign-target.php';
	}
	elseif ($menu == 'add_campaign_balance') {
		require_once 'reply/process-add-campaign-balance.php';
	}
	// ============================================
	// FILE UPLOAD HANDLERS
	// ============================================
	elseif ($photo) {
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
		} elseif($menu == "upload_proof") {
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
	elseif ($document) {
		// DEBUGGING ONLY:
/*		$file_id = $bot->getDocumentFileId();
		$reply = "📄 Dokumen terdeteksi!\nFile ID: " . $file_id;
		if ($caption) {
			$reply .= "\nCaption: " . $caption;
		}
		$bot->sendMessage($chat_id, $reply);*/
	}
} else {
	// ============================================
	// CALLBACK HANDLERS (Pattern ordered by specificity)
	// ============================================
	
	// ============================================
	// ADMIN VERIFICATION HANDLERS
	// ============================================
	if($cb_data == "verifikasi" && hasPermission($chat_id, 'task_verify')) {
		require_once 'reply/task-admin.php';
	}
	elseif($cb_data == "campaign_admin" && hasPermission($chat_id, 'campaign_verify')) {
		require_once 'reply/campaign-admin.php';
	}
	elseif($cb_data == "deposit_admin" && hasPermission($chat_id, 'deposit_verify')) {
		require_once 'reply/deposit-admin.php';
	}
	elseif($cb_data == "withdraw_admin" && hasPermission($chat_id, 'withdraw_verify')) {
		require_once 'reply/withdraw-admin.php';
	}
	// ============================================
	// ADMIN ACTION HANDLERS (Approve/Reject)
	// ============================================
	// Topup Actions
	elseif(strpos($cb_data, 'admin_approve_topup_') === 0 && hasPermission($chat_id, 'deposit_verify')) {
		require_once 'reply/admin-topup.php';
	}
	elseif(strpos($cb_data, 'admin_reject_topup_') === 0 && hasPermission($chat_id, 'deposit_verify')) {
		require_once 'reply/admin-topup.php';
	}
	// Withdraw Actions
	elseif(strpos($cb_data, 'admin_approve_withdraw_') === 0 && hasPermission($chat_id, 'withdraw_verify')) {
		require_once 'reply/admin-withdraw.php';
	}
	elseif(strpos($cb_data, 'admin_reject_withdraw_') === 0 && hasPermission($chat_id, 'withdraw_verify')) {
		require_once 'reply/admin-withdraw.php';
	}
	// Task Actions
	elseif(strpos($cb_data, 'admin_approve_task_') === 0 && hasPermission($chat_id, 'task_verify')) {
		require_once 'reply/admin-task.php';
	}
	elseif(strpos($cb_data, 'admin_reject_task_') === 0 && hasPermission($chat_id, 'task_verify')) {
		require_once 'reply/admin-task.php';
	}
	// Campaign Actions
	elseif(strpos($cb_data, 'admin_approve_campaign_') === 0 && hasPermission($chat_id, 'campaign_verify')) {
		require_once 'reply/admin-campaign.php';
	}
	elseif(strpos($cb_data, 'admin_reject_campaign_') === 0 && hasPermission($chat_id, 'campaign_verify')) {
		require_once 'reply/admin-campaign.php';
	}
	// ============================================
	// SETTINGS HANDLERS
	// ============================================
	elseif($cb_data == "settings" && hasAnyPermission($chat_id, ['settings_payment', 'settings_withdraw', 'settings_campaign'])) {
		require_once 'reply/settings.php';
	}
	elseif($cb_data == "settings_payment" && hasPermission($chat_id, 'settings_payment')) {
		require_once 'reply/settings-payment.php';
	}
	elseif($cb_data == "settings_withdraw" && hasPermission($chat_id, 'settings_withdraw')) {
		require_once 'reply/settings-withdraw.php';
	}
	elseif($cb_data == "settings_campaign" && hasPermission($chat_id, 'settings_campaign')) {
		require_once 'reply/settings-campaign.php';
	}
	elseif($cb_data == "settings_edit_dana" || $cb_data == "settings_edit_shopeepay") {
		require_once 'reply/settings-edit-payment.php';
	}
	elseif($cb_data == "settings_edit_min_withdraw" || $cb_data == "settings_edit_admin_fee") {
		require_once 'reply/settings-edit-withdraw.php';
	}
	elseif($cb_data == "settings_edit_min_price_per_task") {
		require_once 'reply/settings-edit-campaign.php';
	}
	// ============================================
	// UTILITY HANDLERS
	// ============================================
	elseif($cb_data == "close_notif") {
		require_once 'reply/close-notif.php';
	}
	// ============================================
	// USER NAVIGATION HANDLERS
	// ============================================
	elseif($cb_data == "/start") {
		require_once 'reply/start.php';
	}
	// Help Menu
	elseif($cb_data == "/help") {
		require_once 'reply/help.php';
	}
	elseif($cb_data == "/help_about") {
		require_once 'reply/help-about.php';
	}
	elseif($cb_data == "/help_campaign") {
		require_once 'reply/help-campaign.php';
	}
	elseif($cb_data == "/help_task") {
		require_once 'reply/help-task.php';
	}
	elseif($cb_data == "/help_saldo") {
		require_once 'reply/help-saldo.php';
	}
	elseif($cb_data == "/help_withdraw") {
		require_once 'reply/help-withdraw.php';
	}
	elseif($cb_data == "/help_medsos") {
		require_once 'reply/help-medsos.php';
	}
	elseif($cb_data == "/social") {
		require_once 'reply/social.php';
	}
	// ============================================
	// WALLET OPERATIONS
	// ============================================
	elseif($cb_data == "/cek_saldo") {
		require_once 'reply/cek-saldo.php';
	}
	elseif($cb_data == "/riwayat_topup") {
		require_once 'reply/riwayat-topup.php';
	}
	// Topup (exact match before pattern match)
	elseif($cb_data == "/topup") {
		require_once 'reply/topup.php';
	}
	elseif($cb_data == "/konfirmasi_topup") {
		require_once 'reply/konfirmasi-topup.php';
	}
	elseif(strpos($cb_data, '/topup_') === 0) {
		require_once 'reply/opsi-topup.php';
	}
	// Withdraw
	elseif($cb_data == "/withdraw") {
		require_once 'reply/withdraw.php';
	}
	elseif($cb_data == "/withdraw_wallet") {
		require_once 'reply/withdraw-wallet.php';
	}
	elseif($cb_data == "/withdraw_campaign") {
		require_once 'reply/withdraw-campaign.php';
	}
	// ============================================
	// SOCIAL MEDIA ACCOUNT MANAGEMENT
	// ============================================
	elseif($cb_data == "/tambah_medsos") {
		require_once 'reply/tambah-medsos.php';
	}
	elseif($cb_data == "/add_instagram") {
		require_once 'reply/tambah-medsos.php';
	}
	elseif($cb_data == "/add_tiktok") {
		require_once 'reply/tambah-medsos.php';
	}
	elseif($cb_data == "/edit_medsos") {
		require_once 'reply/edit-medsos.php';
	}
	elseif(strpos($cb_data, '/edit_account_') === 0) {
		require_once 'reply/edit-medsos.php';
	}
	elseif(strpos($cb_data, '/delete_account_') === 0) {
		require_once 'reply/delete-medsos.php';
	}
	elseif(strpos($cb_data, '/confirm_delete_') === 0) {
		require_once 'reply/delete-medsos.php';
	}
	elseif(strpos($cb_data, '/edit_username_') === 0) {
		require_once 'reply/edit-username.php';
	}
	// ============================================
	// CAMPAIGN MANAGEMENT
	// ============================================
	elseif($cb_data == "/cek_campaign") {
		require_once 'reply/cek-campaign.php';
	}
	// Campaign Creation (exact match before pattern)
	elseif($cb_data == "/buat_campaign") {
		require_once 'reply/buat-campaign.php';
	}
	elseif($cb_data == "/simpan_campaign") {
		require_once 'reply/buat-campaign-simpan.php';
	}
	elseif(strpos($cb_data, '/buat_campaign_') === 0) {
		require_once 'reply/buat-campaign-type.php';
	}
	elseif(strpos($cb_data, '/select_account_') === 0) {
		require_once 'reply/buat-campaign-select-akun.php';
	}
	// Campaign Editing (exact matches before patterns)
	elseif($cb_data == "/edit_campaign") {
		require_once 'reply/edit-campaign.php';
	}
	elseif($cb_data == "/campaign_topup") {
		require_once 'reply/campaign-topup-list.php';
	}
	elseif(strpos($cb_data, '/select_campaign_') === 0) {
		require_once 'reply/select-campaign.php';
	}
	elseif(strpos($cb_data, '/edit_campaign_detail_') === 0) {
		require_once 'reply/edit-campaign-detail.php';
	}
	elseif(strpos($cb_data, '/edit_campaign_title_') === 0) {
		require_once 'reply/edit-campaign-title.php';
	}
	elseif(strpos($cb_data, '/edit_campaign_target_') === 0) {
		require_once 'reply/edit-campaign-target.php';
	}
	elseif(strpos($cb_data, '/pause_campaign_') === 0) {
		require_once 'reply/pause-campaign.php';
	}
	elseif(strpos($cb_data, '/resume_campaign_') === 0) {
		require_once 'reply/resume-campaign.php';
	}
	elseif(strpos($cb_data, '/add_campaign_balance_') === 0) {
		require_once 'reply/add-campaign-balance.php';
	}
	// Campaign Deletion (specific pattern before general)
	elseif(strpos($cb_data, '/delete_campaign_confirm_') === 0) {
		require_once 'reply/delete-campaign-confirm.php';
	}
	elseif(strpos($cb_data, '/delete_campaign_') === 0) {
		require_once 'reply/delete-campaign.php';
	}
	// ============================================
	// TASK MANAGEMENT
	// ============================================
	elseif($cb_data == "/task") {
		require_once 'reply/task.php';
	}
	elseif($cb_data == "/task_refresh") {
		require_once 'reply/task.php';
	}
	elseif(strpos($cb_data, '/take_task_') === 0) {
		require_once 'reply/take-task.php';
	}
	elseif(strpos($cb_data, '/cancel_task_') === 0) {
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
