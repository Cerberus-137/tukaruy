<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';
require_once '../auth.php';
require_once 'TukeruyAPI.php';

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // Initialize API
    $api = new TukeruyAPI();
    
    // Get search without filters to get available dates
    // This will show all available tracking numbers and their ship dates
    $result = $api->search([], 500); // Get first 500 results to analyze dates
    
    $dates = [];
    $dateCount = [];
    
    // Extract unique ship dates from results
    if (isset($result['results']) && is_array($result['results'])) {
        foreach ($result['results'] as $item) {
            if (isset($item['ship_date'])) {
                try {
                    $date = new DateTime($item['ship_date']);
                    $dateStr = $date->format('Y-m-d');
                    $dateCount[$dateStr] = ($dateCount[$dateStr] ?? 0) + 1;
                } catch (Exception $e) {
                    // Skip invalid dates
                }
            }
        }
    }
    
    // Sort dates and convert to array of dates with counts
    ksort($dateCount);
    foreach ($dateCount as $date => $count) {
        $dates[] = [
            'date' => $date,
            'count' => $count
        ];
    }
    
    echo json_encode([
        'success' => true,
        'dates' => $dates,
        'total_dates' => count($dates)
    ]);
    
} catch (Exception $e) {
    error_log('Ship Dates API Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
