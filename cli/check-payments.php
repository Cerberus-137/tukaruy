<?php
/**
 * CLI Script to check and update pending payments
 * Usage: php cli/check-payments.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../api/QRISPayAPI.php';
require_once __DIR__ . '/../api/SaweriaAPI.php';

// Prevent web access
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script can only be run from CLI');
}

echo "=== Payment Check Script ===\n\n";

try {
    $pdo = getDBConnection();
    
    // Get all pending QRIS payments
    $stmt = $pdo->prepare("
        SELECT p.*, u.email 
        FROM payments p
        JOIN users u ON p.user_id = u.id
        WHERE p.status = 'pending' AND p.payment_method = 'qrispay'
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $qrisPayments = $stmt->fetchAll();
    
    if ($qrisPayments) {
        echo "Checking " . count($qrisPayments) . " pending QRIS payments...\n\n";
        
        $qrisPay = new QRISPayAPI();
        $updated = 0;
        
        foreach ($qrisPayments as $payment) {
            try {
                echo "[QRIS] Checking payment {$payment['id']} (Ref: {$payment['payment_reference']})... ";
                
                $statusResponse = $qrisPay->checkPaymentStatus($payment['qris_id']);
                $status = isset($statusResponse['status']) ? strtolower($statusResponse['status']) : '';
                
                echo "Status: $status ";
                
                if ($status === 'paid' || $status === 'success' || $status === 'completed') {
                    // Update payment to paid
                    $pdo->beginTransaction();
                    
                    $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?");
                    $stmt->execute([$payment['id']]);
                    
                    $stmt = $pdo->prepare("UPDATE users SET tickets = tickets + ? WHERE id = ?");
                    $stmt->execute([$payment['tickets'], $payment['user_id']]);
                    
                    $pdo->commit();
                    
                    echo "✅ UPDATED (Added {$payment['tickets']} tickets to {$payment['email']})\n";
                    $updated++;
                } else {
                    echo "⏳ Still pending\n";
                }
            } catch (Exception $e) {
                echo "❌ ERROR: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\nQRIS: Updated $updated payments\n\n";
    } else {
        echo "No pending QRIS payments found.\n\n";
    }
    
    // Get all pending Saweria payments
    $stmt = $pdo->prepare("
        SELECT p.*, u.email 
        FROM payments p
        JOIN users u ON p.user_id = u.id
        WHERE p.status = 'pending' AND p.payment_method = 'saweria'
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $saweriaPayments = $stmt->fetchAll();
    
    if ($saweriaPayments) {
        echo "Checking " . count($saweriaPayments) . " pending Saweria payments...\n\n";
        
        $saweria = new SaweriaAPI();
        $updated = 0;
        
        foreach ($saweriaPayments as $payment) {
            try {
                echo "[Saweria] Checking payment {$payment['id']} (Ref: {$payment['payment_reference']})... ";
                
                if ($saweria->isDonationPaid($payment['external_id'])) {
                    // Update payment to paid
                    $pdo->beginTransaction();
                    
                    $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?");
                    $stmt->execute([$payment['id']]);
                    
                    $stmt = $pdo->prepare("UPDATE users SET tickets = tickets + ? WHERE id = ?");
                    $stmt->execute([$payment['tickets'], $payment['user_id']]);
                    
                    $pdo->commit();
                    
                    echo "✅ UPDATED (Added {$payment['tickets']} tickets to {$payment['email']})\n";
                    $updated++;
                } else {
                    echo "⏳ Still pending\n";
                }
            } catch (Exception $e) {
                echo "❌ ERROR: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\nSaweria: Updated $updated payments\n\n";
    } else {
        echo "No pending Saweria payments found.\n\n";
    }
    
    echo "=== Check Complete ===\n";
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
