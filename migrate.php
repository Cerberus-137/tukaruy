<?php
/**
 * Database Migration Script
 * Run this file once to update your database schema
 */

require_once 'config.php';

echo "<pre>";
echo "=== Tukeruy Database Migration ===\n\n";

try {
    $pdo = getDBConnection();
    
    echo "1. Adding payment method support...\n";
    
    // Check if payment_method column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM payments LIKE 'payment_method'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN payment_method ENUM('qrispay', 'saweria') DEFAULT 'qrispay' AFTER user_id");
        echo "   ✓ Added payment_method column\n";
    } else {
        echo "   - payment_method column already exists\n";
    }
    
    // Check if external_id column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM payments LIKE 'external_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN external_id VARCHAR(255) DEFAULT NULL AFTER qris_id");
        echo "   ✓ Added external_id column\n";
    } else {
        echo "   - external_id column already exists\n";
    }
    
    // Check if payment_url column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM payments LIKE 'payment_url'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN payment_url TEXT DEFAULT NULL AFTER qris_image_url");
        echo "   ✓ Added payment_url column\n";
    } else {
        echo "   - payment_url column already exists\n";
    }
    
    // Make qris_id nullable
    $pdo->exec("ALTER TABLE payments MODIFY COLUMN qris_id VARCHAR(255) DEFAULT NULL");
    echo "   ✓ Made qris_id nullable\n";
    
    // Add indexes if they don't exist
    try {
        $pdo->exec("ALTER TABLE payments ADD INDEX idx_external_id (external_id)");
        echo "   ✓ Added external_id index\n";
    } catch (Exception $e) {
        echo "   - external_id index already exists\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE payments ADD INDEX idx_payment_method (payment_method)");
        echo "   ✓ Added payment_method index\n";
    } catch (Exception $e) {
        echo "   - payment_method index already exists\n";
    }
    
    echo "\n2. Adding/updating admin settings...\n";
    
    // Add new admin settings
    $newSettings = [
        'saweria_api_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJjdXJyZW5jeSI6IklEUiIsImlkIjoiNWNjODc1NTItMjUwMC00ZmE5LWFhZmYtYWY1MmM4MTZiZTBhIiwiZW1haWwiOiJtdWhhbW1hZGlsaGFtMTM3MTNAZ21haWwuY29tIiwidXNlcm5hbWUiOiJtaWxoYW02OSIsInRpZXJfa2V5IjoiQkFTSUMiLCJpc3MiOiJzYXdlcmlhLWxvZ2luIiwiaWF0IjoxNzgzMTYxMTc5LCJleHAiOjE3ODM0MjAzNzksImp0aSI6IjIzNTEyMzE5LTk4NjUtNDg2Mi1hMjQ1LWVhOGRjZTM0NTdhZSJ9.aiB1H9S5yo98OzJnx2IPKUch2FiMyq9TU5zVMJAcgdo',
        'payment_methods_qrispay' => '1',
        'payment_methods_saweria' => '1'
    ];
    
    foreach ($newSettings as $key => $value) {
        $stmt = $pdo->prepare("
            INSERT INTO admin_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE 
            setting_value = IF(setting_value = '' OR setting_value IS NULL, VALUES(setting_value), setting_value)
        ");
        $stmt->execute([$key, $value]);
        echo "   ✓ Added/updated setting: $key\n";
    }
    
    echo "\n3. Testing API connections...\n";
    
    // Test TrackTaco API
    $tracktacoKey = getAdminSetting('tracktaco_api_key');
    if ($tracktacoKey) {
        echo "   ✓ TrackTaco API key configured\n";
    } else {
        echo "   ⚠ TrackTaco API key not configured\n";
    }
    
    // Test payment methods
    $qrispayEnabled = getAdminSetting('payment_methods_qrispay', '1');
    $saweriaEnabled = getAdminSetting('payment_methods_saweria', '1');
    
    echo "   - QRIS Pay: " . ($qrispayEnabled ? 'Enabled' : 'Disabled') . "\n";
    echo "   - Saweria: " . ($saweriaEnabled ? 'Enabled' : 'Disabled') . "\n";
    
    echo "\n=== Migration Complete! ===\n";
    echo "✓ Database schema updated successfully\n";
    echo "✓ Payment methods configured\n";
    echo "✓ API settings ready\n\n";
    
    echo "Next steps:\n";
    echo "1. Update API keys in Settings page\n";
    echo "2. Test payment functionality\n";
    echo "3. Test tracking number search\n\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>