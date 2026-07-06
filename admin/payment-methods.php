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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .glass-effect {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-item {
            transition: all 0.2s ease;
        }
        .nav-item.active {
            background: rgba(139, 92, 246, 0.2);
            border-left: 3px solid #8b5cf6;
            color: #c084fc;
        }
    </style>
</head>
<body class="bg-black text-gray-100 min-h-screen">
    
    <!-- Top Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4 border-b border-gray-800 bg-black/80 backdrop-blur-md">
        <div class="max-w-[1600px] mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white text-sm"></i>
                    </div>
                    <span class="text-xl font-bold">Tukarkuy Admin</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/track" class="text-sm text-gray-400 hover:text-white transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to App
                    </a>
                    <div class="relative">
                        <button id="user-menu-btn" class="w-8 h-8 rounded-lg bg-dark-300 hover:bg-dark-400 transition flex items-center justify-center">
                            <i class="fas fa-user text-sm"></i>
                        </button>
                        <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-slate-800 border border-slate-700 rounded-lg shadow-lg hidden z-50">
                            <div class="p-3 border-b border-slate-700">
                                <p class="text-sm font-medium"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                <p class="text-xs text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <a href="/logout" class="block px-3 py-2 text-sm hover:bg-slate-700 transition text-red-400 user-menu-link">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-20 px-6 pb-6">
        <div class="max-w-[1600px] mx-auto flex gap-6">
            
            <!-- Sidebar Navigation -->
            <aside class="w-64">
                <div class="glass-effect rounded-2xl p-4 sticky top-24 space-y-2">
                    <a href="/admin" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-dark-400 transition">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="/admin/packages" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-dark-400 transition">
                        <i class="fas fa-tag"></i>
                        <span>Paket Harga</span>
                    </a>
                    <a href="/admin/payment-methods" class="nav-item active flex items-center space-x-3 px-4 py-3 rounded-lg">
                        <i class="fas fa-credit-card"></i>
                        <span>Payment Method</span>
                    </a>
                    <a href="/admin/payments" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-dark-400 transition">
                        <i class="fas fa-money-bill"></i>
                        <span>Transaksi</span>
                    </a>
                    <a href="/admin/users" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-dark-400 transition">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1">
            
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

            <!-- Payment Methods -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($methods as $method): ?>
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-dark-400 rounded-lg flex items-center justify-center">
                                <i class="fas fa-<?php echo $method['icon']; ?> text-xl text-purple-400"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold"><?php echo htmlspecialchars($method['display_name']); ?></h3>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($method['method_name']); ?></p>
                            </div>
                        </div>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" class="rounded" <?php echo $method['enabled'] ? 'checked' : ''; ?> onchange="togglePaymentMethod(<?php echo $method['id']; ?>, this.checked)">
                        </label>
                    </div>

                    <p class="text-sm text-gray-400 mb-4"><?php echo htmlspecialchars($method['description']); ?></p>

                    <div class="space-y-3 mb-4 p-3 bg-dark-400/50 rounded-lg">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Status:</span>
                            <span class="<?php echo $method['enabled'] ? 'text-green-400' : 'text-gray-500'; ?>">
                                <?php echo $method['enabled'] ? '✓ Aktif' : '✗ Nonaktif'; ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($method['method_name'] === 'qrispay'): ?>
                    <div class="text-xs text-gray-500 p-3 bg-yellow-500/10 rounded-lg border border-yellow-500/20">
                        <p><i class="fas fa-exclamation-triangle mr-2"></i>QRIS memiliki batas maksimal Rp 499.000 per transaksi</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Settings -->
            <div class="glass-effect rounded-2xl p-6 mt-6">
                <h3 class="text-lg font-bold mb-4">API Configuration</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">QRISPay API Token</label>
                        <input type="password" id="qrispay-token" class="w-full bg-dark-400 border border-dark-400 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500" placeholder="cki_...">
                        <button onclick="saveApiSetting('qrispay_api_token', 'qrispay-token')" class="mt-2 bg-dark-300 hover:bg-dark-400 text-white px-4 py-2 rounded transition text-sm">Simpan</button>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Saweria API Token</label>
                        <input type="password" id="saweria-token" class="w-full bg-dark-400 border border-dark-400 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500" placeholder="eyJ...">
                        <button onclick="saveApiSetting('saweria_api_token', 'saweria-token')" class="mt-2 bg-dark-300 hover:bg-dark-400 text-white px-4 py-2 rounded transition text-sm">Simpan</button>
                    </div>
                </div>
            </div>

                </div>

            </main>
        </div>
    </div>

    <script>
        async function togglePaymentMethod(id, enabled) {
            try {
                const response = await fetch('/admin/api/payment-methods/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        enabled: enabled ? 1 : 0
                    })
                });

                const data = await response.json();
                if (!data.success) {
                    alert('Gagal: ' + data.error);
                    location.reload();
                } else {
                    // Reload to show updated status
                    setTimeout(() => location.reload(), 500);
                }
            } catch (error) {
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
                const response = await fetch('/admin/api/settings/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        key: key,
                        value: value
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Tersimpan!');
                } else {
                    alert('Gagal: ' + data.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
    </script>

    <script>
        // Setup user menu - close on click and outside click
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuBtn = document.getElementById('user-menu-btn');
            const userMenu = document.getElementById('user-menu');
            const userMenuLinks = document.querySelectorAll('.user-menu-link');
            
            if (!userMenuBtn || !userMenu) return;
            
            // Toggle menu on button click
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userMenu.classList.toggle('hidden');
            });
            
            // Close menu when clicking on a link
            userMenuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    setTimeout(() => {
                        userMenu.classList.add('hidden');
                    }, 50);
                });
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
