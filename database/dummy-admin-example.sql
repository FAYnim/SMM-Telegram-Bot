-- Dummy Admin Data
-- Insert admin records for testing purposes with different permission levels

-- ============================================
-- SUPER ADMIN - All Permissions
-- ============================================
INSERT INTO smm_admins (
    chatid,
    username,
    first_name,
    last_name,
    status,
    permissions,
    msg_id
) VALUES (
    010101010,
    'superadmin',
    'Super',
    'Admin',
    'active',
    '{"all": true, "task_verify": true, "deposit_verify": true, "withdraw_verify": true, "campaign_verify": true, "settings_payment": true, "settings_withdraw": true, "settings_campaign": true, "admin_manage": true}',
    NULL
);

-- ============================================
-- TASK ADMIN - Verifikasi Tugas Worker Saja
-- ============================================
INSERT INTO smm_admins (
    chatid,
    username,
    first_name,
    last_name,
    status,
    permissions,
    msg_id
) VALUES (
    111111111,
    'taskadmin',
    'Task',
    'Admin',
    'active',
    '{"all": false, "task_verify": true, "deposit_verify": false, "withdraw_verify": false, "campaign_verify": false, "settings_payment": false, "settings_withdraw": false, "settings_campaign": false, "admin_manage": false}',
    NULL
);

-- ============================================
-- DEPOSIT ADMIN - Verifikasi Topup Saja
-- ============================================
INSERT INTO smm_admins (
    chatid,
    username,
    first_name,
    last_name,
    status,
    permissions,
    msg_id
) VALUES (
    222222222,
    'depositadmin',
    'Deposit',
    'Admin',
    'active',
    '{"all": false, "task_verify": false, "deposit_verify": true, "withdraw_verify": false, "campaign_verify": false, "settings_payment": false, "settings_withdraw": false, "settings_campaign": false, "admin_manage": false}',
    NULL
);

-- ============================================
-- WITHDRAW ADMIN - Proses Penarikan Saja
-- ============================================
INSERT INTO smm_admins (
    chatid,
    username,
    first_name,
    last_name,
    status,
    permissions,
    msg_id
) VALUES (
    333333333,
    'withdrawadmin',
    'Withdraw',
    'Admin',
    'active',
    '{"all": false, "task_verify": false, "deposit_verify": false, "withdraw_verify": true, "campaign_verify": false, "settings_payment": false, "settings_withdraw": false, "settings_campaign": false, "admin_manage": false}',
    NULL
);

-- ============================================
-- CAMPAIGN ADMIN - Verifikasi Campaign Saja
-- ============================================
INSERT INTO smm_admins (
    chatid,
    username,
    first_name,
    last_name,
    status,
    permissions,
    msg_id
) VALUES (
    444444444,
    'campaignadmin',
    'Campaign',
    'Admin',
    'active',
    '{"all": false, "task_verify": false, "deposit_verify": false, "withdraw_verify": false, "campaign_verify": true, "settings_payment": false, "settings_withdraw": false, "settings_campaign": false, "admin_manage": false}',
    NULL
);

-- ============================================
-- FINANCE ADMIN - Deposit + Withdraw
-- ============================================
INSERT INTO smm_admins (
    chatid,
    username,
    first_name,
    last_name,
    status,
    permissions,
    msg_id
) VALUES (
    555555555,
    'financeadmin',
    'Finance',
    'Admin',
    'active',
    '{"all": false, "task_verify": false, "deposit_verify": true, "withdraw_verify": true, "campaign_verify": false, "settings_payment": false, "settings_withdraw": false, "settings_campaign": false, "admin_manage": false}',
    NULL
);

-- ============================================
-- SETTINGS ADMIN - Kelola Pengaturan Saja
-- ============================================
INSERT INTO smm_admins (
    chatid,
    username,
    first_name,
    last_name,
    status,
    permissions,
    msg_id
) VALUES (
    666666666,
    'settingsadmin',
    'Settings',
    'Admin',
    'active',
    '{"all": false, "task_verify": false, "deposit_verify": false, "withdraw_verify": false, "campaign_verify": false, "settings_payment": true, "settings_withdraw": true, "settings_campaign": true, "admin_manage": false}',
    NULL
);

-- ============================================
-- EXAMPLE: Legacy format (backward compatible)
-- ============================================
INSERT INTO smm_admins (
    chatid,
    username,
    first_name,
    last_name,
    status,
    permissions,
    msg_id
) VALUES (
    123456789,
    'admin02',
    'FirstName',
    'LastName',
    'active',
    '{"all": true}',
    NULL
);
