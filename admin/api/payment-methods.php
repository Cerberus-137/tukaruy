<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../config.php';
require_once '../../auth.php';

// Require login & admin role
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_SLASHES);
    exit;
}

if (getCurrentUser()['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied'], JSON_UNESCAPED_SLASHES);
    exit;
}

$pdo = getDBConnection();
$action = $_GET['action'] ?? 'list';

try {
    // UPDATE payment method
    if ($action === 'update') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = $input['id'] ?? null;
        $enabled = $input['enabled'] ?? 0;
        
        if (!$id) {
            throw new Exception('Payment method ID required');
        }
        
        $stmt = $pdo->prepare("UPDATE payment_methods SET enabled = ? WHERE id = ?");
        $stmt->execute([$enabled, $id]);
        
        echo json_encode(['success' => true], JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    // GET all payment methods (default)
    $stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY sort_order ASC");
    $methods = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'methods' => $methods], JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    error_log('Payment Methods API Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_SLASHES);
}
