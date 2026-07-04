<?php
// Configuration file for Tukeruy

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'tukarkuy');
define('DB_USER', 'root');
define('DB_PASS', 'Milham159753');

// Site configuration
define('SITE_NAME', 'Tukarkuy');
define('SITE_URL', 'https://tukarkuy.web.id');
define('BASE_PATH', '/');

// API Configuration - TrackTaco
define('API_BASE_URL', 'https://v2.tracktaco.com');

// API Configuration - QRISPay
define('QRISPAY_API_URL', 'https://api.qrispy.id');

// Payment method configuration
define('PAYMENT_METHODS', [
    'qrispay' => [
        'enabled' => true,
        'name' => 'QRIS Pay',
        'description' => 'Bayar dengan QRIS melalui e-wallet'
    ],
    'saweria' => [
        'enabled' => true,
        'name' => 'Saweria',
        'description' => 'Donasi melalui Saweria'
    ]
]);

// App configuration
define('ITEMS_PER_PAGE', 25);
define('MAX_ITEMS_PER_PAGE', 50);

// Ticket packages (in IDR)
// Format: [credits => [price, bonus, discount_percentage]]
define('TICKET_PACKAGES', [
    5 => ['price' => 250000, 'bonus' => 2, 'total' => 7, 'discount' => 0],
    10 => ['price' => 500000, 'bonus' => 4, 'total' => 14, 'discount' => 0],
    25 => ['price' => 1250000, 'bonus' => 10, 'total' => 35, 'discount' => 0],
    50 => ['price' => 2500000, 'bonus' => 20, 'total' => 70, 'discount' => 0],
    100 => ['price' => 5000000, 'bonus' => 50, 'total' => 150, 'discount' => 0]
]);

// Base price per credit
define('BASE_PRICE_PER_CREDIT', 50000); // Rp 50,000 per credit

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_set_cookie_params([
    'lifetime' => 86400, // 24 hours
    'path' => '/',
    'domain' => '',
    'secure' => false, // set to true in production with HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Helper function to get admin setting
function getAdminSetting($key, $default = null) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT setting_value FROM admin_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

// Get API keys from database (fallback to constants)
function getTrackTacoAPIKey() {
    return getAdminSetting('tracktaco_api_key', 'tt_live_T5w7dupesqnPFQprpV6ozAdE40LKird_BZkrF4TL7dk');
}

function getQRISPayAPIToken() {
    return getAdminSetting('qrispay_api_token', 'cki_PsO8fSC6e1ASeJq9AbTDpcjXjAk1VvvXxjbAl7MqxMr9fEi7');
}

function getSaweriaAPIToken() {
    return getAdminSetting('saweria_api_token', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJjdXJyZW5jeSI6IklEUiIsImlkIjoiNWNjODc1NTItMjUwMC00ZmE5LWFhZmYtYWY1MmM4MTZiZTBhIiwiZW1haWwiOiJtdWhhbW1hZGlsaGFtMTM3MTNAZ21haWwuY29tIiwidXNlcm5hbWUiOiJtaWxoYW02OSIsInRpZXJfa2V5IjoiQkFTSUMiLCJpc3MiOiJzYXdlcmlhLWxvZ2luIiwiaWF0IjoxNzgzMTYxMTc5LCJleHAiOjE3ODM0MjAzNzksImp0aSI6IjIzNTEyMzE5LTk4NjUtNDg2Mi1hMjQ1LWVhOGRjZTM0NTdhZSJ9.aiB1H9S5yo98OzJnx2IPKUch2FiMyq9TU5zVMJAcgdo');
}

// Check if payment method is enabled
function isPaymentMethodEnabled($method) {
    $methods = PAYMENT_METHODS;
    return isset($methods[$method]) && $methods[$method]['enabled'];
}

// Get enabled payment methods
function getEnabledPaymentMethods() {
    $methods = PAYMENT_METHODS;
    return array_filter($methods, function($method) {
        return $method['enabled'];
    });
}
