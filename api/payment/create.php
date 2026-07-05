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
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
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
                'message' => $saweriaResponse['message'] ?? ''
            ]
        ], JSON_UNESCAPED_SLASHES);
        
    } else {
        // Use QRIS Pay API (default)
        $qrisPay = new QRISPayAPI();
        
        try {
            $qrisResponse = $qrisPay->generateQRIS($amount, $paymentRef, SITE_URL . '/tickets.php');
        } catch (Exception $e) {
            error_log('QRISPay API Error: ' . $e->getMessage());
            throw new Exception('Failed to generate QRIS: ' . $e->getMessage());
        }
        
        // Validate QRIS response
        if (!isset($qrisResponse['qris_id'])) {
            error_log('QRIS Response missing qris_id. Full response: ' . json_encode($qrisResponse));
            throw new Exception('Invalid QRIS response - missing QRIS ID');
        }
        
        if (empty($qrisResponse['qris_image_url'])) {
            error_log('QRIS Response missing qris_image_url. Full response: ' . json_encode($qrisResponse));
            // Continue but log warning - will be handled by frontend
        }
        
        // Convert expired_at to MySQL TIMESTAMP format if it's in ISO 8601 format
        $expiredAt = $qrisResponse['expired_at'] ?? date('Y-m-d H:i:s', strtotime('+1 hour'));
        if (!empty($expiredAt)) {
            try {
                $dateTime = new DateTime($expiredAt, new DateTimeZone('UTC'));
                $expiredAt = $dateTime->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                error_log('Failed to parse expired_at: ' . $expiredAt);
                $expiredAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            }
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
            $expiredAt
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
        ], JSON_UNESCAPED_SLASHES);
    }
    
} catch (Exception $e) {
    error_log('Payment Creation Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_SLASHES);
}