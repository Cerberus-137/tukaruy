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
                    <span class="text-xl font-bold">Payment Methods</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-20 px-6 pb-6">
        <div class="max-w-[1200px] mx-auto">
            
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
</body>
</html>
