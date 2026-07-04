<?php
/**
 * Saweria API Test Script
 * 
 * This script tests the Saweria API integration directly
 * Run from command line: php test_saweria.php
 * Or access via browser: https://yourdomain.com/test_saweria.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'api/SaweriaAPI.php';

echo "=== Saweria API Test Script ===\n\n";

// Display configuration
$token = getSaweriaAPIToken();
echo "Configuration:\n";
echo "- API URL: https://backend.saweria.co\n";
echo "- Token (first 30 chars): " . substr($token, 0, 30) . "...\n";
echo "- Token length: " . strlen($token) . " chars\n\n";

// Decode JWT to check expiration
$parts = explode('.', $token);
if (count($parts) === 3) {
    $payload = json_decode(base64_decode(str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4, '=', STR_PAD_RIGHT)), true);
    if ($payload) {
        echo "JWT Token Info:\n";
        echo "- Username: " . ($payload['username'] ?? 'N/A') . "\n";
        echo "- Email: " . ($payload['email'] ?? 'N/A') . "\n";
        echo "- Issued at: " . (isset($payload['iat']) ? date('Y-m-d H:i:s', $payload['iat']) : 'N/A') . "\n";
        echo "- Expires at: " . (isset($payload['exp']) ? date('Y-m-d H:i:s', $payload['exp']) : 'N/A') . "\n";
        
        if (isset($payload['exp'])) {
            $now = time();
            $exp = $payload['exp'];
            if ($now < $exp) {
                $hoursLeft = round(($exp - $now) / 3600, 1);
                echo "- Status: ✅ VALID (expires in $hoursLeft hours)\n";
            } else {
                echo "- Status: ❌ EXPIRED\n";
            }
        }
        echo "\n";
    }
}

// Test 1: Get Profile
echo "Test 1: Getting Saweria profile...\n";
try {
    $saweria = new SaweriaAPI();
    $profile = $saweria->getProfile();
    
    echo "✅ Profile retrieved successfully!\n\n";
    echo "Profile Data:\n";
    echo "====================\n";
    print_r($profile);
    echo "====================\n\n";
    
    $username = $profile['username'] ?? $profile['user']['username'] ?? null;
    if ($username) {
        echo "✅ Username: $username\n";
        echo "   Payment URL will be: https://saweria.co/$username\n\n";
    } else {
        echo "❌ Unable to extract username from profile\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Profile retrieval failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "Common causes:\n";
    echo "1. JWT token is expired - need to refresh it\n";
    echo "2. JWT token is invalid - check if copied correctly\n";
    echo "3. Saweria API endpoint changed\n";
    echo "4. Account is not active\n\n";
    
    echo "To fix:\n";
    echo "1. Login to Saweria.co\n";
    echo "2. Go to Settings > API\n";
    echo "3. Generate new token\n";
    echo "4. Update in config.php function getSaweriaAPIToken()\n";
    echo "   or in database table 'admin_settings' key 'saweria_api_token'\n\n";
    
    exit;
}

// Test 2: Generate Payment Link
echo "Test 2: Generating payment link...\n";
$testAmount = 500000; // Rp 500,000
$testMessage = "Test top up - Tukeruy";

try {
    $payment = $saweria->generatePaymentLink($testAmount, $testMessage, 'Test User');
    
    echo "✅ Payment link generated successfully!\n\n";
    echo "Payment Data:\n";
    echo "====================\n";
    print_r($payment);
    echo "====================\n\n";
    
    // Validate
    if (isset($payment['donation_id']) && !empty($payment['donation_id'])) {
        echo "✅ donation_id: " . $payment['donation_id'] . "\n";
    } else {
        echo "❌ donation_id: MISSING\n";
    }
    
    if (isset($payment['payment_url']) && !empty($payment['payment_url'])) {
        echo "✅ payment_url: " . $payment['payment_url'] . "\n";
    } else {
        echo "❌ payment_url: MISSING\n";
    }
    
    if (isset($payment['amount']) && $payment['amount'] == $testAmount) {
        echo "✅ amount: Rp " . number_format($payment['amount']) . "\n";
    } else {
        echo "⚠️  amount: " . ($payment['amount'] ?? 'MISSING') . "\n";
    }
    
    echo "\n";
    echo "Summary:\n";
    echo "- ✅ Saweria API is working correctly\n";
    echo "- Users can make payments via: " . ($payment['payment_url'] ?? 'N/A') . "\n";
    echo "- Payment tracking ID: " . ($payment['donation_id'] ?? 'N/A') . "\n";
    echo "- Note: User must manually enter the amount on Saweria page\n";
    
} catch (Exception $e) {
    echo "❌ Payment link generation failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "This could mean:\n";
    echo "1. Profile endpoint works but payment/donation endpoint doesn't\n";
    echo "2. Saweria API has changed\n";
    echo "3. Account doesn't have permission for this endpoint\n\n";
}

echo "\n=== End of Test ===\n";
