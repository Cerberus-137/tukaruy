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
    $qrisId = $_GET['qris_id'] ?? '';
    
    if (empty($qrisId)) {
        throw new Exception('QRIS ID is required');
    }
    
    // Check if payment belongs to user
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE qris_id = ? AND user_id = ?");
    $stmt->execute([$qrisId, $user['id']]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        throw new Exception('Payment not found');
    }
    
    // If already paid, return success
    if ($payment['status'] === 'paid') {
        echo json_encode([
            'success' => true,
            'status' => 'paid',
            'tickets' => $payment['tickets']
        ]);
        exit;
    }
    
    // Check status with QRISPay
    $qrisPay = new QRISPayAPI();
    $status = $qrisPay->checkPaymentStatus($qrisId);
    
    // Update payment status if paid
    if ($status['status'] === 'paid') {
        $pdo->beginTransaction();
        
        // Update payment
        $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE qris_id = ?");
        $stmt->execute([$qrisId]);
        
        // Add tickets to user
        $stmt = $pdo->prepare("UPDATE users SET tickets = tickets + ? WHERE id = ?");
        $stmt->execute([$payment['tickets'], $user['id']]);
        
        $pdo->commit();
        
        // Update session
        $_SESSION['user_tickets'] = ($user['tickets'] ?? 0) + $payment['tickets'];
        
        echo json_encode([
            'success' => true,
            'status' => 'paid',
            'tickets' => $payment['tickets']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'status' => $status['status']
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
