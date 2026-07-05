-- Database schema for Tukeruy
-- Run this SQL in your MySQL database

CREATE DATABASE IF NOT EXISTS tukarkuy;
USE tukarkuy;

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

-- Ticket packages (with prices managed from admin panel)
CREATE TABLE IF NOT EXISTS ticket_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    credits INT NOT NULL UNIQUE,
    price INT NOT NULL,
    bonus INT DEFAULT 0,
    total_credits INT NOT NULL,
    discount_percentage INT DEFAULT 0,
    active BOOLEAN DEFAULT 1,
    order_index INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT DEFAULT NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_credits (credits),
    INDEX idx_active (active),
    INDEX idx_order (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment methods configuration
CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    method_name ENUM('qrispay', 'saweria') UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    enabled BOOLEAN DEFAULT 1,
    icon VARCHAR(100),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT DEFAULT NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_enabled (enabled)
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

-- Insert default ticket packages
INSERT INTO ticket_packages (credits, price, bonus, total_credits, discount_percentage, active, order_index) VALUES
(1, 50000, 0, 1, 0, 1, 1),
(3, 150000, 0, 3, 0, 1, 2),
(5, 250000, 0, 5, 0, 1, 3),
(9, 450000, 0, 9, 0, 1, 4),
(10, 500000, 1, 11, 10, 1, 5),
(25, 1250000, 5, 30, 15, 1, 6),
(50, 2500000, 10, 60, 20, 1, 7),
(100, 5000000, 25, 125, 25, 1, 8);

-- Insert default payment methods
INSERT INTO payment_methods (method_name, display_name, description, enabled, icon, sort_order) VALUES
('qrispay', 'QRIS Pay', 'Bayar dengan QRIS melalui e-wallet', 1, 'qr-code', 1),
('saweria', 'Saweria', 'Donasi melalui Saweria', 0, 'hand-holding-heart', 2);
-- Update existing payments table structure (if exists)
ALTER TABLE payments 
ADD COLUMN payment_method ENUM('qrispay', 'saweria') DEFAULT 'qrispay' AFTER user_id,
ADD COLUMN external_id VARCHAR(255) DEFAULT NULL AFTER qris_id,
ADD COLUMN payment_url TEXT DEFAULT NULL AFTER qris_image_url,
ADD INDEX idx_external_id (external_id),
ADD INDEX idx_payment_method (payment_method);

-- Make qris_id nullable for Saweria payments
ALTER TABLE payments MODIFY COLUMN qris_id VARCHAR(255) DEFAULT NULL;
