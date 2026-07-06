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
    error_log('📅 Ship Dates API: Request received');
    
    // Initialize API
    $api = new TukeruyAPI();
    
    // Get search without filters to get available dates
    // This will show all available tracking numbers and their ship dates
    error_log('📅 Ship Dates API: Fetching search results...');
    $result = $api->search([], 500); // Get first 500 results to analyze dates
    
    error_log('📅 Ship Dates API: Search result keys: ' . json_encode(array_keys($result)));
    
    $dates = [];
    $dateCount = [];
    
    // Extract unique ship dates from results
    // The API returns 'results' array with tracking number details
    if (isset($result['results']) && is_array($result['results'])) {
        error_log('📅 Ship Dates API: Found ' . count($result['results']) . ' results');
        
        foreach ($result['results'] as $item) {
            // Check if item contains ship_date information
            if (isset($item['ship_date'])) {
                try {
                    $date = new DateTime($item['ship_date']);
                    $dateStr = $date->format('Y-m-d');
                    $dateCount[$dateStr] = ($dateCount[$dateStr] ?? 0) + 1;
                } catch (Exception $e) {
                    error_log('📅 Ship Dates API: Invalid date format: ' . $item['ship_date']);
                }
            }
        }
    } else {
        error_log('📅 Ship Dates API: No results array found in response');
        error_log('📅 Ship Dates API: Response structure: ' . json_encode($result, JSON_UNESCAPED_SLASHES));
    }
    
    // Sort dates and convert to array of dates with counts
    ksort($dateCount);
    foreach ($dateCount as $date => $count) {
        $dates[] = [
            'date' => $date,
            'count' => $count
        ];
    }
    
    error_log('✅ Ship Dates API: Extracted ' . count($dates) . ' unique dates');
    
    echo json_encode([
        'success' => true,
        'dates' => $dates,
        'total_dates' => count($dates),
        'total_results' => $result['total'] ?? 0
    ], JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    error_log('❌ Ship Dates API Error: ' . $e->getMessage());
    error_log('❌ Stack trace: ' . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_SLASHES);
}
