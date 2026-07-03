<?php
session_start();
require_once 'config.php';
require_once 'api/TukeruyAPI.php';

$api = new TukeruyAPI();
$stats = $api->getStats();
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tukeruy - Dashboard Pelacakan Pengiriman</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            100: '#1a1a1a',
                            200: '#2a2a2a',
                            300: '#3a3a3a',
                            400: '#4a4a4a',
                        }
                    }
                }
            }
        }
    </script>
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
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-pre-transit {
            background: rgba(168, 85, 247, 0.2);
            color: #c084fc;
        }
        .badge-transit {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }
        .badge-delivered {
            background: rgba(34, 197, 94, 0.2);
            color: #4ade80;
        }
        .filter-btn {
            padding: 8px 16px;
            background: rgba(42, 42, 42, 0.8);
            border: 1px solid rgba(74, 74, 74, 0.5);
            border-radius: 8px;
            font-size: 0.875rem;
            color: #9ca3af;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .filter-btn:hover {
            background: rgba(58, 58, 58, 0.8);
            color: #fff;
        }
        .filter-btn.active {
            background: rgba(139, 92, 246, 0.3);
            border-color: #8b5cf6;
            color: #c084fc;
        }
    </style>
</head>
<body class="bg-black text-gray-100 min-h-screen">
    
    <!-- Top Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4">
        <div class="max-w-[1600px] mx-auto">
            <div class="glass-effect rounded-2xl px-6 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shipping-fast text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-bold">Tukeruy</span>
                    </div>
                    <div class="hidden md:flex items-center space-x-6 text-sm">
                        <a href="#" class="text-white font-medium">Pelacakan</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">Riwayat</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">API</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">Bantuan</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-400">Kredit: <span class="text-white font-semibold" id="credits-display"><?php echo number_format($stats['credits']); ?></span></span>
                    <button class="w-8 h-8 rounded-lg bg-dark-300 hover:bg-dark-400 transition flex items-center justify-center">
                        <i class="fas fa-user text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-24 px-6 pb-6">
        <div class="max-w-[1600px] mx-auto flex gap-6">
            
            <!-- Filter Sidebar -->
            <aside class="w-80">
                <div class="glass-effect rounded-2xl p-6 sticky top-24">
                    <h3 class="font-semibold text-lg mb-6">Filter</h3>
                    
                    <div class="space-y-6">
                        <!-- Kurir -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Kurir</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" class="filter-btn active" data-type="carrier" data-value="all">Semua</button>
                                <button type="button" class="filter-btn" data-type="carrier" data-value="fedex">FedEx</button>
                                <button type="button" class="filter-btn" data-type="carrier" data-value="dhl">DHL</button>
                                <button type="button" class="filter-btn" data-type="carrier" data-value="ups">UPS</button>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Status</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" class="filter-btn" data-type="status" data-value="pre-transit">Pra Kirim</button>
                                <button type="button" class="filter-btn" data-type="status" data-value="transit">Transit</button>
                                <button type="button" class="filter-btn" data-type="status" data-value="delivered">Terkirim</button>
                            </div>
                        </div>

                        <!-- Asal -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Asal Pengiriman</label>
                            <input type="text" id="origin_country" placeholder="Negara (ID, US, GB...)" class="w-full mb-2 bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm">
                            <input type="text" id="origin_city" placeholder="Kota" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm">
                        </div>

                        <!-- Tujuan -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Tujuan</label>
                            <input type="text" id="dest_country" placeholder="Negara (ID, US, GB...)" class="w-full mb-2 bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm">
                            <input type="text" id="dest_city" placeholder="Kota" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm">
                        </div>

                        <!-- Tanggal Kirim -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Tanggal Pengiriman</label>
                            <input type="date" id="ship_from" class="w-full mb-2 bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm">
                            <input type="date" id="ship_to" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm">
                        </div>

                        <!-- Estimasi Tiba -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Estimasi Tiba</label>
                            <input type="date" id="delivery_from" class="w-full mb-2 bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm">
                            <input type="date" id="delivery_to" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm">
                        </div>

                        <button onclick="applyFilters()" class="w-full bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium py-3 rounded-lg transition">
                            <i class="fas fa-filter mr-2"></i>Terapkan Filter
                        </button>

                        <button onclick="resetFilters()" class="w-full bg-dark-300 hover:bg-dark-400 text-white font-medium py-3 rounded-lg transition">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1">
                
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="glass-effect rounded-xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-400 text-sm">Total Resi</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500/20 to-purple-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-purple-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold"><?php echo number_format($stats['total']); ?></div>
                        <div class="text-xs text-gray-500 mt-1">Tersedia</div>
                    </div>

                    <div class="glass-effect rounded-xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-400 text-sm">FedEx</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-truck text-blue-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold"><?php echo number_format($stats['fedex']); ?></div>
                        <div class="text-xs text-gray-500 mt-1">Paket</div>
                    </div>

                    <div class="glass-effect rounded-xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-400 text-sm">DHL</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-yellow-500/20 to-yellow-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shipping-fast text-yellow-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold"><?php echo number_format($stats['dhl']); ?></div>
                        <div class="text-xs text-gray-500 mt-1">Paket</div>
                    </div>

                    <div class="glass-effect rounded-xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-400 text-sm">UPS</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500/20 to-green-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-truck-fast text-green-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold"><?php echo number_format($stats['ups']); ?></div>
                        <div class="text-xs text-gray-500 mt-1">Paket</div>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold">Kandidat</h2>
                        <span class="text-sm text-gray-400" id="result-count">~100 hasil</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-dark-400">
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Kurir</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Status</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Asal</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Tujuan</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Pengiriman</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Berat</th>
                                    <th class="text-right py-3 px-4 text-xs font-medium text-gray-400 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="results-table">
                                <tr>
                                    <td colspan="7" class="text-center py-12">
                                        <i class="fas fa-spinner fa-spin text-3xl text-purple-500"></i>
                                        <div class="mt-3 text-gray-500">Memuat data...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-center">
                        <button id="load-more" onclick="loadMore()" class="hidden text-purple-400 hover:text-purple-300 text-sm font-medium">
                            Muat Lebih Banyak
                        </button>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Reveal Modal -->
    <div id="reveal-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center">
        <div class="glass-effect rounded-2xl p-8 max-w-lg w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Tampilkan Nomor Resi</h3>
            <p class="text-gray-400 mb-6">Ini akan menggunakan 1 kredit untuk menampilkan nomor resi.</p>
            <div id="modal-content" class="mb-6"></div>
            <div class="flex gap-3">
                <button onclick="closeModal()" class="flex-1 bg-dark-300 hover:bg-dark-400 text-white font-medium py-2.5 rounded-lg">
                    Batal
                </button>
                <button id="confirm-btn" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium py-2.5 rounded-lg">
                    Konfirmasi
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
