<?php
session_start();
require_once '../config.php';
require_once '../auth.php';

// Require login & admin role
requireLogin('/login.php');
if (getCurrentUser()['role'] !== 'admin') {
    http_response_code(403);
    die('Access denied');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic - Admin Tukarkuy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-black text-gray-100 min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">🔍 System Diagnostic</h1>
        
        <div class="space-y-6">
            <!-- Test 1: Basic API Test -->
            <div class="bg-slate-800 rounded-xl p-6">
                <h2 class="text-xl font-bold mb-4">Test 1: Basic API Endpoint</h2>
                <button onclick="testBasicAPI()" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded">
                    Test Basic API
                </button>
                <pre id="test1-result" class="mt-4 bg-slate-900 p-4 rounded text-sm overflow-x-auto"></pre>
            </div>
            
            <!-- Test 2: Packages API List -->
            <div class="bg-slate-800 rounded-xl p-6">
                <h2 class="text-xl font-bold mb-4">Test 2: Packages API (List)</h2>
                <button onclick="testPackagesListAPI()" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded">
                    Test Packages List
                </button>
                <pre id="test2-result" class="mt-4 bg-slate-900 p-4 rounded text-sm overflow-x-auto"></pre>
            </div>
            
            <!-- Test 3: Ship Date Button -->
            <div class="bg-slate-800 rounded-xl p-6">
                <h2 class="text-xl font-bold mb-4">Test 3: Ship Date Button Clickability</h2>
                <p class="text-gray-400 mb-4">This tests if the ship date button is clickable</p>
                <button 
                    id="test-ship-date-btn" 
                    onclick="testShipDateButton()" 
                    class="w-full max-w-md bg-[#1a1a1a99] border border-[#4a4a4a66] rounded-[10px] px-4 py-3 text-sm text-left transition-all duration-150 hover:bg-[#3a3a3a99] hover:border-[#8b5cf680] flex items-center justify-between"
                    style="cursor: pointer !important; pointer-events: auto !important;">
                    <span class="text-gray-400">Click me to test button</span>
                    <i class="fas fa-calendar-alt text-xs text-purple-400"></i>
                </button>
                <div id="test3-result" class="mt-4 text-sm"></div>
            </div>
            
            <!-- Test 4: File Paths -->
            <div class="bg-slate-800 rounded-xl p-6">
                <h2 class="text-xl font-bold mb-4">Test 4: File Paths Check</h2>
                <div class="space-y-2 text-sm">
                    <?php
                    $files = [
                        'admin/api/packages.php' => '../admin/api/packages.php',
                        'admin/api/test-endpoint.php' => '../admin/api/test-endpoint.php',
                        '.htaccess' => '../.htaccess',
                        'assets/js/app.js' => '../assets/js/app.js',
                        'track.php' => '../track.php'
                    ];
                    
                    foreach ($files as $name => $path) {
                        $exists = file_exists(__DIR__ . '/' . $path);
                        $icon = $exists ? '✅' : '❌';
                        $color = $exists ? 'text-green-400' : 'text-red-400';
                        echo "<div class='$color'>$icon $name: " . ($exists ? 'EXISTS' : 'MISSING') . "</div>";
                    }
                    ?>
                </div>
            </div>
            
            <!-- Test 5: Server Info -->
            <div class="bg-slate-800 rounded-xl p-6">
                <h2 class="text-xl font-bold mb-4">Test 5: Server Information</h2>
                <div class="space-y-2 text-sm">
                    <div><strong>PHP Version:</strong> <?php echo phpversion(); ?></div>
                    <div><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></div>
                    <div><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></div>
                    <div><strong>Request URI:</strong> <?php echo $_SERVER['REQUEST_URI'] ?? 'Unknown'; ?></div>
                    <div><strong>User Role:</strong> <?php echo $user['role']; ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        async function testBasicAPI() {
            const result = document.getElementById('test1-result');
            result.textContent = '⏳ Testing...';
            
            try {
                const response = await fetch('/admin/api/test-endpoint.php');
                const contentType = response.headers.get('content-type');
                
                result.textContent = '📋 Response Headers:\n';
                result.textContent += `Status: ${response.status} ${response.statusText}\n`;
                result.textContent += `Content-Type: ${contentType}\n\n`;
                
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    result.textContent += '✅ JSON Response:\n' + JSON.stringify(data, null, 2);
                } else {
                    const text = await response.text();
                    result.textContent += '❌ Non-JSON Response:\n' + text.substring(0, 500);
                }
            } catch (error) {
                result.textContent = '❌ Error: ' + error.message;
            }
        }
        
        async function testPackagesListAPI() {
            const result = document.getElementById('test2-result');
            result.textContent = '⏳ Testing...';
            
            try {
                const response = await fetch('/admin/api/packages.php?action=list');
                const contentType = response.headers.get('content-type');
                
                result.textContent = '📋 Response Headers:\n';
                result.textContent += `Status: ${response.status} ${response.statusText}\n`;
                result.textContent += `Content-Type: ${contentType}\n\n`;
                
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    result.textContent += '✅ JSON Response:\n' + JSON.stringify(data, null, 2);
                } else {
                    const text = await response.text();
                    result.textContent += '❌ Non-JSON Response:\n' + text.substring(0, 500);
                }
            } catch (error) {
                result.textContent = '❌ Error: ' + error.message;
            }
        }
        
        function testShipDateButton() {
            const result = document.getElementById('test3-result');
            result.innerHTML = '<span class="text-green-400">✅ Button is clickable! Event fired successfully.</span>';
            
            setTimeout(() => {
                result.innerHTML = '';
            }, 3000);
        }
        
        // Add event listener to test button
        document.addEventListener('DOMContentLoaded', function() {
            const testBtn = document.getElementById('test-ship-date-btn');
            if (testBtn) {
                testBtn.addEventListener('click', function(e) {
                    console.log('Test button clicked via event listener');
                }, { capture: true });
            }
        });
    </script>
</body>
</html>
