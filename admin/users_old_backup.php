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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .glass-effect {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-black text-gray-100 min-h-screen">
    
    <!-- Top Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4 border-b border-dark-400">
        <div class="max-w-[1600px] mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="/admin" class="text-gray-400 hover:text-white transition">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <span class="text-xl font-bold">Users</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-20 px-6 pb-6">
        <div class="max-w-[1400px] mx-auto">
            
            <div class="glass-effect rounded-2xl p-6">
                <h2 class="text-lg font-bold mb-4">Daftar Pengguna</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-dark-400">
                                <th class="text-left py-3 px-4 font-semibold">Name</th>
                                <th class="text-left py-3 px-4 font-semibold">Email</th>
                                <th class="text-left py-3 px-4 font-semibold">Company</th>
                                <th class="text-right py-3 px-4 font-semibold">Credits</th>
                                <th class="text-left py-3 px-4 font-semibold">Joined</th>
                                <th class="text-left py-3 px-4 font-semibold">Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $u): ?>
                                <tr class="border-b border-dark-400 hover:bg-dark-300/50 transition">
                                    <td class="py-3 px-4">
                                        <div class="font-medium"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-400"><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td class="py-3 px-4 text-gray-400"><?php echo htmlspecialchars($u['company'] ?? '-'); ?></td>
                                    <td class="py-3 px-4 text-right">
                                        <span class="bg-purple-500/20 text-purple-400 px-3 py-1 rounded-full text-xs font-semibold">
                                            <?php echo number_format($u['tickets']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-500 text-xs">
                                        <?php echo date('d M Y', strtotime($u['created_at'])); ?>
                                    </td>
                                    <td class="py-3 px-4 text-gray-500 text-xs">
                                        <?php echo $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : 'Never'; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-500">
                                        <i class="fas fa-user-slash mr-2"></i>No users yet
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-4 border-t border-dark-400 text-sm text-gray-400">
                    <i class="fas fa-info-circle mr-2"></i>
                    Total Users: <?php echo count($users); ?>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
