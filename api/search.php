<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once 'TukeruyAPI.php';

try {
    $api = new TukeruyAPI();
    
    // Get filter parameters from POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    $filters = $input['filters'] ?? [];
    $pageSize = $input['page_size'] ?? ITEMS_PER_PAGE;
    $cursor = $input['cursor'] ?? null;
    
    // Perform search
    $result = $api->search($filters, $pageSize, $cursor);
    
    // Format results for frontend
    $formattedResults = [];
    foreach ($result['results'] as $item) {
        $formattedResults[] = [
            'tn_id' => $item['tn_id'],
            'carrier' => TukeruyAPI::formatCarrier($item['carrier']),
            'carrier_raw' => $item['carrier'],
            'status' => TukeruyAPI::formatStatus($item['status']),
            'status_raw' => $item['status'],
            'status_class' => TukeruyAPI::getStatusBadgeClass($item['status']),
            'service' => ucfirst($item['service'] ?? 'N/A'),
            'origin' => isset($item['origin']) ? 
                ($item['origin']['city'] ?? '') . ', ' . ($item['origin']['state'] ?? '') . ' ' . ($item['origin']['country'] ?? '') : 
                'N/A',
            'destination' => ($item['dest']['city'] ?? '') . ', ' . ($item['dest']['state'] ?? '') . ' ' . ($item['dest']['country'] ?? ''),
            'ship_date' => $item['ship_date'] ?? 'Not shipped yet',
            'est_delivery_date' => $item['est_delivery_date'] ?? 'N/A',
            'weight' => isset($item['weight_grams']) ? number_format($item['weight_grams'] / 1000, 2) . ' kg' : 'N/A',
            'reveal_cost' => $item['reveal_cost_credits'] ?? 1
        ];
    }
    
    echo json_encode([
        'success' => true,
        'results' => $formattedResults,
        'next_cursor' => $result['next_cursor'] ?? null,
        'total' => $result['total'] ?? 0
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
