<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../config.php';
require_once '../auth.php';

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$pdo = getDBConnection();

try {
    // Get pagination parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Query top-up history
    $stmt = $pdo->prepare("
        SELECT 
            th.id,
            th.payment_id,
            th.payment_method,
            th.credits_purchased,
            th.bonus_credits,
            th.total_credits,
            th.amount_paid,
            th.payment_reference,
            th.purchased_at,
            p.status as payment_status,
            p.external_id,
            p.qris_id
        FROM topup_history th
        LEFT JOIN payments p ON th.payment_id = p.id
        WHERE th.user_id = ?
        ORDER BY th.purchased_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$user['id'], $limit, $offset]);
    $history = $stmt->fetchAll();
    
    // Get total count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM topup_history WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $total = $stmt->fetchColumn();
    
    // Get total amount spent
    $stmt = $pdo->prepare("SELECT SUM(amount_paid) FROM topup_history WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $totalSpent = $stmt->fetchColumn() ?? 0;
    
    // Format response
    $formattedHistory = array_map(function($item) {
        return [
            'id' => $item['id'],
            'payment_id' => $item['payment_id'],
            'payment_method' => strtoupper($item['payment_method']),
            'payment_method_display' => $item['payment_method'] === 'qrispay' ? 'QRIS' : 'Saweria',
            'credits_purchased' => $item['credits_purchased'],
            'bonus_credits' => $item['bonus_credits'],
            'total_credits' => $item['total_credits'],
            'amount_paid' => $item['amount_paid'],
            'payment_reference' => $item['payment_reference'],
            'purchased_at' => $item['purchased_at'],
            'payment_status' => $item['payment_status']
        ];
    }, $history);
    
    echo json_encode([
        'success' => true,
        'history' => $formattedHistory,
        'total' => $total,
        'total_spent' => $totalSpent,
        'limit' => $limit,
        'offset' => $offset
    ]);
    
} catch (Exception $e) {
    error_log('Top-Up History API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load history: ' . $e->getMessage()
    ]);
}
