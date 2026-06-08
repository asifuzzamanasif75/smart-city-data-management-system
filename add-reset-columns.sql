-- Add password reset columns to users table
-- Run this in phpMyAdmin

ALTER TABLE `users` 
ADD COLUMN `reset_code` VARCHAR(10) NULL DEFAULT NULL AFTER `password`,
ADD COLUMN `reset_code_expiry` DATETIME NULL DEFAULT NULL AFTER `reset_code`;

-- Verify columns were added
DESCRIBE users;