-- Truncate all tables - remove all data but keep structure
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE smm_referrals;
TRUNCATE TABLE smm_referral_codes;
TRUNCATE TABLE smm_audit_logs;
TRUNCATE TABLE smm_withdrawals;
TRUNCATE TABLE smm_deposits;
TRUNCATE TABLE smm_task_proofs;
TRUNCATE TABLE smm_tasks;
TRUNCATE TABLE smm_campaigns;
TRUNCATE TABLE smm_social_accounts;
TRUNCATE TABLE smm_wallet_transactions;
TRUNCATE TABLE smm_wallets;
TRUNCATE TABLE smm_admins;
TRUNCATE TABLE smm_settings;
TRUNCATE TABLE smm_users;

SET FOREIGN_KEY_CHECKS = 1;