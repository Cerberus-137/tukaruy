<?php
session_start();
header('Content-Type: application/json');
require_once '../../config.php';
require_once '../../auth.php';
require_once '../QRISPayAPI.php';
require_once '../SaweriaAPI.php';

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
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
            ]);
            exit;
        }
        
        // Check status with QRIS API
        $qrisPay = new QRISPayAPI();
        
        try {
            $statusResponse = $qrisPay->checkPaymentStatus($qrisId);
            
            if ($statusResponse['status'] === 'paid' || $statusResponse['status'] === 'success') {
                $pdo->beginTransaction();
                
                // Update payment status
                $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?");
                $stmt->execute([$payment['id']]);
                
                // Add tickets to user
                $stmt = $pdo->prepare("UPDATE users SET tickets = tickets + ? WHERE id = ?");
                $stmt->execute([$payment['tickets'], $user['id']]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'status' => 'paid',
                    'tickets' => $payment['tickets']
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'status' => $payment['status']
                ]);
            }
        } catch (Exception $e) {
            // If API check fails, return current status
            echo json_encode([
                'success' => true,
                'status' => $payment['status']
            ]);
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
            ]);
            exit;
        }
        
        // Check status with Saweria API
        $saweria = new SaweriaAPI();
        
        try {
            if ($saweria->isDonationPaid($saweriaId)) {
                $pdo->beginTransaction();
                
                // Update payment status
                $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?");
                $stmt->execute([$payment['id']]);
                
                // Add tickets to user
                $stmt = $pdo->prepare("UPDATE users SET tickets = tickets + ? WHERE id = ?");
                $stmt->execute([$payment['tickets'], $user['id']]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'status' => 'paid',
                    'tickets' => $payment['tickets']
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'status' => $payment['status']
                ]);
            }
        } catch (Exception $e) {
            // If API check fails, return current status
            echo json_encode([
                'success' => true,
                'status' => $payment['status']
            ]);
        }
    }
    
} catch (Exception $e) {
    error_log('Payment Check Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
