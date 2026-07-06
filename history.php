<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Require login
requireLogin('/login.php');

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Reveal - Tukarkuy</title>
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
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4 border-b border-gray-800 bg-black/80 backdrop-blur-md">
        <div class="max-w-[1600px] mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-history text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-bold">Riwayat Reveal</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/track" class="text-sm text-gray-400 hover:text-white transition">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Track
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
                            <a href="/logout" class="block px-3 py-2 text-sm hover:bg-slate-700 transition text-red-400">
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
        <div class="max-w-[1400px] mx-auto">
            
            <!-- Summary Card -->
            <div class="glass-effect rounded-2xl p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Riwayat Reveal Anda</h2>
                        <p class="text-gray-400">Tracking number yang sudah Anda beli</p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-purple-400" id="total-reveals">0</div>
                        <div class="text-sm text-gray-500">Total Reveal</div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="glass-effect rounded-2xl p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-dark-400">
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Tracking Number</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Carrier</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Status</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Origin</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Destination</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Ship Date</th>
                                <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase">Revealed At</th>
                            </tr>
                        </thead>
                        <tbody id="history-table">
                            <tr>
                                <td colspan="7" class="text-center py-12">
                                    <i class="fas fa-spinner fa-spin text-3xl text-purple-500"></i>
                                    <div class="mt-3 text-gray-500">Loading history...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex justify-center">
                    <button id="load-more" class="hidden px-6 py-3 bg-purple-600 hover:bg-purple-700 rounded-lg transition">
                        Load More
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        let currentOffset = 0;
        const limit = 50;

        // Load history
        async function loadHistory(append = false) {
            try {
                const response = await fetch(`api/history?limit=${limit}&offset=${currentOffset}`);
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Failed to load history');
                }

                displayHistory(data.history, append);
                
                // Update total
                document.getElementById('total-reveals').textContent = data.total;

                // Update load more button
                const loadMoreBtn = document.getElementById('load-more');
                if (data.history.length >= limit && data.total > currentOffset + limit) {
                    loadMoreBtn.classList.remove('hidden');
                } else {
                    loadMoreBtn.classList.add('hidden');
                }

            } catch (error) {
                console.error('Error loading history:', error);
                const tableBody = document.getElementById('history-table');
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                            <div class="mt-3 text-red-400">Error: ${error.message}</div>
                        </td>
                    </tr>
                `;
            }
        }

        // Display history
        function displayHistory(history, append = false) {
            const tableBody = document.getElementById('history-table');
            
            if (!append) {
                tableBody.innerHTML = '';
            }

            if (history.length === 0 && !append) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <i class="fas fa-inbox text-3xl text-gray-500"></i>
                            <div class="mt-3 text-gray-500">Belum ada riwayat reveal</div>
                        </td>
                    </tr>
                `;
                return;
            }

            history.forEach(item => {
                const row = document.createElement('tr');
                row.className = 'border-b border-dark-400 hover:bg-dark-300/30 transition';
                
                const revealedDate = new Date(item.revealed_at);
                const formattedDate = revealedDate.toLocaleString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                row.innerHTML = `
                    <td class="py-4 px-4">
                        <span class="font-mono text-sm text-purple-400">${item.tracking_number}</span>
                    </td>
                    <td class="py-4 px-4">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md bg-blue-500/20 text-blue-400">
                            ${item.carrier.toUpperCase()}
                        </span>
                    </td>
                    <td class="py-4 px-4">
                        <span class="text-sm ${getStatusColor(item.status)}">${formatStatus(item.status)}</span>
                    </td>
                    <td class="py-4 px-4 text-sm text-gray-300">${item.origin}</td>
                    <td class="py-4 px-4 text-sm text-gray-300">${item.destination}</td>
                    <td class="py-4 px-4 text-sm text-gray-400">${item.ship_date || '—'}</td>
                    <td class="py-4 px-4 text-sm text-gray-500">${formattedDate}</td>
                `;

                tableBody.appendChild(row);
            });
        }

        // Format status
        function formatStatus(status) {
            const statusMap = {
                'pre-transit': 'Pre Transit',
                'transit': 'Transit',
                'delivered': 'Delivered'
            };
            return statusMap[status] || status;
        }

        // Get status color
        function getStatusColor(status) {
            const colorMap = {
                'pre-transit': 'text-purple-400',
                'transit': 'text-blue-400',
                'delivered': 'text-green-400'
            };
            return colorMap[status] || 'text-gray-400';
        }

        // Load more
        document.getElementById('load-more').addEventListener('click', function() {
            currentOffset += limit;
            loadHistory(true);
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

        // Load on page load
        loadHistory();
    </script>
</body>
</html>
