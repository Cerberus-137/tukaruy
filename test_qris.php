<?php
/**
 * QRIS API Test Script
 * 
 * This script tests the QRIS Pay API integration directly
 * Run from command line: php test_qris.php
 * Or access via browser: https://yourdomain.com/test_qris.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'api/QRISPayAPI.php';

echo "=== QRIS API Test Script ===\n\n";

// Display configuration
echo "Configuration:\n";
echo "- API URL: " . QRISPAY_API_URL . "\n";
echo "- API Token: " . substr(getQRISPayAPIToken(), 0, 20) . "...\n\n";

// Test amount
$testAmount = 50000; // Rp 50,000
$testReference = 'TEST-' . time();
$testReturnUrl = SITE_URL . '/test.php';

echo "Test Parameters:\n";
echo "- Amount: Rp " . number_format($testAmount) . "\n";
echo "- Reference: $testReference\n";
echo "- Return URL: $testReturnUrl\n\n";

echo "Calling QRIS API...\n\n";

try {
    $qris = new QRISPayAPI();
    $result = $qris->generateQRIS($testAmount, $testReference, $testReturnUrl);
    
    echo "✅ SUCCESS!\n\n";
    echo "Response:\n";
    echo "====================\n";
    print_r($result);
    echo "====================\n\n";
    
    // Validate required fields
    echo "Validation:\n";
    
    if (isset($result['qris_id']) && !empty($result['qris_id'])) {
        echo "✅ qris_id: " . $result['qris_id'] . "\n";
    } else {
        echo "❌ qris_id: MISSING OR EMPTY\n";
    }
    
    if (isset($result['qris_image_url']) && !empty($result['qris_image_url'])) {
        echo "✅ qris_image_url: " . $result['qris_image_url'] . "\n";
    } else {
        echo "❌ qris_image_url: MISSING OR EMPTY\n";
        echo "   This is the problem! QR code cannot be displayed.\n";
    }
    
    if (isset($result['amount']) && $result['amount'] == $testAmount) {
        echo "✅ amount: " . $result['amount'] . "\n";
    } else {
        echo "⚠️  amount: " . ($result['amount'] ?? 'MISSING') . " (expected: $testAmount)\n";
    }
    
    if (isset($result['expired_at']) && !empty($result['expired_at'])) {
        echo "✅ expired_at: " . $result['expired_at'] . "\n";
    } else {
        echo "⚠️  expired_at: MISSING OR EMPTY\n";
    }
    
    echo "\n";
    
    // Check if QR image is accessible
    if (isset($result['qris_image_url']) && !empty($result['qris_image_url'])) {
        echo "Testing QR Image URL accessibility...\n";
        
        $ch = curl_init($result['qris_image_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            echo "✅ QR Image URL is accessible (HTTP $httpCode)\n";
        } else {
            echo "❌ QR Image URL returned HTTP $httpCode\n";
        }
    }
    
    echo "\n";
    echo "Summary:\n";
    echo "- The API call succeeded\n";
    if (isset($result['qris_image_url']) && !empty($result['qris_image_url'])) {
        echo "- QR code should display correctly\n";
        echo "- You can use this payment in production\n";
    } else {
        echo "- ⚠️  QR image URL is missing\n";
        echo "- Contact QRIS Pay support to fix this\n";
        echo "- Provide them with the response above\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR!\n\n";
    echo "Error Message: " . $e->getMessage() . "\n\n";
    echo "What to check:\n";
    echo "1. Verify QRIS Pay API token is correct in config.php\n";
    echo "2. Check if API endpoint URL is correct: " . QRISPAY_API_URL . "\n";
    echo "3. Verify your QRIS Pay account is active\n";
    echo "4. Check server error logs for more details\n";
    echo "5. Contact QRIS Pay support with the error message above\n";
}

echo "\n=== End of Test ===\n";
