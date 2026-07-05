<?php
session_start();

// Log all requests for debugging
error_log("📋 Admin Packages API Request - Method: " . $_SERVER['REQUEST_METHOD'] . ", URI: " . $_SERVER['REQUEST_URI']);
error_log("📋 GET params: " . json_encode($_GET));
error_log("📋 POST params: " . json_encode($_POST));

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
require_once '../../config.php';
require_once '../../auth.php';

// Require login & admin role
if (!isLoggedIn()) {
    error_log("❌ Admin Packages API: Not logged in");
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (getCurrentUser()['role'] !== 'admin') {
    error_log("❌ Admin Packages API: Not admin - Role: " . getCurrentUser()['role']);
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$pdo = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

error_log("✅ Admin Packages API: Authenticated - Action: " . $action);

try {
    // UPDATE package
    if ($action === 'update') {
        $input = json_decode(file_get_contents('php://input'), true);
        error_log("📝 Update request input: " . json_encode($input));
        
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
        
        error_log("✅ Updated package ID $id: $field = $value");
        
        // Recalculate total_credits if bonus changed
        if ($field === 'bonus') {
            $stmt = $pdo->prepare("
                UPDATE ticket_packages 
                SET total_credits = credits + bonus 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            error_log("✅ Recalculated total_credits for package ID $id");
        }
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    // CREATE package
    if ($action === 'create') {
        $input = json_decode(file_get_contents('php://input'), true);
        error_log("📝 Create request input: " . json_encode($input));
        
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
        
        $newId = $pdo->lastInsertId();
        error_log("✅ Created package ID $newId");
        
        echo json_encode(['success' => true, 'id' => $newId]);
        exit;
    }
    
    // DELETE package
    if ($action === 'delete') {
        $input = json_decode(file_get_contents('php://input'), true);
        error_log("📝 Delete request input: " . json_encode($input));
        
        $id = $input['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Package ID required');
        }
        
        $stmt = $pdo->prepare("DELETE FROM ticket_packages WHERE id = ?");
        $stmt->execute([$id]);
        
        error_log("✅ Deleted package ID $id");
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    // GET all packages (default)
    $stmt = $pdo->query("SELECT * FROM ticket_packages ORDER BY order_index ASC");
    $packages = $stmt->fetchAll();
    
    error_log("✅ Returning " . count($packages) . " packages");
    
    echo json_encode(['success' => true, 'packages' => $packages]);
    
} catch (Exception $e) {
    error_log('❌ Packages API Error: ' . $e->getMessage());
    error_log('❌ Stack trace: ' . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
