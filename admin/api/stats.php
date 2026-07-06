<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';
require_once '../../auth.php';

// Require login & admin role
if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $totalUsers = $stmt->fetch()['count'];
    
    // Total revenue (paid payments)
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'paid'");
    $totalRevenue = $stmt->fetch()['total'] ?? 0;
    
    // Pending payments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'");
    $pendingPayments = $stmt->fetch()['count'];
    
    // Total credits issued
    $stmt = $pdo->query("SELECT SUM(tickets) as total FROM payments WHERE status = 'paid'");
    $totalCredits = $stmt->fetch()['total'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'total_users' => $totalUsers,
        'total_revenue' => $totalRevenue,
        'pending_payments' => $pendingPayments,
        'total_credits' => $totalCredits
    ], JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_SLASHES);
}
