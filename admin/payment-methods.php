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

// Get all payment methods
$stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY sort_order ASC");
$methods = $stmt->fetchAll();

// Calculate stats
$enabledCount = count(array_filter($methods, fn($m) => $m['enabled']));

// Get API settings from database
$stmt = $pdo->query("SELECT setting_key, setting_value FROM admin_settings WHERE setting_key IN ('qrispay_api_token', 'saweria_api_token')");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$qrisToken = $settings['qrispay_api_token'] ?? '';
$qris2Token = $settings['saweria_api_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Methods - Admin Tukarkuy</title>
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
                        <p class="text-xs text-gray-400">Payment Methods</p>
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
                <a href="/admin/payment-methods" class="nav-item active flex items-center space-x-3 px-4 py-3 rounded-xl">
                    <i class="fas fa-credit-card text-lg w-5"></i>
                    <span class="font-medium">Payment Methods</span>
                </a>
                <a href="/admin/payments" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl">
                    <i class="fas fa-money-bill-wave text-lg w-5"></i>
                    <span class="font-medium">Transactions</span>
                </a>
                
                <div class="pt-4 mt-4 border-t border-gray-700">
                    <a href="/admin/diagnostic" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl">
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
                <h1 class="text-3xl font-bold mb-2">Payment Methods</h1>
                <p class="text-gray-400">Kelola metode pembayaran yang tersedia untuk pengguna.</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-credit-card text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo count($methods); ?></div>
                    <div class="text-sm text-gray-400">Total Methods</div>
                </div>

                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo $enabledCount; ?></div>
                    <div class="text-sm text-gray-400">Active Methods</div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="glass-effect rounded-2xl p-6 mb-6 border border-blue-500/30 bg-blue-500/10">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-info-circle text-blue-400 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold mb-1">Kelola metode pembayaran</p>
                        <p class="text-sm text-gray-400">Aktifkan atau matikan metode pembayaran yang tersedia untuk pengguna. Hanya metode yang aktif yang akan ditampilkan di halaman checkout.</p>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <?php foreach ($methods as $method): 
                    // Replace brand names for display
                    $displayName = $method['display_name'];
                    if ($method['method_name'] === 'qrispay') {
                        $displayName = 'QRIS';
                    } elseif ($method['method_name'] === 'saweria') {
                        $displayName = 'QRIS 2';
                    }
                ?>
                <div class="glass-effect rounded-2xl p-6 hover:border-purple-500/50 transition">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-purple-500/20 to-blue-600/20 rounded-xl flex items-center justify-center">
                                <i class="fas fa-<?php echo $method['icon']; ?> text-3xl text-purple-400"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold"><?php echo htmlspecialchars($displayName); ?></h3>
                                <p class="text-xs text-gray-500 font-mono"><?php echo htmlspecialchars($method['method_name']); ?></p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" <?php echo $method['enabled'] ? 'checked' : ''; ?> onchange="togglePaymentMethod(<?php echo $method['id']; ?>, this.checked)">
                            <div class="w-14 h-7 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>

                    <p class="text-sm text-gray-400 mb-4"><?php echo htmlspecialchars($method['description']); ?></p>

                    <div class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl">
                        <span class="text-sm text-gray-400">Status:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo $method['enabled'] ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'; ?>">
                            <?php echo $method['enabled'] ? '✓ Aktif' : '✗ Nonaktif'; ?>
                        </span>
                    </div>

                    <?php if ($method['method_name'] === 'qrispay'): ?>
                    <div class="mt-4 text-xs text-yellow-400 p-3 bg-yellow-500/10 rounded-lg border border-yellow-500/20">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Maximum transaction: Rp 499.000
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- API Configuration Section -->
            <div class="glass-effect rounded-2xl p-6">
                <h3 class="text-xl font-bold mb-6 flex items-center">
                    <i class="fas fa-key text-purple-400 mr-3"></i>
                    API Configuration
                </h3>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">QRIS API Token</label>
                        <div class="flex gap-3">
                            <input type="password" id="qrispay-token" value="<?php echo htmlspecialchars(substr($qrisToken, 0, 20)); ?>..." class="flex-1 bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition" placeholder="cki_...">
                            <button onclick="saveApiSetting('qrispay_api_token', 'qrispay-token')" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                                <i class="fas fa-save mr-2"></i>Simpan
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">API token untuk QRIS payment gateway (Primary)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">QRIS 2 API Token</label>
                        <div class="flex gap-3">
                            <input type="password" id="saweria-token" value="<?php echo htmlspecialchars(substr($qris2Token, 0, 20)); ?>..." class="flex-1 bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition" placeholder="eyJ...">
                            <button onclick="saveApiSetting('saweria_api_token', 'saweria-token')" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                                <i class="fas fa-save mr-2"></i>Simpan
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">API token untuk QRIS payment gateway (Secondary)</p>
                    </div>
                </div>
            </div>

        </main>

    </div>

    <script>
        async function togglePaymentMethod(id, enabled) {
            try {
                const response = await fetch('/admin/api/payment-methods.php?action=update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, enabled: enabled ? 1 : 0 })
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned HTML instead of JSON');
                }

                const data = await response.json();
                if (!data.success) {
                    alert('Gagal: ' + data.error);
                    location.reload();
                } else {
                    setTimeout(() => location.reload(), 500);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            }
        }

        async function saveApiSetting(key, inputId) {
            const value = document.getElementById(inputId).value;
            
            if (!value) {
                alert('Isi API token terlebih dahulu');
                return;
            }

            try {
                const response = await fetch('/admin/api/settings.php?action=update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ key, value })
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned HTML instead of JSON');
                }

                const data = await response.json();
                if (data.success) {
                    alert('✅ Tersimpan!');
                } else {
                    alert('Gagal: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            }
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
