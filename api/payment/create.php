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
    $input = json_decode(file_get_contents('php://input'), true);
    
    $credits = $input['credits'] ?? 0;
    $amount = $input['amount'] ?? 0;
    $total = $input['total'] ?? 0;
    $paymentMethod = $input['payment_method'] ?? 'qrispay';
    
    if ($credits <= 0 || $amount <= 0) {
        throw new Exception('Invalid package');
    }
    
    // Validate QRIS maximum amount
    if ($paymentMethod === 'qrispay' && $amount > QRIS_MAX_AMOUNT) {
        throw new Exception('QRIS payment maximum is Rp ' . number_format(QRIS_MAX_AMOUNT) . '. Please select Saweria for larger amounts or contact admin for custom packages.');
    }
    
    // Check if payment method is enabled
    if (!isPaymentMethodEnabled($paymentMethod)) {
        throw new Exception('Payment method not available');
    }
    
    // Generate payment reference
    $paymentRef = 'TKY-' . $user['id'] . '-' . time();
    
    if ($paymentMethod === 'saweria') {
        // Use Saweria API
        $saweria = new SaweriaAPI();
        
        try {
            $saweriaResponse = $saweria->generatePaymentLink(
                $amount,
                "Top up {$total} credits - Tukeruy",
                $user['first_name'] . ' ' . $user['last_name']
            );
        } catch (Exception $e) {
            error_log('Saweria API Error: ' . $e->getMessage());
            throw new Exception('Failed to generate Saweria payment: ' . $e->getMessage());
        }
        
        // Save to database
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO payments (user_id, payment_method, external_id, amount, tickets, status, payment_reference, payment_url, created_at)
            VALUES (?, 'saweria', ?, ?, ?, 'pending', ?, ?, NOW())
        ");
        $stmt->execute([
            $user['id'],
            $saweriaResponse['donation_id'],
            $amount,
            $total,
            $paymentRef,
            $saweriaResponse['payment_url']
        ]);
        
        echo json_encode([
            'success' => true,
            'payment_method' => 'saweria',
            'saweria' => [
                'donation_id' => $saweriaResponse['donation_id'],
                'payment_url' => $saweriaResponse['payment_url'],
                'amount' => $amount,
                'payment_reference' => $paymentRef,
                'message' => $saweriaResponse['message']
            ]
        ]);
        
    } else {
        // Use QRIS Pay API (default)
        $qrisPay = new QRISPayAPI();
        
        try {
            $qrisResponse = $qrisPay->generateQRIS($amount, $paymentRef, SITE_URL . '/tickets.php');
        } catch (Exception $e) {
            error_log('QRISPay API Error: ' . $e->getMessage());
            
            // Better error message for QRIS generation
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'QRIS generated successfully') !== false) {
                // This is actually a success case with confusing message
                throw new Exception('QRIS code generated successfully but response format is invalid. Please try again.');
            } else {
                throw new Exception('Failed to generate QRIS: ' . $errorMessage);
            }
        }
        
        // Validate QRIS response
        if (!isset($qrisResponse['qris_id'])) {
            throw new Exception('Invalid QRIS response - missing QRIS ID');
        }
        
        // Save to database
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO payments (user_id, payment_method, qris_id, amount, tickets, status, payment_reference, qris_image_url, expired_at, created_at)
            VALUES (?, 'qrispay', ?, ?, ?, 'pending', ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user['id'],
            $qrisResponse['qris_id'],
            $amount,
            $total,
            $paymentRef,
            $qrisResponse['qris_image_url'] ?? '',
            $qrisResponse['expired_at'] ?? date('Y-m-d H:i:s', strtotime('+1 hour'))
        ]);
        
        echo json_encode([
            'success' => true,
            'payment_method' => 'qrispay',
            'qris' => [
                'qris_id' => $qrisResponse['qris_id'],
                'qris_image_url' => $qrisResponse['qris_image_url'] ?? '',
                'amount' => $qrisResponse['amount'] ?? $amount,
                'expired_at' => $qrisResponse['expired_at'] ?? date('Y-m-d H:i:s', strtotime('+1 hour')),
                'expires_in_seconds' => $qrisResponse['expires_in_seconds'] ?? 3600,
                'payment_reference' => $qrisResponse['payment_reference'] ?? $paymentRef
            ]
        ]);
    }
    
} catch (Exception $e) {
    error_log('Payment Creation Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
