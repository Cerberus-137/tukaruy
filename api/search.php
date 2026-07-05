<?php
session_start();

// Enhanced error logging
error_log("🔍 Search API Request - Method: " . $_SERVER['REQUEST_METHOD'] . ", URI: " . $_SERVER['REQUEST_URI']);

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once '../config.php';
require_once '../auth.php';
require_once 'TukeruyAPI.php';

// Require login
if (!isLoggedIn()) {
    error_log("❌ Search API: Not logged in");
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("📋 Search API: Filters received - " . json_encode($input));
    
    // Initialize API
    $api = new TukeruyAPI();
    
    // Process filters
    $filters = [];
    
    // Carrier filter
    if (isset($input['carrier']) && !empty($input['carrier'])) {
        $filters['carrier'] = $input['carrier'];
    }
    
    // Status filter
    if (isset($input['status']) && !empty($input['status'])) {
        $filters['status'] = $input['status'];
    }
    
    // Origin filter
    if (isset($input['origin_country']) && !empty($input['origin_country'])) {
        $filters['origin_country'] = $input['origin_country'];
    }
    if (isset($input['origin_city']) && !empty($input['origin_city'])) {
        $filters['origin_city'] = $input['origin_city'];
    }
    
    // Destination filter
    if (isset($input['dest_country']) && !empty($input['dest_country'])) {
        $filters['dest_country'] = $input['dest_country'];
    }
    if (isset($input['dest_state']) && !empty($input['dest_state'])) {
        $filters['dest_state'] = $input['dest_state'];
    }
    if (isset($input['dest_city']) && !empty($input['dest_city'])) {
        $filters['dest_city'] = $input['dest_city'];
    }
    
    // Date filters
    if (isset($input['ship_from']) && !empty($input['ship_from'])) {
        $filters['ship_from'] = $input['ship_from'];
    }
    if (isset($input['ship_to']) && !empty($input['ship_to'])) {
        $filters['ship_to'] = $input['ship_to'];
    }
    if (isset($input['delivery_from']) && !empty($input['delivery_from'])) {
        $filters['delivery_from'] = $input['delivery_from'];
    }
    if (isset($input['delivery_to']) && !empty($input['delivery_to'])) {
        $filters['delivery_to'] = $input['delivery_to'];
    }
    
    // Cursor for pagination
    $cursor = $input['cursor'] ?? null;
    
    error_log("🔎 Search API: Processing filters - " . json_encode($filters));
    
    // Perform search
    $result = $api->search($filters, ITEMS_PER_PAGE, $cursor);
    
    error_log("✅ Search API: Found " . count($result['results'] ?? []) . " results");
    
    echo json_encode([
        'success' => true,
        'results' => $result['results'] ?? [],
        'next_cursor' => $result['next_cursor'] ?? null,
        'total' => $result['total'] ?? 0
    ]);
    
} catch (Exception $e) {
    error_log('❌ Search API Error: ' . $e->getMessage());
    error_log('❌ Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}