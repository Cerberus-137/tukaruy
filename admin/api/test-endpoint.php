<?php
// Simple test endpoint to verify PHP execution
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

echo json_encode([
    'success' => true,
    'message' => 'API endpoint is working',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'request_uri' => $_SERVER['REQUEST_URI'],
    'query_string' => $_SERVER['QUERY_STRING'] ?? ''
], JSON_UNESCAPED_SLASHES);
