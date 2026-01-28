-- Migration: Add soft delete capability to social accounts
-- Created: 2026-01-28
-- Description: Menambahkan nilai 'disabled' ke kolom status untuk fitur soft delete

-- Modify status enum to include 'disabled' value
ALTER TABLE smm_social_accounts 
MODIFY status ENUM('active', 'inactive', 'disabled') DEFAULT 'active';

-- Verify the change
-- Run this to check: SHOW COLUMNS FROM smm_social_accounts LIKE 'status';
