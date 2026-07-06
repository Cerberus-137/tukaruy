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
    $input = json_decode(file_get_contents('php://input'), true);
    
    $tnId = $input['tn_id'] ?? '';
    
    if (empty($tnId)) {
        throw new Exception('Tracking ID required');
    }
    
    // Check if user has enough tickets
    if ($user['tickets'] < 1) {
        throw new Exception('Insufficient credits. Please top up your account.');
    }
    
    // Initialize API
    $api = new TukeruyAPI();
    
    // Reveal tracking number
    $response = $api->reveal([$tnId]);
    
    if (!isset($response['results'][0])) {
        throw new Exception('Failed to reveal tracking number');
    }
    
    $result = $response['results'][0];
    
    if ($result['outcome'] !== 'revealed') {
        $errorMessages = [
            'already_revealed' => 'This tracking number has already been revealed by another customer',
            'not_found' => 'Tracking number not found',
            'insufficient_credits' => 'Insufficient credits',
            'internal' => 'Internal server error. Please try again.'
        ];
        
        $errorMessage = $errorMessages[$result['outcome']] ?? 'Failed to reveal tracking number';
        throw new Exception($errorMessage);
    }
    
    // Deduct ticket from user
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    try {
        // Update user tickets
        $stmt = $pdo->prepare("UPDATE users SET tickets = tickets - 1 WHERE id = ? AND tickets >= 1");
        $stmt->execute([$user['id']]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Insufficient credits or concurrent usage detected');
        }
        
        // Log usage
        $stmt = $pdo->prepare("
            INSERT INTO ticket_usage (user_id, tn_id, tracking_number, carrier, tickets_used) 
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $user['id'],
            $tnId,
            $result['tracking_number'],
            $result['carrier']
        ]);
        
        $pdo->commit();
        
        // Get updated user tickets
        $newTicketCount = $user['tickets'] - 1;
        
        echo json_encode([
            'success' => true,
            'result' => [
                'tracking_number' => $result['tracking_number'],
                'carrier' => $result['carrier'],
                'service' => $result['service'] ?? 'unknown',
                'status' => $result['status'] ?? 'pre-transit',
                'dest' => $result['dest'] ?? null,
                'origin' => $result['origin'] ?? null,
                'revealed_at' => $result['revealed_at'] ?? date('Y-m-d H:i:s')
            ],
            'credits_remaining' => $newTicketCount
        ], JSON_UNESCAPED_SLASHES);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Reveal API Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_SLASHES);
}