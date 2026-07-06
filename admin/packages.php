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
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Paket - Admin Tukarkuy</title>
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
                    <a href="/admin/packages" class="nav-item active flex items-center space-x-3 px-4 py-3 rounded-lg">
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
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1">
                
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
                    <h2 class="text-lg font-bold mb-4">Daftar Paket</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-dark-400">
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-400">Kredit</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-400">Harga (IDR)</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-400">Bonus</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-400">Total</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-400">Diskon %</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-400">Status</th>
                                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody id="packages-tbody">
                            <?php foreach ($packages as $pkg): ?>
                            <tr class="border-b border-dark-400 hover:bg-dark-300/50 transition package-row" data-id="<?php echo $pkg['id']; ?>">
                                <td class="py-3 px-4"><?php echo $pkg['credits']; ?></td>
                                <td class="py-3 px-4">
                                    <input type="number" class="w-32 bg-slate-700 border border-slate-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" value="<?php echo $pkg['price']; ?>" data-field="price" onchange="savePackage(<?php echo $pkg['id']; ?>, this)">
                                </td>
                                <td class="py-3 px-4">
                                    <input type="number" class="w-24 bg-slate-700 border border-slate-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" value="<?php echo $pkg['bonus']; ?>" data-field="bonus" onchange="savePackage(<?php echo $pkg['id']; ?>, this)">
                                </td>
                                <td class="py-3 px-4 font-semibold text-purple-400"><?php echo $pkg['total_credits']; ?></td>
                                <td class="py-3 px-4">
                                    <input type="number" class="w-20 bg-slate-700 border border-slate-600 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" value="<?php echo $pkg['discount_percentage']; ?>" data-field="discount_percentage" onchange="savePackage(<?php echo $pkg['id']; ?>, this)">
                                </td>
                                <td class="py-3 px-4">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" class="rounded" <?php echo $pkg['active'] ? 'checked' : ''; ?> onchange="togglePackage(<?php echo $pkg['id']; ?>, this.checked)">
                                        <span class="text-sm"><?php echo $pkg['active'] ? 'Aktif' : 'Nonaktif'; ?></span>
                                    </label>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button onclick="deletePackage(<?php echo $pkg['id']; ?>)" class="text-red-400 hover:text-red-300 transition text-sm">
                                        <i class="fas fa-trash mr-1"></i>Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 pt-6 border-t border-dark-400">
                    <button onclick="openAddPackageModal()" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                        <i class="fas fa-plus mr-2"></i>Tambah Paket
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Add Package Modal -->
    <div id="add-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center">
        <div class="glass-effect rounded-2xl p-8 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-6">Tambah Paket Baru</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Kredit</label>
                    <input type="number" id="new-credits" class="w-full bg-dark-400 border border-dark-400 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500" placeholder="10">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Harga (IDR)</label>
                    <input type="number" id="new-price" class="w-full bg-dark-400 border border-dark-400 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500" placeholder="500000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Bonus</label>
                    <input type="number" id="new-bonus" class="w-full bg-dark-400 border border-dark-400 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500" placeholder="1" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Diskon (%)</label>
                    <input type="number" id="new-discount" class="w-full bg-dark-400 border border-dark-400 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500" placeholder="10" value="0">
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button onclick="closeAddPackageModal()" class="flex-1 bg-dark-400 hover:bg-dark-300 text-white font-medium py-2 rounded-lg transition">Batal</button>
                <button onclick="addPackage()" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-semibold py-2 rounded-lg transition">Tambah</button>
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
            const row = element.closest('tr');

            console.log(`Saving package ${id}: ${field} = ${value}`);

            try {
                const response = await fetch('/admin/api/packages.php?action=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        field: field,
                        value: value
                    })
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);

                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response received:', text.substring(0, 500));
                    
                    // Show detailed error
                    alert(`Error: Server returned HTML instead of JSON.\n\nThis usually means:\n1. The API endpoint is not being reached\n2. There's a PHP error in the API file\n3. .htaccess is redirecting incorrectly\n\nCheck browser console and server logs for details.\n\nResponse preview:\n${text.substring(0, 200)}`);
                    throw new Error('Server returned HTML instead of JSON. Check console for details.');
                }

                const data = await response.json();
                console.log('Response data:', data);

                if (!data.success) {
                    alert('Gagal menyimpan: ' + data.error);
                    location.reload();
                } else {
                    console.log('Saved successfully');
                    // Visual feedback
                    element.style.borderColor = '#10b981';
                    setTimeout(() => {
                        element.style.borderColor = '';
                    }, 1000);
                }
            } catch (error) {
                console.error('Error details:', error);
                alert('Error: ' + error.message + '\n\nCheck browser console for more details.');
            }
        }

        async function togglePackage(id, active) {
            try {
                const response = await fetch('/admin/api/packages.php?action=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        field: 'active',
                        value: active ? 1 : 0
                    })
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
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
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
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        credits: credits,
                        price: price,
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
            if (e.key === 'Escape') {
                closeAddPackageModal();
            }
        });
    </script>

    <script>
        // Setup user menu - close on click and outside click
        setTimeout(() => {
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
        }, 200);
    </script>
</body>
</html>
