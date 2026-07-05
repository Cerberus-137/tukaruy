<?php
session_start();
header('Content-Type: application/json');
require_once '../../config.php';
require_once '../../auth.php';

// Require login & admin role
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (getCurrentUser()['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$pdo = getDBConnection();
$requestUri = $_SERVER['REQUEST_URI'];

try {
    // UPDATE package
    if (strpos($requestUri, '/update') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = $input['id'] ?? null;
        $field = $input['field'] ?? null;
        $value = $input['value'] ?? null;
        
        if (!$id || !$field) {
            throw new Exception('Missing required fields');
        }
        
        // Validate field
        $allowedFields = ['price', 'bonus', 'discount_percentage', 'active'];
        if (!in_array($field, $allowedFields)) {
            throw new Exception('Invalid field');
        }
        
        // Update package
        $stmt = $pdo->prepare("UPDATE ticket_packages SET $field = ? WHERE id = ?");
        $stmt->execute([$value, $id]);
        
        // Recalculate total_credits if bonus changed
        if ($field === 'bonus') {
            $stmt = $pdo->prepare("
                UPDATE ticket_packages 
                SET total_credits = credits + bonus 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
        }
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    // CREATE package
    if (strpos($requestUri, '/create') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $credits = $input['credits'] ?? null;
        $price = $input['price'] ?? null;
        $bonus = $input['bonus'] ?? 0;
        $discount = $input['discount_percentage'] ?? 0;
        
        if (!$credits || !$price) {
            throw new Exception('Credits and price are required');
        }
        
        $totalCredits = $credits + $bonus;
        
        // Get max order_index
        $stmt = $pdo->query("SELECT MAX(order_index) as max_order FROM ticket_packages");
        $result = $stmt->fetch();
        $orderIndex = ($result['max_order'] ?? 0) + 1;
        
        // Insert package
        $stmt = $pdo->prepare("
            INSERT INTO ticket_packages (credits, price, bonus, total_credits, discount_percentage, order_index, active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$credits, $price, $bonus, $totalCredits, $discount, $orderIndex]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        exit;
    }
    
    // DELETE package
    if (strpos($requestUri, '/delete') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = $input['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Package ID required');
        }
        
        $stmt = $pdo->prepare("DELETE FROM ticket_packages WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    // GET all packages (default)
    $stmt = $pdo->query("SELECT * FROM ticket_packages ORDER BY order_index ASC");
    $packages = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'packages' => $packages]);
    
} catch (Exception $e) {
    error_log('Packages API Error: ' + $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
