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

// Get all packages
$stmt = $pdo->query("SELECT * FROM ticket_packages ORDER BY order_index ASC");
$packages = $stmt->fetchAll();

// Calculate stats
$totalPackages = count($packages);
$activePackages = count(array_filter($packages, fn($p) => $p['active']));
$totalValue = array_sum(array_column($packages, 'price'));
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Harga - Admin Tukarkuy</title>
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
                        <p class="text-xs text-gray-400">Package Management</p>
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
                <a href="/admin/packages" class="nav-item active flex items-center space-x-3 px-4 py-3 rounded-xl">
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
                <h1 class="text-3xl font-bold mb-2">Paket Harga</h1>
                <p class="text-gray-400">Kelola harga paket kredit dan bonus untuk pengguna.</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-box text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo $totalPackages; ?></div>
                    <div class="text-sm text-gray-400">Total Packages</div>
                </div>

                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold mb-1"><?php echo $activePackages; ?></div>
                    <div class="text-sm text-gray-400">Active Packages</div>
                </div>

                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-coins text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold mb-1">Rp <?php echo number_format($totalValue / 1000); ?>K</div>
                    <div class="text-sm text-gray-400">Total Package Value</div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="glass-effect rounded-2xl p-6 mb-6 border border-blue-500/30 bg-blue-500/10">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-info-circle text-blue-400 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold mb-1">Kelola harga paket kredit Anda</p>
                        <p class="text-sm text-gray-400">Edit harga, bonus, dan diskon untuk setiap paket. Perubahan akan langsung berlaku untuk pengguna baru.</p>
                    </div>
                </div>
            </div>

            <!-- Packages Table -->
            <div class="glass-effect rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold">Daftar Paket</h2>
                    <button onclick="openAddPackageModal()" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-lg">
                        <i class="fas fa-plus mr-2"></i>Tambah Paket
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Kredit</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Harga (IDR)</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Bonus</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Total</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Diskon %</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Status</th>
                                <th class="text-right py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody id="packages-tbody">
                            <?php foreach ($packages as $pkg): ?>
                            <tr class="border-b border-gray-700/50 hover:bg-white/5 transition">
                                <td class="py-4 px-4">
                                    <span class="font-bold text-purple-400"><?php echo $pkg['credits']; ?></span>
                                </td>
                                <td class="py-4 px-4">
                                    <input type="number" class="w-36 bg-slate-700/50 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500 transition" value="<?php echo $pkg['price']; ?>" data-field="price" onchange="savePackage(<?php echo $pkg['id']; ?>, this)">
                                </td>
                                <td class="py-4 px-4">
                                    <input type="number" class="w-24 bg-slate-700/50 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500 transition" value="<?php echo $pkg['bonus']; ?>" data-field="bonus" onchange="savePackage(<?php echo $pkg['id']; ?>, this)">
                                </td>
                                <td class="py-4 px-4">
                                    <span class="font-bold text-green-400"><?php echo $pkg['total_credits']; ?></span>
                                </td>
                                <td class="py-4 px-4">
                                    <input type="number" class="w-20 bg-slate-700/50 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500 transition" value="<?php echo $pkg['discount_percentage']; ?>" data-field="discount_percentage" onchange="savePackage(<?php echo $pkg['id']; ?>, this)">
                                </td>
                                <td class="py-4 px-4">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" class="rounded w-5 h-5" <?php echo $pkg['active'] ? 'checked' : ''; ?> onchange="togglePackage(<?php echo $pkg['id']; ?>, this.checked)">
                                        <span class="text-sm <?php echo $pkg['active'] ? 'text-green-400' : 'text-gray-500'; ?>"><?php echo $pkg['active'] ? 'Aktif' : 'Nonaktif'; ?></span>
                                    </label>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <button onclick="deletePackage(<?php echo $pkg['id']; ?>)" class="text-red-400 hover:text-red-300 transition text-sm px-3 py-1.5 rounded-lg hover:bg-red-500/10">
                                        <i class="fas fa-trash mr-1"></i>Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

    </div>

    <!-- Add Package Modal -->
    <div id="add-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-6">
        <div class="glass-effect rounded-2xl p-8 max-w-md w-full border border-gray-700">
            <h3 class="text-2xl font-bold mb-6">Tambah Paket Baru</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Kredit</label>
                    <input type="number" id="new-credits" class="w-full bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition" placeholder="10">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Harga (IDR)</label>
                    <input type="number" id="new-price" class="w-full bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition" placeholder="500000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Bonus</label>
                    <input type="number" id="new-bonus" class="w-full bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition" placeholder="1" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Diskon (%)</label>
                    <input type="number" id="new-discount" class="w-full bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition" placeholder="10" value="0">
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button onclick="closeAddPackageModal()" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 rounded-lg transition">Batal</button>
                <button onclick="addPackage()" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-semibold py-3 rounded-lg transition">Tambah</button>
            </div>
        </div>
    </div>

    <script>
        function openAddPackageModal() {
            document.getElementById('add-modal').classList.remove('hidden');
            document.getElementById('add-modal').classList.add('flex');
        }

        function closeAddPackageModal() {
            document.getElementById('add-modal').classList.add('hidden');
            document.getElementById('add-modal').classList.remove('flex');
        }

        async function savePackage(id, element) {
            const field = element.dataset.field;
            const value = element.value;

            console.log(`Saving package ${id}: ${field} = ${value}`);

            try {
                const response = await fetch('/admin/api/packages.php?action=update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, field, value })
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned HTML instead of JSON');
                }

                const data = await response.json();

                if (!data.success) {
                    alert('Gagal menyimpan: ' + data.error);
                    location.reload();
                } else {
                    element.style.borderColor = '#10b981';
                    setTimeout(() => { element.style.borderColor = ''; }, 1000);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            }
        }

        async function togglePackage(id, active) {
            try {
                const response = await fetch('/admin/api/packages.php?action=update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, field: 'active', value: active ? 1 : 0 })
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned HTML instead of JSON');
                }

                const data = await response.json();
                if (!data.success) {
                    alert('Gagal: ' + data.error);
                    location.reload();
                }
            } catch (error) {
                alert('Error: ' + error.message);
                location.reload();
            }
        }

        async function deletePackage(id) {
            if (!confirm('Hapus paket ini?')) return;

            try {
                const response = await fetch('/admin/api/packages.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned HTML instead of JSON');
                }

                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal: ' + data.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function addPackage() {
            const credits = document.getElementById('new-credits').value;
            const price = document.getElementById('new-price').value;
            const bonus = document.getElementById('new-bonus').value;
            const discount = document.getElementById('new-discount').value;

            if (!credits || !price) {
                alert('Isi semua field yang diperlukan');
                return;
            }

            try {
                const response = await fetch('/admin/api/packages.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        credits,
                        price,
                        bonus: bonus || 0,
                        discount_percentage: discount || 0
                    })
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned HTML instead of JSON');
                }

                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal: ' + data.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAddPackageModal();
        });

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
