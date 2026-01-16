<?php
// Admin Permission Helper Functions
// Procedural helper for managing admin permissions

/**
 * Check if user is an admin
 * @param int $chat_id Telegram chat ID
 * @return bool True if user is admin
 */
function isAdmin($chat_id) {
    $admin = db_read('smm_admins', ['chatid' => $chat_id]);
    return !empty($admin);
}

/**
 * Check if admin has super admin privileges (all permissions)
 * @param int $chat_id Telegram chat ID
 * @return bool True if admin has 'all' permission
 */
function isSuperAdmin($chat_id) {
    $admin = db_read('smm_admins', ['chatid' => $chat_id]);

    if (empty($admin)) {
        return false;
    }

    $permissions = json_decode($admin[0]['permissions'], true);

    if (isset($permissions['all']) && $permissions['all'] === true) {
        return true;
    }

    return false;
}

/**
 * Check if admin has specific permission
 * Super admins bypass all permission checks
 * @param int $chat_id Telegram chat ID
 * @param string $permission Permission key to check
 * @return bool True if admin has permission
 */
function hasPermission($chat_id, $permission) {
    $admin = db_read('smm_admins', ['chatid' => $chat_id]);

    if (empty($admin)) {
        logMessage('permission', [
            'chat_id' => $chat_id,
            'permission' => $permission,
            'result' => 'not_admin'
        ], 'debug');
        return false;
    }

    $permissions = json_decode($admin[0]['permissions'], true);

    if (is_string($permissions)) {
        $permissions = json_decode($permissions, true);
    }

    if (isset($permissions['all']) && $permissions['all'] === true) {
        logMessage('permission', [
            'chat_id' => $chat_id,
            'permission' => $permission,
            'result' => 'super_admin_bypass'
        ], 'debug');
        return true;
    }

    $hasPermission = isset($permissions[$permission]) && $permissions[$permission] === true;

    logMessage('permission', [
        'chat_id' => $chat_id,
        'permission' => $permission,
        'result' => $hasPermission ? 'allowed' : 'denied'
    ], 'debug');

    return $hasPermission;
}

/**
 * Get all permissions for an admin as associative array
 * @param int $chat_id Telegram chat ID
 * @return array|null Permissions array or null if not admin
 */
function getAdminPermissions($chat_id) {
    $admin = db_read('smm_admins', ['chatid' => $chat_id]);

    if (empty($admin)) {
        return null;
    }

    $permissions = json_decode($admin[0]['permissions'], true);

    if (is_string($permissions)) {
        $permissions = json_decode($permissions, true);
    }

    return $permissions;
}

/**
 * Check if admin has any of the specified permissions
 * @param int $chat_id Telegram chat ID
 * @param array $permissions Array of permission keys to check
 * @return bool True if admin has at least one permission
 */
function hasAnyPermission($chat_id, $permissions) {
    foreach ($permissions as $permission) {
        if (hasPermission($chat_id, $permission)) {
            return true;
        }
    }
    return false;
}

/**
 * Check if admin has all of the specified permissions
 * @param int $chat_id Telegram chat ID
 * @param array $permissions Array of permission keys to check
 * @return bool True if admin has all permissions
 */
function hasAllPermissions($chat_id, $permissions) {
    foreach ($permissions as $permission) {
        if (!hasPermission($chat_id, $permission)) {
            return false;
        }
    }
    return true;
}

/**
 * Send unauthorized access message
 * @param object $bot TelegramBot instance
 * @param int $chat_id Telegram chat ID
 * @param string $feature Feature name for error message
 */
function adminNoPermission($bot, $chat_id, $feature) {
    $message = "❌ <b>Akses Ditolak</b>\n\n"
        . "Anda tidak memiliki izin untuk mengakses fitur: <b>$feature</b>\n\n"
        . "Silakan hubungi administrator jika ini kesalahan.";

    $bot->sendMessage($chat_id, $message, null, 'HTML');
}

/**
 * Check permission and send error if denied
 * Use this for early return in callback handlers
 * @param object $bot TelegramBot instance
 * @param int $chat_id Telegram chat ID
 * @param string $permission Permission key required
 * @param string $feature Feature name for error message
 * @return bool True if has permission, false if denied (and message sent)
 */
function checkPermissionOrFail($bot, $chat_id, $permission, $feature) {
    if (!hasPermission($chat_id, $permission)) {
        adminNoPermission($bot, $chat_id, $feature);
        return false;
    }
    return true;
}

/**
 * Get admin menu buttons based on permissions
 * @param int $chat_id Telegram chat ID
 * @return array|null Menu keyboard array or null if not admin
 */
