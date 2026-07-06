<?php
session_start();
require_once '../config.php';
require_once '../auth.php';
require_once '../api/QRISPayAPI.php';
require_once '../api/SaweriaAPI.php';

// Require admin login
requireLogin('/login');
$user = getCurrentUser();
if ($user['role'] !== 'admin') {
    http_response_code(403);
    die('Access denied. Admin only.');
}

$pdo = getDBConnection();
$message = '';
$action = $_GET['action'] ?? null;
$paymentId = $_GET['id'] ?? null;

// Handle payment check/update
if ($action === 'check' && $paymentId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch();
        
        if (!$payment) {
            throw new Exception('Payment not found');
        }
        
        if ($payment['payment_method'] === 'qrispay') {
            $qrisPay = new QRISPayAPI();
            $statusResponse = $qrisPay->checkPaymentStatus($payment['qris_id']);
            $status = isset($statusResponse['status']) ? strtolower($statusResponse['status']) : '';
            
            if ($status === 'paid' || $status === 'success' || $status === 'completed') {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?");
                $stmt->execute([$payment['id']]);
                
                $stmt = $pdo->prepare("UPDATE users SET tickets = tickets + ? WHERE id = ?");
                $stmt->execute([$payment['tickets'], $payment['user_id']]);
                
                $pdo->commit();
                
                $message = "✅ Payment updated! Added {$payment['tickets']} credits.";
            } else {
                $message = "⏳ Payment status: $status (not yet paid)";
            }
        } else if ($payment['payment_method'] === 'saweria') {
            $saweria = new SaweriaAPI();
            
            if ($saweria->isDonationPaid($payment['external_id'])) {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?");
                $stmt->execute([$payment['id']]);
                
                $stmt = $pdo->prepare("UPDATE users SET tickets = tickets + ? WHERE id = ?");
                $stmt->execute([$payment['tickets'], $payment['user_id']]);
                
                $pdo->commit();
                
                $message = "✅ Payment updated! Added {$payment['tickets']} credits.";
            } else {
                $message = "⏳ Payment not yet paid";
            }
        }
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}

// Get payment stats
$stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'");
$pendingCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'paid'");
$paidCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'paid'");
$totalRevenue = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$pendingToday = $stmt->fetchColumn();

