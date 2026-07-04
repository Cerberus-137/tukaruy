-- Database schema for Tukeruy
-- Run this SQL in your MySQL database

CREATE DATABASE IF NOT EXISTS tukeruy;
USE tukeruy;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    company VARCHAR(255) DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    tickets INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    payment_method ENUM('qrispay', 'saweria') DEFAULT 'qrispay',
    qris_id VARCHAR(255) DEFAULT NULL,
    external_id VARCHAR(255) DEFAULT NULL, -- For Saweria donation ID
    amount INT NOT NULL,
    tickets INT NOT NULL,
    status ENUM('pending', 'paid', 'expired', 'cancelled') DEFAULT 'pending',
    payment_reference VARCHAR(255) DEFAULT NULL,
    qris_image_url TEXT DEFAULT NULL,
    payment_url TEXT DEFAULT NULL, -- For Saweria payment URL
    expired_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_qris_id (qris_id),
    INDEX idx_external_id (external_id),
    INDEX idx_payment_method (payment_method),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ticket usage history
CREATE TABLE IF NOT EXISTS ticket_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tn_id VARCHAR(255) NOT NULL,
    tracking_number VARCHAR(255) NOT NULL,
    carrier VARCHAR(50) NOT NULL,
    tickets_used INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin settings
CREATE TABLE IF NOT EXISTS admin_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT DEFAULT NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO users (email, password, first_name, last_name, company, role, tickets) 
VALUES (
    'admin@tukaruy.online',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin',
    'Tukeruy',
    'Tukeruy Inc.',
    'admin',
    999999
);

-- Insert default settings
INSERT INTO admin_settings (setting_key, setting_value) VALUES
('qrispay_api_token', 'cki_PsO8fSC6e1ASeJq9AbTDpcjXjAk1VvvXxjbAl7MqxMr9fEi7'),
('saweria_api_token', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJjdXJyZW5jeSI6IklEUiIsImlkIjoiNWNjODc1NTItMjUwMC00ZmE5LWFhZmYtYWY1MmM4MTZiZTBhIiwiZW1haWwiOiJtdWhhbW1hZGlsaGFtMTM3MTNAZ21haWwuY29tIiwidXNlcm5hbWUiOiJtaWxoYW02OSIsInRpZXJfa2V5IjoiQkFTSUMiLCJpc3MiOiJzYXdlcmlhLWxvZ2luIiwiaWF0IjoxNzgzMTYxMTc5LCJleHAiOjE3ODM0MjAzNzksImp0aSI6IjIzNTEyMzE5LTk4NjUtNDg2Mi1hMjQ1LWVhOGRjZTM0NTdhZSJ9.aiB1H9S5yo98OzJnx2IPKUch2FiMyq9TU5zVMJAcgdo'),
('tracktaco_api_key', 'tt_live_T5w7dupesqnPFQprpV6ozAdE40LKird_BZkrF4TL7dk'),
('site_name', 'Tukeruy'),
('site_url', 'https://tukaruy.online'),
('base_price_per_credit', '50000'),
('payment_methods_qrispay', '1'),
('payment_methods_saweria', '1');

-- Update existing payments table structure (if exists)
ALTER TABLE payments 
ADD COLUMN payment_method ENUM('qrispay', 'saweria') DEFAULT 'qrispay' AFTER user_id,
ADD COLUMN external_id VARCHAR(255) DEFAULT NULL AFTER qris_id,
ADD COLUMN payment_url TEXT DEFAULT NULL AFTER qris_image_url,
ADD INDEX idx_external_id (external_id),
ADD INDEX idx_payment_method (payment_method);

-- Make qris_id nullable for Saweria payments
ALTER TABLE payments MODIFY COLUMN qris_id VARCHAR(255) DEFAULT NULL;
