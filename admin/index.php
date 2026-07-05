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
    <title>Admin Panel - Tukarkuy</title>
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
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4 border-b border-dark-400">
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
                    <div class="relative group">
                        <button class="w-8 h-8 rounded-lg bg-dark-300 hover:bg-dark-400 transition flex items-center justify-center">
                            <i class="fas fa-user text-sm"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-dark-200 border border-dark-400 rounded-lg shadow-lg hidden group-hover:block z-50">
                            <div class="p-3 border-b border-dark-400">
                                <p class="text-sm font-medium"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                <p class="text-xs text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <a href="/logout" class="block px-3 py-2 text-sm hover:bg-dark-300 transition text-red-400">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Layout -->
    <div class="pt-20 px-6 pb-6">
        <div class="max-w-[1600px] mx-auto flex gap-6">
            
            <!-- Sidebar -->
            <aside class="w-64">
                <div class="glass-effect rounded-2xl p-4 sticky top-24 space-y-2">
                    <a href="/admin" class="nav-item active flex items-center space-x-3 px-4 py-3 rounded-lg">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="/admin/packages" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-dark-400 transition">
                        <i class="fas fa-tag"></i>
                        <span>Paket Harga</span>
                    </a>
                    <a href="/admin/payment-methods" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-dark-400 transition">
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
                    <a href="/admin/settings" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-dark-400 transition">
                        <i class="fas fa-cog"></i>
                        <span>Pengaturan</span>
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1">
                
                <!-- Welcome Section -->
                <div class="glass-effect rounded-2xl p-8 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold mb-2">Selamat Datang, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                            <p class="text-gray-400">Kelola sistem Tukarkuy dari sini</p>
                        </div>
                        <div class="text-5xl opacity-10">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="glass-effect rounded-xl p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-400 text-sm">Total Users</span>
                            <i class="fas fa-users text-2xl text-blue-400 opacity-20"></i>
                        </div>
                        <div class="text-3xl font-bold" id="stat-users">-</div>
                        <p class="text-xs text-gray-500 mt-2">Pengguna terdaftar</p>
                    </div>

                    <div class="glass-effect rounded-xl p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-400 text-sm">Total Revenue</span>
                            <i class="fas fa-money-bill text-2xl text-green-400 opacity-20"></i>
                        </div>
                        <div class="text-3xl font-bold" id="stat-revenue">-</div>
                        <p class="text-xs text-gray-500 mt-2">Dari pembayaran berhasil</p>
                    </div>

                    <div class="glass-effect rounded-xl p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-400 text-sm">Pending Payments</span>
                            <i class="fas fa-hourglass text-2xl text-yellow-400 opacity-20"></i>
                        </div>
                        <div class="text-3xl font-bold" id="stat-pending">-</div>
                        <p class="text-xs text-gray-500 mt-2">Menunggu konfirmasi</p>
                    </div>

                    <div class="glass-effect rounded-xl p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-400 text-sm">Credits Issued</span>
                            <i class="fas fa-ticket text-2xl text-purple-400 opacity-20"></i>
                        </div>
                        <div class="text-3xl font-bold" id="stat-credits">-</div>
                        <p class="text-xs text-gray-500 mt-2">Total kredit terjual</p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="glass-effect rounded-2xl p-6">
                    <h3 class="text-lg font-bold mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="/admin/packages" class="p-4 bg-dark-300 hover:bg-dark-400 rounded-lg transition flex items-center space-x-3">
                            <i class="fas fa-tag text-xl text-purple-400"></i>
                            <div>
                                <p class="font-medium">Kelola Paket</p>
                                <p class="text-xs text-gray-500">Edit harga & bonus</p>
                            </div>
                        </a>
                        <a href="/admin/payment-methods" class="p-4 bg-dark-300 hover:bg-dark-400 rounded-lg transition flex items-center space-x-3">
                            <i class="fas fa-credit-card text-xl text-blue-400"></i>
                            <div>
                                <p class="font-medium">Payment Methods</p>
                                <p class="text-xs text-gray-500">Aktifkan/matikan metode</p>
                            </div>
                        </a>
                        <a href="/admin/users" class="p-4 bg-dark-300 hover:bg-dark-400 rounded-lg transition flex items-center space-x-3">
                            <i class="fas fa-users text-xl text-green-400"></i>
                            <div>
                                <p class="font-medium">Lihat Users</p>
                                <p class="text-xs text-gray-500">Manage pengguna</p>
                            </div>
                        </a>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        // Load dashboard stats
        async function loadStats() {
            try {
                const response = await fetch('/admin/api/stats');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('stat-users').textContent = data.total_users || 0;
                    document.getElementById('stat-revenue').textContent = 'Rp ' + (data.total_revenue ? new Intl.NumberFormat('id-ID').format(data.total_revenue) : '0');
                    document.getElementById('stat-pending').textContent = data.pending_payments || 0;
                    document.getElementById('stat-credits').textContent = data.total_credits || 0;
                }
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', loadStats);
    </script>
</body>
</html>
