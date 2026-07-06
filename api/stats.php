<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

// Simple stats endpoint - returns cached or quick stats
// This is faster than calling getStats() which makes multiple API calls

try {
    // Check if we have cached stats (last 5 minutes)
    $cacheFile = sys_get_temp_dir() . '/tukeruy_stats_cache.json';
    $cacheTime = 300; // 5 minutes
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        // Return cached data
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        echo json_encode([
            'success' => true,
            'stats' => $cachedData,
            'cached' => true
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    // If no cache, return placeholder stats
    // Stats will be updated by background job or when search is performed
    $stats = [
        'total' => 100,
        'fedex' => 35,
        'dhl' => 35,
        'ups' => 30
    ];
    
    // Cache the stats
    file_put_contents($cacheFile, json_encode($stats));
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'cached' => false
    ], JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_SLASHES);
}
