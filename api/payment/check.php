<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';
require_once '../../auth.php';
require_once '../QRISPayAPI.php';
require_once '../SaweriaAPI.php';

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $user = getCurrentUser();
    $qrisId = $_GET['qris_id'] ?? null;
    $saweriaId = $_GET['saweria_id'] ?? null;
    
    if (!$qrisId && !$saweriaId) {
        throw new Exception('Payment ID required');
    }
    
    $pdo = getDBConnection();
    
    if ($qrisId) {
        // Check QRIS payment
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? AND qris_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user['id'], $qrisId]);
        $payment = $stmt->fetch();
        
        if (!$payment) {
            throw new Exception('Payment not found');
        }
        
        if ($payment['status'] === 'paid') {
            echo json_encode([
                'success' => true,
                'status' => 'paid',
                'tickets' => $payment['tickets']
            ], JSON_UNESCAPED_SLASHES);
            exit;
        }
        
        // Check status with QRIS API
        $qrisPay = new QRISPayAPI();
        
        try {
            $statusResponse = $qrisPay->checkPaymentStatus($qrisId);
            
            error_log('QRIS Status Response: ' . json_encode($statusResponse));
            
            // Check if payment is paid (case-insensitive)
            $paymentStatus = isset($statusResponse['status']) ? strtolower($statusResponse['status']) : '';
            
            error_log('Payment Status for QRIS ID ' . $qrisId . ': ' . $paymentStatus);
            
            if ($paymentStatus === 'paid' || $paymentStatus === 'success' || $paymentStatus === 'completed' || $paymentStatus === 'settlement') {
                error_log('Payment confirmed as PAID for QRIS ID: ' . $qrisId);
                
                $pdo->beginTransaction();
                
                try {
                    // Update payment status
                    $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?");
                    $stmt->execute([$payment['id']]);
                    error_log('✅ Payment status updated to PAID in database');
                    
                    // Add tickets to user
                    $stmt = $pdo->prepare("UPDATE users SET tickets = tickets + ? WHERE id = ?");
                    $stmt->execute([$payment['tickets'], $user['id']]);
                    error_log('✅ Tickets added to user: ' . $payment['tickets'] . ' tickets for user ID: ' . $user['id']);
                    
                    // Get updated user tickets
                    $stmt = $pdo->prepare("SELECT tickets FROM users WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    $updatedUser = $stmt->fetch();
                    $newBalance = $updatedUser['tickets'];
                    
                    error_log('✅ User new ticket balance: ' . $newBalance);
                    
                    $pdo->commit();
                    
                    echo json_encode([
                        'success' => true,
                        'status' => 'paid',
                        'tickets_added' => $payment['tickets'],
                        'new_balance' => $newBalance,
                        'message' => 'Payment successful! ' . $payment['tickets'] . ' credits have been added to your account.'
                    ], JSON_UNESCAPED_SLASHES);
                } catch (Exception $dbError) {
                    $pdo->rollBack();
                    error_log('❌ Database error updating payment: ' . $dbError->getMessage());
                    throw $dbError;
                }
            } else {
                error_log('⏳ Payment not yet paid. Current status: ' . $paymentStatus);
                echo json_encode([
                    'success' => true,
                    'status' => 'pending',
                    'api_status' => $paymentStatus,
                    'message' => 'Payment is still pending. Please complete the payment.'
                ], JSON_UNESCAPED_SLASHES);
            }
        } catch (Exception $e) {
            // If API check fails, return current status
            error_log('❌ QRIS API check failed: ' . $e->getMessage());
            echo json_encode([
                'success' => true,
                'status' => $payment['status'],
                'error' => 'Unable to verify payment status: ' . $e->getMessage()
            ], JSON_UNESCAPED_SLASHES);
        }
        
    } else {
        // Check Saweria payment
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? AND external_id = ? AND payment_method = 'saweria' ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user['id'], $saweriaId]);
        $payment = $stmt->fetch();
        
        if (!$payment) {
            throw new Exception('Payment not found');
        }
        
        if ($payment['status'] === 'paid') {
            echo json_encode([
                'success' => true,
                'status' => 'paid',
                'tickets' => $payment['tickets']
            ], JSON_UNESCAPED_SLASHES);
            exit;
        }
        
        // Check status with Saweria API
        $saweria = new SaweriaAPI();
        
        try {
            error_log('Checking Saweria donation status for ID: ' . $saweriaId);
            
            if ($saweria->isDonationPaid($saweriaId)) {
                error_log('Saweria donation confirmed as paid');
                
                $pdo->beginTransaction();
                
                try {
                    // Update payment status
                    $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?");
                    $stmt->execute([$payment['id']]);
                    error_log('Payment status updated to paid');
                    
                    // Add tickets to user
                    $stmt = $pdo->prepare("UPDATE users SET tickets = tickets + ? WHERE id = ?");
                    $stmt->execute([$payment['tickets'], $user['id']]);
                    error_log('Tickets added to user: ' . $payment['tickets']);
                    
                    $pdo->commit();
                    
                    echo json_encode([
                        'success' => true,
                        'status' => 'paid',
                        'tickets' => $payment['tickets']
                    ], JSON_UNESCAPED_SLASHES);
                } catch (Exception $dbError) {
                    $pdo->rollBack();
                    error_log('Database error updating payment: ' . $dbError->getMessage());
                    throw $dbError;
                }
            } else {
                error_log('Saweria donation not yet paid');
                echo json_encode([
                    'success' => true,
                    'status' => $payment['status']
                ], JSON_UNESCAPED_SLASHES);
            }
        } catch (Exception $e) {
            // If API check fails, return current status
            error_log('Saweria API check failed: ' . $e->getMessage());
            echo json_encode([
                'success' => true,
                'status' => $payment['status'],
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_SLASHES);
        }
    }
    
} catch (Exception $e) {
    error_log('Payment Check Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_SLASHES);
}
