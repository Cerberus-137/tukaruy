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
$pdo = getDBConnection();

// Get system stats
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM payments");
    $paymentCount = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM ticket_packages");
    $packageCount = $stmt->fetchColumn();
    
    $dbStatus = 'connected';
} catch (Exception $e) {
    $dbStatus = 'error: ' . $e->getMessage();
    $userCount = $paymentCount = $packageCount = 0;
}
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic - Admin Tukarkuy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        .glass-effect {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-item {
            transition: all 0.2s ease;
        }
        .nav-item.active {
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.2), transparent);
            border-left: 4px solid #8b5cf6;
            color: #c084fc;
        }
        .nav-item:hover:not(.active) {
            background: rgba(255, 255, 255, 0.05);
            border-left: 4px solid rgba(139, 92, 246, 0.3);
        }
    </style>
</head>
<body class="text-gray-100 min-h-screen">
    
    <!-- Top Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4 glass-effect border-b border-gray-700">
        <div class="max-w-full mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-shield-halved text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold bg-gradient-to-r from-purple-400 to-blue-400 bg-clip-text text-transparent">Tukarkuy Admin</span>
                        <p class="text-xs text-gray-400">System Diagnostic</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/track" class="flex items-center space-x-2 text-sm text-gray-400 hover:text-white transition px-4 py-2 rounded-lg hover:bg-white/5">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to App</span>
                    </a>
                    <div class="relative">
                        <button id="user-menu-btn" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-white/5 transition">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-blue-600 flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <div class="text-left hidden md:block">
                                <p class="text-sm font-medium"><?php echo htmlspecialchars($user['first_name']); ?></p>
                                <p class="text-xs text-gray-400">Administrator</p>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>
                        <div id="user-menu" class="absolute right-0 mt-2 w-56 glass-effect rounded-xl shadow-2xl hidden z-50 border border-gray-700 overflow-hidden">
                            <div class="p-4 border-b border-gray-700">
                                <p class="text-sm font-medium"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                <p class="text-xs text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <a href="/logout" class="block px-4 py-3 text-sm hover:bg-white/5 transition text-red-400">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Layout -->
    <div class="flex pt-20">
        
        <!-- Sidebar -->
        <aside class="fixed left-0 top-20 bottom-0 w-72 glass-effect border-r border-gray-700 overflow-y-auto">
            <div class="p-6 space-y-2">
                <a href="/admin" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl">
                    <i class="fas fa-chart-line text-lg w-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="/admin/users" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl">
                    <i class="fas fa-users text-lg w-5"></i>
                    <span class="font-medium">Users</span>
                </a>
                <a href="/admin/packages" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl">
                    <i class="fas fa-tag text-lg w-5"></i>
                    <span class="font-medium">Paket Harga</span>
                </a>
                <a href="/admin/payment-methods" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl">
                    <i class="fas fa-credit-card text-lg w-5"></i>
                    <span class="font-medium">Payment Methods</span>
                </a>
                <a href="/admin/payments" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl">
                    <i class="fas fa-money-bill-wave text-lg w-5"></i>
                    <span class="font-medium">Transactions</span>
                </a>
                
                <div class="pt-4 mt-4 border-t border-gray-700">
                    <a href="/admin/diagnostic" class="nav-item active flex items-center space-x-3 px-4 py-3 rounded-xl">
                        <i class="fas fa-stethoscope text-lg w-5"></i>
                        <span class="font-medium">Diagnostic</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="ml-72 flex-1 p-8 min-h-screen">
            
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2 flex items-center">
                    <i class="fas fa-stethoscope text-purple-400 mr-3"></i>
                    System Diagnostic
                </h1>
                <p class="text-gray-400">Monitor system health dan test API endpoints.</p>
            </div>

            <!-- System Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-database text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold mb-1"><?php echo $dbStatus === 'connected' ? 'Connected' : 'Error'; ?></div>
                    <div class="text-sm text-gray-400">Database Status</div>
                </div>

                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo $userCount; ?></div>
                    <div class="text-sm text-gray-400">Users in DB</div>
                </div>

                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-receipt text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo $paymentCount; ?></div>
                    <div class="text-sm text-gray-400">Total Payments</div>
                </div>

                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-box text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo $packageCount; ?></div>
                    <div class="text-sm text-gray-400">Packages</div>
                </div>
            </div>

            <!-- API Tests -->
            <div class="grid grid-cols-1 gap-6 mb-8">
                
                <!-- Test 1: Basic API -->
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold flex items-center">
                            <i class="fas fa-plug text-blue-400 mr-3"></i>
                            Test 1: Basic API Endpoint
                        </h2>
                        <button onclick="testBasicAPI()" class="bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                            <i class="fas fa-play mr-2"></i>Run Test
                        </button>
                    </div>
                    <pre id="test1-result" class="bg-slate-900/50 p-4 rounded-lg text-sm overflow-x-auto min-h-[100px] font-mono"></pre>
                </div>

                <!-- Test 2: Packages API -->
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold flex items-center">
                            <i class="fas fa-box-open text-green-400 mr-3"></i>
                            Test 2: Packages API
                        </h2>
                        <button onclick="testPackagesListAPI()" class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                            <i class="fas fa-play mr-2"></i>Run Test
                        </button>
                    </div>
                    <pre id="test2-result" class="bg-slate-900/50 p-4 rounded-lg text-sm overflow-x-auto min-h-[100px] font-mono"></pre>
                </div>

                <!-- Test 3: Button Clickability -->
                <div class="glass-effect rounded-2xl p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-mouse-pointer text-purple-400 mr-3"></i>
                        Test 3: Button Clickability
                    </h2>
                    <p class="text-gray-400 mb-4 text-sm">Test if buttons respond to click events</p>
                    <button 
                        id="test-ship-date-btn" 
                        onclick="testShipDateButton()" 
                        class="w-full bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-left hover:bg-slate-700 hover:border-purple-500 transition flex items-center justify-between">
                        <span class="text-gray-300">Click me to test</span>
                        <i class="fas fa-calendar-alt text-purple-400"></i>
                    </button>
                    <div id="test3-result" class="mt-4 text-sm"></div>
                </div>

            </div>

            <!-- System Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- File System Check -->
                <div class="glass-effect rounded-2xl p-6">
                    <h2 class="text-xl font-bold mb-6 flex items-center">
                        <i class="fas fa-folder-open text-yellow-400 mr-3"></i>
                        File System Check
                    </h2>
                    <div class="space-y-3">
                        <?php
                        $files = [
                            'admin/api/packages.php' => '../admin/api/packages.php',
                            'admin/api/test-endpoint.php' => '../admin/api/test-endpoint.php',
                            '.htaccess' => '../.htaccess',
                            'assets/js/app.js' => '../assets/js/app.js',
                            'track.php' => '../track.php',
                            'config.php' => '../config.php'
                        ];
                        
                        foreach ($files as $name => $path) {
                            $exists = file_exists(__DIR__ . '/' . $path);
                            $icon = $exists ? 'fa-check-circle text-green-400' : 'fa-times-circle text-red-400';
                            echo "<div class='flex items-center justify-between p-3 bg-slate-800/50 rounded-lg'>";
                            echo "<span class='text-sm font-mono text-gray-300'>$name</span>";
                            echo "<i class='fas $icon'></i>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Server Information -->
                <div class="glass-effect rounded-2xl p-6">
                    <h2 class="text-xl font-bold mb-6 flex items-center">
                        <i class="fas fa-server text-cyan-400 mr-3"></i>
                        Server Information
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between p-3 bg-slate-800/50 rounded-lg">
                            <span class="text-sm text-gray-400">PHP Version:</span>
                            <span class="text-sm font-semibold text-white"><?php echo phpversion(); ?></span>
                        </div>
                        <div class="flex justify-between p-3 bg-slate-800/50 rounded-lg">
                            <span class="text-sm text-gray-400">Server Software:</span>
                            <span class="text-sm font-semibold text-white"><?php echo substr($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown', 0, 30); ?></span>
                        </div>
                        <div class="flex justify-between p-3 bg-slate-800/50 rounded-lg">
                            <span class="text-sm text-gray-400">User Role:</span>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-purple-500/20 text-purple-400">
                                <?php echo strtoupper($user['role']); ?>
                            </span>
                        </div>
                        <div class="flex justify-between p-3 bg-slate-800/50 rounded-lg">
                            <span class="text-sm text-gray-400">Database:</span>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full <?php echo $dbStatus === 'connected' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'; ?>">
                                <?php echo strtoupper($dbStatus); ?>
                            </span>
                        </div>
                    </div>
                </div>

            </div>

        </main>

    </div>

    <script>
        async function testBasicAPI() {
            const result = document.getElementById('test1-result');
            result.textContent = '⏳ Testing...';
            
            try {
                const response = await fetch('/admin/api/test-endpoint.php');
                const contentType = response.headers.get('content-type');
                
                let output = '📋 Response Headers:\n';
                output += `Status: ${response.status} ${response.statusText}\n`;
                output += `Content-Type: ${contentType}\n\n`;
                
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    output += '✅ JSON Response:\n' + JSON.stringify(data, null, 2);
                    result.textContent = output;
                    result.classList.remove('text-red-400');
                    result.classList.add('text-green-400');
                } else {
                    const text = await response.text();
                    output += '❌ Non-JSON Response:\n' + text.substring(0, 500);
                    result.textContent = output;
                    result.classList.remove('text-green-400');
                    result.classList.add('text-red-400');
                }
            } catch (error) {
                result.textContent = '❌ Error: ' + error.message;
                result.classList.add('text-red-400');
            }
        }
        
        async function testPackagesListAPI() {
            const result = document.getElementById('test2-result');
            result.textContent = '⏳ Testing...';
            
            try {
                const response = await fetch('/admin/api/packages.php?action=list');
                const contentType = response.headers.get('content-type');
                
                let output = '📋 Response Headers:\n';
                output += `Status: ${response.status} ${response.statusText}\n`;
                output += `Content-Type: ${contentType}\n\n`;
                
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    output += '✅ JSON Response:\n' + JSON.stringify(data, null, 2);
                    result.textContent = output;
                    result.classList.remove('text-red-400');
                    result.classList.add('text-green-400');
                } else {
                    const text = await response.text();
                    output += '❌ Non-JSON Response:\n' + text.substring(0, 500);
                    result.textContent = output;
                    result.classList.remove('text-green-400');
                    result.classList.add('text-red-400');
                }
            } catch (error) {
                result.textContent = '❌ Error: ' + error.message;
                result.classList.add('text-red-400');
            }
        }
        
        function testShipDateButton() {
            const result = document.getElementById('test3-result');
            result.innerHTML = '<div class="p-3 bg-green-500/10 border border-green-500/30 rounded-lg"><span class="text-green-400">✅ Button is clickable! Event fired successfully.</span></div>';
            
            setTimeout(() => {
                result.innerHTML = '';
            }, 3000);
        }

        // User menu toggle
        document.getElementById('user-menu-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('user-menu').classList.toggle('hidden');
        });

        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('user-menu');
            const userMenuBtn = document.getElementById('user-menu-btn');
            if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
    </script>

</body>
</html>
