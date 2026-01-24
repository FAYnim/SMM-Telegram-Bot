-- ============================================
-- ADD REFERRAL TABLES TO EXISTING DATABASE
-- Run this script to add referral system to existing database
-- ============================================

-- 1. Tambahkan settings referral ke tabel smm_settings
INSERT INTO smm_settings (category, setting_key, setting_value, description) VALUES
    ('referral', 'mandatory', 'no', 'Apakah kode referral wajib untuk user baru (yes/no)'),
    ('referral', 'reward_amount', '5000', 'Jumlah reward referral dalam Rupiah')
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    description = VALUES(description);

-- 2. Buat tabel smm_referral_codes
CREATE TABLE IF NOT EXISTS smm_referral_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(12) NOT NULL,
    is_custom TINYINT(1) DEFAULT 0 COMMENT '0 = auto-generated, 1 = custom',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_code (code),
    INDEX idx_user_id (user_id),
    INDEX idx_code (code),
    FOREIGN KEY (user_id) REFERENCES smm_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Buat tabel smm_referrals
CREATE TABLE IF NOT EXISTS smm_referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referrer_id INT NOT NULL COMMENT 'User yang membagikan kode referral',
    referred_user_id INT NOT NULL COMMENT 'User yang menggunakan kode referral',
    referral_code VARCHAR(12) NOT NULL COMMENT 'Kode referral yang digunakan',
    reward_amount INT NOT NULL COMMENT 'Jumlah reward dalam Rupiah',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_referrer_id (referrer_id),
    INDEX idx_referred_user_id (referred_user_id),
    INDEX idx_referral_code (referral_code),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (referrer_id) REFERENCES smm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_user_id) REFERENCES smm_users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_referred_user (referred_user_id) COMMENT 'Setiap user hanya bisa menggunakan referral sekali'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Cek tabel yang baru dibuat
SHOW TABLES LIKE 'smm_referral%';

-- Cek struktur tabel smm_referral_codes
DESCRIBE smm_referral_codes;

-- Cek struktur tabel smm_referrals
DESCRIBE smm_referrals;

-- Cek settings referral
SELECT * FROM smm_settings WHERE category = 'referral';

-- ============================================
-- SUCCESS MESSAGE
-- ============================================
SELECT 'Referral tables successfully added!' AS status;