// Get pending payments
$stmt = $pdo->prepare("
    SELECT p.*, u.email, u.first_name, u.last_name
    FROM payments p
    JOIN users u ON p.user_id = u.id
    WHERE p.status = 'pending'
    ORDER BY p.created_at DESC
");
$stmt->execute();
$pendingPayments = $stmt->fetchAll();

// Get recent paid payments
$stmt = $pdo->prepare("
    SELECT p.*, u.email, u.first_name, u.last_name
    FROM payments p
    JOIN users u ON p.user_id = u.id
    WHERE p.status = 'paid'
    ORDER BY p.paid_at DESC
    LIMIT 20
");
$stmt->execute();
$paidPayments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Admin Tukarkuy</title>
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
                        <p class="text-xs text-gray-400">Transaction Management</p>
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
                <a href="/admin/payments" class="nav-item active flex items-center space-x-3 px-4 py-3 rounded-xl">
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
                <h1 class="text-3xl font-bold mb-2">Transactions</h1>
                <p class="text-gray-400">Monitor dan kelola semua transaksi pembayaran.</p>
            </div>

            <?php if ($message): ?>
            <div class="glass-effect rounded-2xl p-6 mb-6 border border-<?php echo strpos($message, '✅') !== false ? 'green' : (strpos($message, '⏳') !== false ? 'yellow' : 'red'); ?>-500/30 bg-<?php echo strpos($message, '✅') !== false ? 'green' : (strpos($message, '⏳') !== false ? 'yellow' : 'red'); ?>-500/10">
                <p class="text-sm"><?php echo $message; ?></p>
            </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo $pendingCount; ?></div>
                    <div class="text-sm text-gray-400">Pending Payments</div>
                </div>

                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo $paidCount; ?></div>
                    <div class="text-sm text-gray-400">Paid Payments</div>
                </div>

                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold mb-1">Rp <?php echo number_format($totalRevenue / 1000); ?>K</div>
                    <div class="text-sm text-gray-400">Total Revenue</div>
                </div>

                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-hourglass-half text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo $pendingToday; ?></div>
                    <div class="text-sm text-gray-400">Pending (24h)</div>
                </div>
            </div>

            <!-- Pending Payments Section -->
            <div class="glass-effect rounded-2xl p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold flex items-center">
                        <i class="fas fa-clock text-yellow-400 mr-3"></i>
                        Pending Payments (<?php echo $pendingCount; ?>)
                    </h2>
                </div>
                
                <?php if ($pendingPayments): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Date</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">User</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Method</th>
                                <th class="text-right py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Amount</th>
                                <th class="text-right py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Credits</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Reference</th>
                                <th class="text-center py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingPayments as $payment): ?>
                            <tr class="border-b border-gray-700/50 hover:bg-white/5 transition">
                                <td class="py-4 px-4 text-sm text-gray-400">
                                    <?php echo date('d M Y, H:i', strtotime($payment['created_at'])); ?>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-medium text-sm"><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($payment['email']); ?></div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md bg-blue-500/20 text-blue-400">
                                        <?php echo $payment['payment_method'] === 'qrispay' ? 'QRIS' : 'QRIS 2'; ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right font-semibold">Rp <?php echo number_format($payment['amount']); ?></td>
                                <td class="py-4 px-4 text-right text-purple-400 font-semibold"><?php echo number_format($payment['tickets']); ?></td>
                                <td class="py-4 px-4 text-xs font-mono text-gray-500"><?php echo htmlspecialchars($payment['payment_reference']); ?></td>
                                <td class="py-4 px-4 text-center">
                                    <a href="?action=check&id=<?php echo $payment['id']; ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white text-sm font-semibold rounded-lg transition">
                                        <i class="fas fa-sync mr-2"></i>Check Status
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-check-circle text-5xl text-green-500/30 mb-4"></i>
                    <p class="text-gray-400">No pending payments. All clear!</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Paid Payments Section -->
            <div class="glass-effect rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold flex items-center">
                        <i class="fas fa-check-circle text-green-400 mr-3"></i>
                        Recent Paid Payments
                    </h2>
                </div>
                
                <?php if ($paidPayments): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Paid Date</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">User</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Method</th>
                                <th class="text-right py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Amount</th>
                                <th class="text-right py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Credits</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paidPayments as $payment): ?>
                            <tr class="border-b border-gray-700/50 hover:bg-white/5 transition">
                                <td class="py-4 px-4 text-sm text-gray-400">
                                    <?php echo date('d M Y, H:i', strtotime($payment['paid_at'])); ?>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-medium text-sm"><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($payment['email']); ?></div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md bg-green-500/20 text-green-400">
                                        <?php echo $payment['payment_method'] === 'qrispay' ? 'QRIS' : 'QRIS 2'; ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right font-semibold">Rp <?php echo number_format($payment['amount']); ?></td>
                                <td class="py-4 px-4 text-right text-purple-400 font-semibold"><?php echo number_format($payment['tickets']); ?></td>
                                <td class="py-4 px-4 text-xs font-mono text-gray-500"><?php echo htmlspecialchars($payment['payment_reference']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-5xl text-gray-500/30 mb-4"></i>
                    <p class="text-gray-400">No paid payments yet.</p>
                </div>
                <?php endif; ?>
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

        // Auto-refresh page every 30 seconds for pending payments
        <?php if ($pendingCount > 0): ?>
        setTimeout(() => location.reload(), 30000);
        <?php endif; ?>
    </script>

</body>
</html>
