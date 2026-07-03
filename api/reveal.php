<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once 'TukeruyAPI.php';

try {
    $api = new TukeruyAPI();
    
    // Get tn_id from POST
    $input = json_decode(file_get_contents('php://input'), true);
    $tnId = $input['tn_id'] ?? null;
    
    if (!$tnId) {
        throw new Exception('tn_id is required');
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
    
    echo json_encode([
        'success' => true,
        'tracking_number' => $item['tracking_number'],
        'carrier' => TukeruyAPI::formatCarrier($item['carrier']),
        'status' => TukeruyAPI::formatStatus($item['status']),
        'credits_remaining' => $result['credits_remaining']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
