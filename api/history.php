<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../config.php';
require_once '../auth.php';

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$pdo = getDBConnection();

try {
    // Get reveal history for current user
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Query reveal history
    $stmt = $pdo->prepare("
        SELECT 
            id,
            tn_id,
            tracking_number,
            carrier,
            service,
            status,
            origin_country,
            origin_state,
            origin_city,
            dest_country,
            dest_state,
            dest_city,
            ship_date,
            est_delivery_date,
            weight_grams,
            signature_required,
            photo_confirmed,
            credits_used,
            revealed_at
        FROM reveal_history
        WHERE user_id = ?
        ORDER BY revealed_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$user['id'], $limit, $offset]);
    $history = $stmt->fetchAll();
    
    // Get total count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reveal_history WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $total = $stmt->fetchColumn();
    
    // Format response
    $formattedHistory = array_map(function($item) {
        // Format location strings
        $origin = '';
        if ($item['origin_city']) $origin .= $item['origin_city'];
        if ($item['origin_state']) $origin .= ($origin ? ', ' : '') . $item['origin_state'];
        if ($item['origin_country']) $origin .= ($origin ? ', ' : '') . $item['origin_country'];
        
        $dest = '';
        if ($item['dest_city']) $dest .= $item['dest_city'];
        if ($item['dest_state']) $dest .= ($dest ? ', ' : '') . $item['dest_state'];
        if ($item['dest_country']) $dest .= ($dest ? ', ' : '') . $item['dest_country'];
        
        return [
            'id' => $item['id'],
            'tn_id' => $item['tn_id'],
            'tracking_number' => $item['tracking_number'],
            'carrier' => $item['carrier'],
            'service' => $item['service'],
            'status' => $item['status'],
            'origin' => $origin ?: 'N/A',
            'destination' => $dest ?: 'N/A',
            'ship_date' => $item['ship_date'],
            'est_delivery_date' => $item['est_delivery_date'],
            'weight_grams' => $item['weight_grams'],
            'signature_required' => (bool)$item['signature_required'],
            'photo_confirmed' => (bool)$item['photo_confirmed'],
            'credits_used' => $item['credits_used'],
            'revealed_at' => $item['revealed_at']
        ];
    }, $history);
    
    echo json_encode([
        'success' => true,
        'history' => $formattedHistory,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);
    
} catch (Exception $e) {
    error_log('History API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load history: ' . $e->getMessage()
    ]);
}
