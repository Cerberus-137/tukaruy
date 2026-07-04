<?php
session_start();
header('Content-Type: application/json');
require_once '../../config.php';
require_once '../../auth.php';
require_once '../QRISPayAPI.php';

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
    
    if ($credits <= 0 || $amount <= 0) {
        throw new Exception('Invalid package');
    }
    
    // Generate payment reference
    $paymentRef = 'TKY-' . $user['id'] . '-' . time();
    
    // Generate QRIS code
    $qrisPay = new QRISPayAPI();
    
    try {
        $qrisResponse = $qrisPay->generateQRIS($amount, $paymentRef, SITE_URL . '/tickets.php');
    } catch (Exception $e) {
        error_log('QRISPay API Error: ' . $e->getMessage());
        throw new Exception('Failed to generate QRIS: ' . $e->getMessage());
    }
    
    // Validate QRIS response
    if (!isset($qrisResponse['qris_id'])) {
        throw new Exception('Invalid QRIS response');
    }
    
    // Save to database
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        INSERT INTO payments (user_id, qris_id, amount, tickets, status, payment_reference, qris_image_url, expired_at)
        VALUES (?, ?, ?, ?, 'pending', ?, ?, ?)
    ");
    $stmt->execute([
        $user['id'],
        $qrisResponse['qris_id'],
        $amount,
        $total,
        $paymentRef,
        $qrisResponse['qris_image_url'],
        $qrisResponse['expired_at']
    ]);
    
    echo json_encode([
        'success' => true,
        'qris' => [
            'qris_id' => $qrisResponse['qris_id'],
            'qris_image_url' => $qrisResponse['qris_image_url'],
            'amount' => $qrisResponse['amount'],
            'expired_at' => $qrisResponse['expired_at'],
            'expires_in_seconds' => $qrisResponse['expires_in_seconds'],
            'payment_reference' => $qrisResponse['payment_reference']
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Payment Creation Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