function getAdminMenu($chat_id) {
    if (!isAdmin($chat_id)) {
        return null;
    }

    $keyboard = [];

    // Task verification - requires task_verify permission
    if (hasPermission($chat_id, 'task_verify')) {
        $keyboard[] = [
            ['text' => '✅ Verifikasi Tugas', 'callback_data' => 'verifikasi']
        ];
    }

    // Campaign verification - requires campaign_verify permission
    if (hasPermission($chat_id, 'campaign_verify')) {
        $keyboard[] = [
            ['text' => '📢 Verifikasi Campaign', 'callback_data' => 'campaign_admin']
        ];
    }

    // Deposit verification - requires deposit_verify permission
    if (hasPermission($chat_id, 'deposit_verify')) {
        $keyboard[] = [
            ['text' => '💰 Cek Deposit', 'callback_data' => 'deposit_admin']
        ];
    }

    // Withdraw verification - requires withdraw_verify permission
    if (hasPermission($chat_id, 'withdraw_verify')) {
        $keyboard[] = [
            ['text' => '💸 Proses Withdraw', 'callback_data' => 'withdraw_admin']
        ];
    }

    // Settings - requires any settings permission or super admin
    $settingsPermissions = ['settings_payment', 'settings_withdraw', 'settings_campaign'];
    $hasSettingsAccess = hasAnyPermission($chat_id, $settingsPermissions) || isSuperAdmin($chat_id);

    if ($hasSettingsAccess) {
        $keyboard[] = [
            ['text' => '⚙️ Settings', 'callback_data' => 'settings']
        ];
    }

    return $keyboard;
}

/**
 * Get list of available admin features (for menu display)
 * @param int $chat_id Telegram chat ID
 * @return array List of feature labels and their callback data
 */
function getAdminFeatures($chat_id) {
    $features = [];

    if (hasPermission($chat_id, 'task_verify')) {
        $features[] = [
            'label' => 'Verifikasi Tugas',
            'callback' => 'verifikasi',
            'icon' => '✅'
        ];
    }

    if (hasPermission($chat_id, 'campaign_verify')) {
        $features[] = [
            'label' => 'Verifikasi Campaign',
            'callback' => 'campaign_admin',
            'icon' => '📢'
        ];
    }

    if (hasPermission($chat_id, 'deposit_verify')) {
        $features[] = [
            'label' => 'Cek Deposit',
            'callback' => 'deposit_admin',
            'icon' => '💰'
        ];
    }

    if (hasPermission($chat_id, 'withdraw_verify')) {
        $features[] = [
            'label' => 'Proses Withdraw',
            'callback' => 'withdraw_admin',
            'icon' => '💸'
        ];
    }

    $settingsPermissions = ['settings_payment', 'settings_withdraw', 'settings_campaign'];
    if (hasAnyPermission($chat_id, $settingsPermissions) || isSuperAdmin($chat_id)) {
        $features[] = [
            'label' => 'Settings',
            'callback' => 'settings',
            'icon' => '⚙️'
        ];
    }

    return $features;
}

/**
 * Log admin action for audit trail
 * @param int $admin_chat_id Admin's Telegram chat ID
 * @param string $action Action performed
 * @param string $table_name Database table affected
 * @param int|null $record_id Record ID affected
 * @param array|null $old_data Previous data state
 * @param array|null $new_data New data state
 */
function logAdminAction($admin_chat_id, $action, $table_name = null, $record_id = null, $old_data = null, $new_data = null) {
    $admin = db_read('smm_admins', ['chatid' => $admin_chat_id]);
    $admin_id = !empty($admin) ? $admin[0]['id'] : null;

    $audit_data = [
        'admin_id' => $admin_id,
        'action' => $action,
        'table_name' => $table_name,
        'record_id' => $record_id,
        'old_data' => $old_data,
        'new_data' => $new_data,
        'description' => "Admin $admin_chat_id performed action: $action"
    ];

    logMessage('admin_action', $audit_data, 'info');

    db_create('smm_audit_logs', [
        'admin_id' => $admin_id,
        'action' => $action,
        'table_name' => $table_name,
        'record_id' => $record_id,
        'old_data' => $old_data ? json_encode($old_data) : null,
        'new_data' => $new_data ? json_encode($new_data) : null,
        'description' => $audit_data['description']
    ]);
}

/**
 * Validate permission structure for admin record
 * @param array $permissions Permission array to validate
 * @return array Validated and normalized permissions
 */
function validatePermissions($permissions) {
    $defaultPermissions = [
        'all' => false,
        'task_verify' => false,
        'deposit_verify' => false,
        'withdraw_verify' => false,
        'campaign_verify' => false,
        'settings_payment' => false,
        'settings_withdraw' => false,
        'settings_campaign' => false,
        'admin_manage' => false
    ];

    if (is_string($permissions)) {
        $permissions = json_decode($permissions, true);
    }

    if (!is_array($permissions)) {
        return $defaultPermissions;
    }

    return array_merge($defaultPermissions, $permissions);
}
?>
