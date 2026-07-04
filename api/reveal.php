<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';
require_once '../auth.php';
require_once 'TukeruyAPI.php';

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Please login first.'
    ]);
    exit;
}

try {
    $user = getCurrentUser();
    $api = new TukeruyAPI();
    
    // Get tn_id from POST
    $input = json_decode(file_get_contents('php://input'), true);
    $tnId = $input['tn_id'] ?? null;
    
    if (!$tnId) {
        throw new Exception('tn_id is required');
    }
    
    // Check if user has tickets
    if ($user['tickets'] < 1) {
        throw new Exception('Insufficient tickets. Please purchase tickets to continue.');
    }
    
    // Reveal tracking number
    $result = $api->reveal($tnId);
    
    if (empty($result['results'])) {
        throw new Exception('No results returned');
    }
    
    $item = $result['results'][0];
    
    if ($item['outcome'] !== 'revealed') {
        $error = $item['error']['message'] ?? 'Unable to reveal tracking number';
        throw new Exception($error);
    }
    
    // Use ticket
    $ticketResult = useTicket($user['id'], $tnId, $item['tracking_number'], $item['carrier']);
    
    if (!$ticketResult['success']) {
        throw new Exception($ticketResult['message']);
    }
    
    echo json_encode([
        'success' => true,
        'tracking_number' => $item['tracking_number'],
        'carrier' => TukeruyAPI::formatCarrier($item['carrier']),
        'status' => TukeruyAPI::formatStatus($item['status']),
        'tickets_remaining' => $ticketResult['remaining_tickets']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
