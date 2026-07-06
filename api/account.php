<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';
require_once '../auth.php';
require_once 'TukeruyAPI.php';

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $user = getCurrentUser();
    
    // Check if history is requested
    if (isset($_GET['history']) && $_GET['history'] === 'true') {
        // Get reveal history from TrackTaco API
        $api = new TukeruyAPI();
        $accountData = $api->getAccount(50); // Get last 50 reveals
        
        echo json_encode([
            'success' => true,
            'history' => $accountData['history'] ?? [],
            'credits_balance' => $accountData['credits']['balance'] ?? 0
        ], JSON_UNESCAPED_SLASHES);
    } else {
        // Get basic account info
        $api = new TukeruyAPI();
        $accountData = $api->getAccount(1);
        
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'tickets' => $user['tickets'],
                'created_at' => $user['created_at']
            ],
            'credits_balance' => $accountData['credits']['balance'] ?? 0
        ], JSON_UNESCAPED_SLASHES);
    }
    
} catch (Exception $e) {
    error_log('Account API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to get account information'
    ], JSON_UNESCAPED_SLASHES);
}