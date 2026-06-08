-- ============================================
-- Fix Demo User Passwords for Login
-- ============================================
-- Run this SQL in phpMyAdmin to fix the passwords
-- This updates the demo users to use bcrypt hashing

-- Update admin password (admin123)
UPDATE users 
SET password = '$2y$10$YourBcryptHashForAdmin123Here' 
WHERE username = 'admin';

-- Update user password (user123)
UPDATE users 
SET password = '$2y$10$YourBcryptHashForUser123Here' 
WHERE username = 'user';

-- ============================================
-- ALTERNATIVE: Delete and recreate users
-- ============================================
-- If the above doesn't work, run these commands instead:

-- Delete existing demo users
DELETE FROM users WHERE username IN ('admin', 'user');

-- NOTE: After running this, you need to register new users
-- OR use the PHP script below to create them properly