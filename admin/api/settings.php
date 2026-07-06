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
$requestUri = $_SERVER['REQUEST_URI'];

try {
    // UPDATE setting
    if (strpos($requestUri, '/update') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $key = $input['key'] ?? null;
        $value = $input['value'] ?? null;
        
        if (!$key || !$value) {
            throw new Exception('Key and value are required');
        }
        
        // Check if setting exists
        $stmt = $pdo->prepare("SELECT * FROM admin_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update
            $stmt = $pdo->prepare("UPDATE admin_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO admin_settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
        
        echo json_encode(['success' => true], JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    // GET all settings (default)
    $stmt = $pdo->query("SELECT * FROM admin_settings");
    $settings = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'settings' => $settings], JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    error_log('Settings API Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_SLASHES);
}
