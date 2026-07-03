<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once 'TukeruyAPI.php';

try {
    $api = new TukeruyAPI();
    
    $limit = $_GET['limit'] ?? 50;
    $cursor = $_GET['cursor'] ?? null;
    
    $result = $api->getAccount($limit, $cursor);
    
    echo json_encode([
        'success' => true,
        'credits' => $result['credits']['balance'] ?? 0,
        'history' => $result['history'] ?? [],
        'next_cursor' => $result['next_cursor'] ?? null
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
