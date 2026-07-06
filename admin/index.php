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

// Get dashboard stats
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'paid'");
$totalPayments = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'paid'");
$totalRevenue = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'");
$pendingPayments = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(tickets) FROM users WHERE role = 'user'");
$totalTicketsInCirculation = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM ticket_usage");
$totalTicketsUsed = $stmt->fetchColumn();

// Recent users
$stmt = $pdo->query("SELECT id, email, first_name, last_name, tickets, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
$recentUsers = $stmt->fetchAll();

// Recent payments
$stmt = $pdo->query("
    SELECT p.*, u.email, u.first_name, u.last_name 
    FROM payments p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$recentPayments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tukarkuy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(139, 92, 246, 0.3);
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
                        <p class="text-xs text-gray-400">Admin Dashboard</p>
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
                            <a href="/admin/settings" class="block px-4 py-3 text-sm hover:bg-white/5 transition">
                                <i class="fas fa-cog mr-2 text-gray-400"></i>Settings
                            </a>
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
                <a href="/admin" class="nav-item active flex items-center space-x-3 px-4 py-3 rounded-xl">
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
                    <p class="text-xs font-semibold text-gray-500 uppercase px-4 mb-2">Content</p>
                    <a href="/admin/blog" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl">
                        <i class="fas fa-newspaper text-lg w-5"></i>
                        <span class="font-medium">Blog Articles</span>
                    </a>
                    <a href="/admin/contact" class="nav-item flex items-center space-x-3 px-4 py-3 rounded-xl">
                        <i class="fas fa-envelope text-lg w-5"></i>
                        <span class="font-medium">Contact Messages</span>
                    </a>
                </div>
                
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
            
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>! 👋</h1>
                <p class="text-gray-400">Here's what's happening with your platform today.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <!-- Total Users -->
                <div class="stat-card glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <span class="text-xs text-green-400 bg-green-500/10 px-2 py-1 rounded-full">Active</span>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo number_format($totalUsers); ?></div>
                    <div class="text-sm text-gray-400">Total Users</div>
                </div>

                <!-- Total Revenue -->
                <div class="stat-card glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-white text-xl"></i>
                        </div>
                        <span class="text-xs text-green-400 bg-green-500/10 px-2 py-1 rounded-full">+12%</span>
                    </div>
                    <div class="text-3xl font-bold mb-1">Rp <?php echo number_format($totalRevenue / 1000); ?>K</div>
                    <div class="text-sm text-gray-400">Total Revenue</div>
                </div>

                <!-- Credits in Circulation -->
                <div class="stat-card glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-ticket text-white text-xl"></i>
                        </div>
                        <span class="text-xs text-purple-400 bg-purple-500/10 px-2 py-1 rounded-full">Live</span>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo number_format($totalTicketsInCirculation); ?></div>
                    <div class="text-sm text-gray-400">Credits in Circulation</div>
                </div>

                <!-- Pending Payments -->
                <div class="stat-card glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                        <span class="text-xs text-orange-400 bg-orange-500/10 px-2 py-1 rounded-full">Pending</span>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo number_format($pendingPayments); ?></div>
                    <div class="text-sm text-gray-400">Pending Payments</div>
                </div>

            </div>

            <!-- Two Columns Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Recent Users -->
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Recent Users</h2>
                        <a href="/admin/users" class="text-sm text-purple-400 hover:text-purple-300 transition">View All →</a>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($recentUsers as $u): ?>
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl hover:bg-white/10 transition">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg flex items-center justify-center">
                                    <span class="text-white font-bold text-sm"><?php echo strtoupper(substr($u['first_name'], 0, 1) . substr($u['last_name'], 0, 1)); ?></span>
                                </div>
                                <div>
                                    <p class="font-medium text-sm"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo htmlspecialchars($u['email']); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-purple-400"><?php echo number_format($u['tickets']); ?> credits</p>
                                <p class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($u['created_at'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Recent Payments</h2>
                        <a href="/admin/payments" class="text-sm text-purple-400 hover:text-purple-300 transition">View All →</a>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($recentPayments as $p): ?>
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl hover:bg-white/10 transition">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-<?php echo $p['payment_method'] === 'saweria' ? 'heart' : 'qrcode'; ?> text-white"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-sm"><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo $p['tickets']; ?> credits</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold">Rp <?php echo number_format($p['amount']); ?></p>
                                <span class="inline-block text-xs px-2 py-1 rounded-full <?php 
                                    echo $p['status'] === 'paid' ? 'bg-green-500/20 text-green-400' : 
                                        ($p['status'] === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400');
                                ?>"><?php echo ucfirst($p['status']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

        </main>

    </div>

    <script>
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
