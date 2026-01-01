-- Reset Database Script
-- This will drop and recreate all tables

-- Drop all tables
DROP TABLE IF EXISTS smm_audit_logs;
DROP TABLE IF EXISTS smm_withdrawals;
DROP TABLE IF EXISTS smm_deposits;
DROP TABLE IF EXISTS smm_task_proofs;
DROP TABLE IF EXISTS smm_tasks;
DROP TABLE IF EXISTS smm_campaigns;
DROP TABLE IF EXISTS smm_social_accounts;
DROP TABLE IF EXISTS smm_wallet_transactions;
DROP TABLE IF EXISTS smm_wallets;
DROP TABLE IF EXISTS smm_admins;
DROP TABLE IF EXISTS smm_users;

-- Wait 2 seconds (commented for SQL, use in application)
-- SELECT SLEEP(2);

-- Recreate all tables (copy from schema.sql)
-- Users table - menyimpan data user dengan role
CREATE TABLE IF NOT EXISTS smm_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chatid BIGINT UNIQUE NOT NULL,
    username VARCHAR(255),
    full_name VARCHAR(255),
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'suspended') DEFAULT 'active',
    menu VARCHAR(50) DEFAULT 'main',
    submenu VARCHAR(50) DEFAULT '',
    msg_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admins table - data khusus untuk admin
CREATE TABLE IF NOT EXISTS smm_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chatid BIGINT UNIQUE NOT NULL,
    username VARCHAR(255),
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    permissions JSON,
    msg_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Wallets table - dompet internal untuk setiap user
CREATE TABLE IF NOT EXISTS smm_wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES smm_users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_wallet (user_id)
);

-- Wallet transactions table - semua transaksi saldo
CREATE TABLE IF NOT EXISTS smm_wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_id INT NOT NULL,
    type ENUM('deposit', 'task_reward', 'withdraw', 'adjustment') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    balance_before DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) NOT NULL,
    description VARCHAR(500),
    reference_id INT,
    status ENUM('pending', 'approved', 'rejected', 'canceled') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wallet_id) REFERENCES smm_wallets(id) ON DELETE CASCADE
);

-- Campaigns table - campaign yang dibuat oleh client
CREATE TABLE IF NOT EXISTS smm_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    type ENUM('view', 'like', 'comment', 'share', 'follow') NOT NULL,
    link_target TEXT NOT NULL,
    price_per_task DECIMAL(10,2) NOT NULL,
    target_total INT NOT NULL,
    completed_count INT DEFAULT 0,
    status ENUM('active', 'paused', 'completed', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES smm_users(id) ON DELETE CASCADE
);

-- Tasks table - tugas individual yang bisa diambil worker
CREATE TABLE IF NOT EXISTS smm_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    worker_id INT NULL,
    status ENUM('available', 'taken', 'pending_review', 'approved', 'rejected') DEFAULT 'available',
    taken_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES smm_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (worker_id) REFERENCES smm_users(id) ON DELETE SET NULL
);

-- Task proofs table - bukti screenshot dari worker
CREATE TABLE IF NOT EXISTS smm_task_proofs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    proof_image_path VARCHAR(500) NOT NULL,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES smm_tasks(id) ON DELETE CASCADE
);

-- Deposits table - permintaan top-up dari client
CREATE TABLE IF NOT EXISTS smm_deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    proof_image_id VARCHAR(500),
    status ENUM('pending', 'approved', 'rejected', 'canceled') DEFAULT 'pending',
    admin_id INT NULL,
    admin_notes TEXT,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES smm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES smm_users(id) ON DELETE SET NULL
);

-- Withdrawals table - permintaan withdraw dari worker
CREATE TABLE IF NOT EXISTS smm_withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    destination_account VARCHAR(255) NOT NULL,
    fee DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'approved', 'rejected', 'canceled') DEFAULT 'pending',
    admin_id INT NULL,
    admin_notes TEXT,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES smm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES smm_users(id) ON DELETE SET NULL
);

-- Audit logs table - log semua tindakan admin
CREATE TABLE IF NOT EXISTS smm_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_data JSON,
    new_data JSON,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES smm_users(id) ON DELETE CASCADE
);

-- Social media accounts table - menyimpan akun media social milik user
CREATE TABLE IF NOT EXISTS smm_social_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    platform ENUM('instagram', 'tiktok', 'youtube', 'twitter', 'facebook') NOT NULL,
    username VARCHAR(255) NOT NULL,
    account_url TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES smm_users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_platform_username (user_id, platform, username)
);

-- Indexes for performance optimization
CREATE INDEX idx_users_telegram_id ON smm_users(chatid);
CREATE INDEX idx_users_role ON smm_users(role);
CREATE INDEX idx_users_msg_id ON smm_users(msg_id);
CREATE INDEX idx_admins_chatid ON smm_admins(chatid);
CREATE INDEX idx_admins_status ON smm_admins(status);
CREATE INDEX idx_admins_msg_id ON smm_admins(msg_id);
CREATE INDEX idx_wallets_user_id ON smm_wallets(user_id);
CREATE INDEX idx_wallet_transactions_wallet_id ON smm_wallet_transactions(wallet_id);
CREATE INDEX idx_wallet_transactions_type ON smm_wallet_transactions(type);
CREATE INDEX idx_campaigns_client_id ON smm_campaigns(client_id);
CREATE INDEX idx_campaigns_status ON smm_campaigns(status);
CREATE INDEX idx_tasks_campaign_id ON smm_tasks(campaign_id);
CREATE INDEX idx_tasks_worker_id ON smm_tasks(worker_id);
CREATE INDEX idx_tasks_status ON smm_tasks(status);
CREATE INDEX idx_task_proofs_task_id ON smm_task_proofs(task_id);
CREATE INDEX idx_deposits_user_id ON smm_deposits(user_id);
CREATE INDEX idx_deposits_status ON smm_deposits(status);
CREATE INDEX idx_withdrawals_user_id ON smm_withdrawals(user_id);
CREATE INDEX idx_withdrawals_status ON smm_withdrawals(status);
CREATE INDEX idx_audit_logs_admin_id ON smm_audit_logs(admin_id);
CREATE INDEX idx_audit_logs_created_at ON smm_audit_logs(created_at);
CREATE INDEX idx_social_accounts_user_id ON smm_social_accounts(user_id);
CREATE INDEX idx_social_accounts_platform ON smm_social_accounts(platform);
CREATE INDEX idx_social_accounts_status ON smm_social_accounts(status);