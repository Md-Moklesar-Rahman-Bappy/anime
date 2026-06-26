-- ============================================================
-- Anikoto – Create Super Admin User
-- Run this in phpMyAdmin SQL tab for the `anikoto` database
-- ============================================================

-- Remove existing admin user if any
DELETE FROM `users` WHERE `email` = 'admin@anikoto.test';

-- Create fresh super admin
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@anikoto.test', '$2y$10$wpJOpCr15M8LrvX.R.JLi.bGa5eLI4SqJn9kEt0g7yZZIjt5H52K.', 'super_admin');

-- ============================================================
-- CREDENTIALS:
--   Email:    admin@anikoto.test
--   Password: admin123
-- ============================================================
