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

// Get all users (not admin)
$stmt = $pdo->query("SELECT id, email, first_name, last_name, company, role, tickets, created_at, last_login FROM users WHERE role = 'user' ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Get stats
$totalUsers = count($users);
$totalCredits = array_sum(array_column($users, 'tickets'));
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin Tukarkuy</title>
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
                        <p class="text-xs text-gray-400">User Management</p>
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
                <a href="/admin/users" class="nav-item active flex items-center space-x-3 px-4 py-3 rounded-xl">
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
                <h1 class="text-3xl font-bold mb-2">User Management</h1>
                <p class="text-gray-400">Manage all registered users and their credits.</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo number_format($totalUsers); ?></div>
                    <div class="text-sm text-gray-400">Total Users</div>
                </div>

                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-ticket text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo number_format($totalCredits); ?></div>
                    <div class="text-sm text-gray-400">Total Credits</div>
                </div>

                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-check text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo $totalUsers > 0 ? number_format($totalCredits / $totalUsers, 1) : '0'; ?></div>
                    <div class="text-sm text-gray-400">Avg Credits/User</div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="glass-effect rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold">All Users</h2>
                    <div class="text-sm text-gray-400">
                        <i class="fas fa-info-circle mr-2"></i>Total: <?php echo $totalUsers; ?> users
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">User</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Email</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Company</th>
                                <th class="text-right py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Credits</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Joined</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($totalUsers > 0): ?>
                                <?php foreach ($users as $u): ?>
                                <tr class="border-b border-gray-700/50 hover:bg-white/5 transition">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg flex items-center justify-center">
                                                <span class="text-white font-bold text-sm"><?php echo strtoupper(substr($u['first_name'], 0, 1) . substr($u['last_name'], 0, 1)); ?></span>
                                            </div>
                                            <div>
                                                <div class="font-medium"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></div>
                                                <div class="text-xs text-gray-500">ID: <?php echo $u['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-gray-400 text-sm"><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td class="py-4 px-4 text-gray-400 text-sm"><?php echo htmlspecialchars($u['company'] ?? '—'); ?></td>
                                    <td class="py-4 px-4 text-right">
                                        <span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded-lg bg-purple-500/20 text-purple-400">
                                            <?php echo number_format($u['tickets']); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-gray-500 text-sm">
                                        <?php echo date('d M Y', strtotime($u['created_at'])); ?>
                                    </td>
                                    <td class="py-4 px-4 text-gray-500 text-sm">
                                        <?php echo $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : '<span class="text-gray-600">Never</span>'; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <i class="fas fa-user-slash text-4xl text-gray-600 mb-3"></i>
                                        <div class="text-gray-500">No users registered yet</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
