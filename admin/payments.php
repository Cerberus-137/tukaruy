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

$message = '';
$action = $_GET['action'] ?? null;
$paymentId = $_GET['id'] ?? null;

// Handle payment check/update
if ($action === 'check' && $paymentId) {
    $pdo = getDBConnection();
    
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
                // Update payment
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
                // Update payment
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?");
                $stmt->execute([$payment['id']]);
                
                $stmt = $pdo->prepare("UPDATE users SET tickets = tickets + ? WHERE id = ?");
                $stmt->execute([$payment['tickets'], $payment['user_id']]);
                
                $pdo->commit();
                
                $message = "✅ Payment updated! Added {$payment['tickets']} credits.";
            } else {
                $message = "⏳ Saweria payment not yet paid";
            }
        }
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}

// Get pending payments
$pdo = getDBConnection();
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
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-900 text-white">
    
    <div class="min-h-screen p-6">
        <div class="max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-3xl font-bold">Payment Management</h1>
                    <a href="/track" class="bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
                
                <?php if ($message): ?>
                <div class="bg-slate-700 border border-slate-600 rounded-lg p-4 mb-6">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pending Payments -->
            <div class="bg-slate-800 rounded-lg p-6 mb-6 border border-slate-700">
                <h2 class="text-2xl font-bold mb-4">Pending Payments (<?php echo count($pendingPayments); ?>)</h2>
                
                <?php if ($pendingPayments): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-700">
                                <th class="text-left py-3 px-4">Date</th>
                                <th class="text-left py-3 px-4">User</th>
                                <th class="text-left py-3 px-4">Method</th>
                                <th class="text-left py-3 px-4">Amount</th>
                                <th class="text-left py-3 px-4">Credits</th>
                                <th class="text-left py-3 px-4">Reference</th>
                                <th class="text-left py-3 px-4">Status</th>
                                <th class="text-left py-3 px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingPayments as $payment): ?>
                            <tr class="border-b border-slate-700 hover:bg-slate-700/50">
                                <td class="py-3 px-4"><?php echo date('M d, H:i', strtotime($payment['created_at'])); ?></td>
                                <td class="py-3 px-4">
                                    <div class="font-medium"><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></div>
                                    <div class="text-xs text-gray-400"><?php echo htmlspecialchars($payment['email']); ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="bg-slate-700 px-2 py-1 rounded text-xs">
                                        <?php echo strtoupper($payment['payment_method']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">Rp <?php echo number_format($payment['amount']); ?></td>
                                <td class="py-3 px-4"><?php echo number_format($payment['tickets']); ?></td>
                                <td class="py-3 px-4 font-mono text-xs"><?php echo htmlspecialchars($payment['payment_reference']); ?></td>
                                <td class="py-3 px-4">
                                    <span class="bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded text-xs">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <a href="?action=check&id=<?php echo $payment['id']; ?>" class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-xs transition inline-block">
                                        <i class="fas fa-sync mr-1"></i>Check
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-gray-400">No pending payments.</p>
                <?php endif; ?>
            </div>

            <!-- Recent Paid Payments -->
            <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
                <h2 class="text-2xl font-bold mb-4">Recent Paid Payments</h2>
                
                <?php if ($paidPayments): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-700">
                                <th class="text-left py-3 px-4">Paid Date</th>
                                <th class="text-left py-3 px-4">User</th>
                                <th class="text-left py-3 px-4">Method</th>
                                <th class="text-left py-3 px-4">Amount</th>
                                <th class="text-left py-3 px-4">Credits</th>
                                <th class="text-left py-3 px-4">Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paidPayments as $payment): ?>
                            <tr class="border-b border-slate-700 hover:bg-slate-700/50">
                                <td class="py-3 px-4"><?php echo date('M d, H:i', strtotime($payment['paid_at'])); ?></td>
                                <td class="py-3 px-4">
                                    <div class="font-medium"><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></div>
                                    <div class="text-xs text-gray-400"><?php echo htmlspecialchars($payment['email']); ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="bg-slate-700 px-2 py-1 rounded text-xs">
                                        <?php echo strtoupper($payment['payment_method']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">Rp <?php echo number_format($payment['amount']); ?></td>
                                <td class="py-3 px-4"><?php echo number_format($payment['tickets']); ?></td>
                                <td class="py-3 px-4 font-mono text-xs"><?php echo htmlspecialchars($payment['payment_reference']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-gray-400">No paid payments yet.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>
