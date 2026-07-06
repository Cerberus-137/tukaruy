-- Top-Up History Table
-- Add this to your existing database

-- Create top-up history table (records successful credit purchases)
CREATE TABLE IF NOT EXISTS topup_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    payment_id INT NOT NULL,
    payment_method ENUM('qrispay', 'saweria') NOT NULL,
    credits_purchased INT NOT NULL,
    bonus_credits INT DEFAULT 0,
    total_credits INT NOT NULL,
    amount_paid INT NOT NULL,
    payment_reference VARCHAR(255) DEFAULT NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_payment_id (payment_id),
    INDEX idx_purchased_at (purchased_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
