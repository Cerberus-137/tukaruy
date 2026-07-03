<?php
session_start();
require_once 'config.php';
require_once 'api/TukeruyAPI.php';

$api = new TukeruyAPI();
$stats = $api->getStats();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tukeruy - Shipment Tracking Dashboard</title>
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
        .badge-exception {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }
        .sidebar-collapsed {
            width: 60px;
        }
        .sidebar-expanded {
            width: 280px;
        }
        .carrier-btn, .status-btn {
            padding: 8px 12px;
            background: rgba(42, 42, 42, 0.8);
            border: 1px solid rgba(74, 74, 74, 0.5);
            border-radius: 8px;
            font-size: 0.875rem;
            color: #9ca3af;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .carrier-btn:hover, .status-btn:hover {
            background: rgba(58, 58, 58, 0.8);
            color: #fff;
        }
        .carrier-btn.active, .status-btn.active {
            background: rgba(139, 92, 246, 0.3);
            border-color: #8b5cf6;
            color: #c084fc;
        }
        .toggle-switch {
            appearance: none;
            width: 44px;
            height: 24px;
            background: #3a3a3a;
            border-radius: 12px;
            position: relative;
            cursor: pointer;
            transition: background 0.3s;
        }
        .toggle-switch:checked {
            background: #8b5cf6;
        }
        .toggle-switch::before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            background: white;
            border-radius: 50%;
            top: 3px;
            left: 3px;
            transition: transform 0.3s;
        }
        .toggle-switch:checked::before {
            transform: translateX(20px);
        }
        .country-item, .city-item {
            transition: background-color 0.15s ease;
        }
        .country-item:hover, .city-item:hover {
            background-color: rgba(58, 58, 58, 0.8);
        }
        #origin-country-dropdown, #dest-country-dropdown,
        #origin-city-dropdown, #dest-city-dropdown {
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-black text-gray-100 min-h-screen">
    
    <!-- Floating Top Navigation -->
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
                        <a href="#" class="text-white font-medium">Tracking</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">History</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">API</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">Support</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-400">Credits: <span class="text-white font-semibold" id="credits-display"><?php echo number_format($stats['credits']); ?></span></span>
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
            
            <!-- Collapsible Filter Sidebar -->
            <aside id="sidebar" class="sidebar-expanded transition-all duration-300">
                <div class="glass-effect rounded-2xl p-6 sticky top-24 h-fit">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-semibold text-sm" id="filter-title">Filters</h3>
                        <button onclick="toggleSidebar()" class="w-8 h-8 rounded-lg hover:bg-dark-300 transition flex items-center justify-center">
                            <i class="fas fa-bars text-sm"></i>
                        </button>
                    </div>
                    
                    <div id="filter-content" class="space-y-6">
                        <!-- Carrier Filter -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Carrier</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" class="carrier-btn active" data-value="all" onclick="toggleCarrier(this)">All</button>
                                <button type="button" class="carrier-btn" data-value="fedex" onclick="toggleCarrier(this)">FedEx</button>
                                <button type="button" class="carrier-btn" data-value="dhl" onclick="toggleCarrier(this)">DHL</button>
                                <button type="button" class="carrier-btn" data-value="ups" onclick="toggleCarrier(this)">UPS</button>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Status</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" class="status-btn" data-value="pre-transit" onclick="toggleStatus(this)">Pre Transit</button>
                                <button type="button" class="status-btn" data-value="transit" onclick="toggleStatus(this)">Transit</button>
                                <button type="button" class="status-btn" data-value="delivered" onclick="toggleStatus(this)">Delivered</button>
                            </div>
                        </div>

                        <!-- Asal/Pengirim -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Asal Pengiriman</label>
                            
                            <!-- Country Selector -->
                            <div class="relative mb-2">
                                <input type="text" id="origin-country-display" readonly placeholder="Semua negara" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 pr-20 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer" onclick="toggleCountryDropdown('origin')">
                                <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center space-x-1">
                                    <button type="button" onclick="clearOriginCountry()" class="w-6 h-6 hover:bg-dark-400 rounded flex items-center justify-center">
                                        <i class="fas fa-times text-xs text-gray-500"></i>
                                    </button>
                                    <button type="button" class="w-6 h-6 hover:bg-dark-400 rounded flex items-center justify-center">
                                        <i class="fas fa-sync text-xs text-gray-500"></i>
                                    </button>
                                </div>
                                
                                <!-- Country Dropdown -->
                                <div id="origin-country-dropdown" class="hidden absolute z-10 w-full mt-1 bg-dark-200 border border-dark-400 rounded-lg shadow-xl max-h-80 overflow-hidden">
                                    <div class="p-2">
                                        <div class="relative">
                                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-xs"></i>
                                            <input type="text" id="origin-search" placeholder="Cari negara..." class="w-full bg-dark-300 border border-dark-400 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" onkeyup="filterCountries('origin')">
                                        </div>
                                    </div>
                                    <div id="origin-country-list" class="overflow-y-auto max-h-60">
                                        <!-- Countries populated by JS -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- City Selector -->
                            <div class="relative">
                                <input type="text" id="origin-city-display" readonly placeholder="Semua kota" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 pr-20 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer" onclick="toggleCityDropdown('origin')">
                                <div class="absolute right-2 top-1/2 transform -translate-y-1/2">
                                    <button type="button" class="w-6 h-6 hover:bg-dark-400 rounded flex items-center justify-center">
                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                    </button>
                                </div>
                                
                                <!-- City Dropdown -->
                                <div id="origin-city-dropdown" class="hidden absolute z-10 w-full mt-1 bg-dark-200 border border-dark-400 rounded-lg shadow-xl max-h-80 overflow-hidden">
                                    <div class="p-2">
                                        <div class="relative">
                                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-xs"></i>
                                            <input type="text" id="origin-city-search" placeholder="Cari kota..." class="w-full bg-dark-300 border border-dark-400 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" onkeyup="filterCities('origin')">
                                        </div>
                                    </div>
                                    <div id="origin-city-list" class="overflow-y-auto max-h-60">
                                        <div class="text-center text-gray-500 text-sm py-4">Pilih negara terlebih dahulu</div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="origin_country" name="origin_country">
                            <input type="hidden" id="origin_city" name="origin_city">
                        </div>

                        <!-- Ship Date Window -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Tanggal Pengiriman</label>
                            <div class="relative">
                                <input type="date" name="ship_from" placeholder="Semua tanggal" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <span class="text-xs text-gray-500 mt-1 block">sampai</span>
                                <input type="date" name="ship_to" class="w-full mt-1 bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>

                        <!-- Destination -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Tujuan</label>
                            
                            <!-- Country Selector -->
                            <div class="relative mb-2">
                                <input type="text" id="dest-country-display" readonly placeholder="Semua negara" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 pr-20 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer" onclick="toggleCountryDropdown('dest')">
                                <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center space-x-1">
                                    <button type="button" onclick="clearDestCountry()" class="w-6 h-6 hover:bg-dark-400 rounded flex items-center justify-center">
                                        <i class="fas fa-times text-xs text-gray-500"></i>
                                    </button>
                                    <button type="button" class="w-6 h-6 hover:bg-dark-400 rounded flex items-center justify-center">
                                        <i class="fas fa-sync text-xs text-gray-500"></i>
                                    </button>
                                </div>
                                
                                <!-- Country Dropdown -->
                                <div id="dest-country-dropdown" class="hidden absolute z-10 w-full mt-1 bg-dark-200 border border-dark-400 rounded-lg shadow-xl max-h-80 overflow-hidden">
                                    <div class="p-2">
                                        <div class="relative">
                                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-xs"></i>
                                            <input type="text" id="dest-search" placeholder="Cari negara..." class="w-full bg-dark-300 border border-dark-400 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" onkeyup="filterCountries('dest')">
                                        </div>
                                    </div>
                                    <div id="dest-country-list" class="overflow-y-auto max-h-60">
                                        <!-- Countries populated by JS -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- City Selector -->
                            <div class="relative">
                                <input type="text" id="dest-city-display" readonly placeholder="Semua kota" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer" onclick="toggleCityDropdown('dest')">
                                <div class="absolute right-2 top-1/2 transform -translate-y-1/2">
                                    <button type="button" class="w-6 h-6 hover:bg-dark-400 rounded flex items-center justify-center">
                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                    </button>
                                </div>
                                
                                <!-- City Dropdown -->
                                <div id="dest-city-dropdown" class="hidden absolute z-10 w-full mt-1 bg-dark-200 border border-dark-400 rounded-lg shadow-xl max-h-80 overflow-hidden left-0">
                                    <div class="p-2">
                                        <div class="relative">
                                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-xs"></i>
                                            <input type="text" id="dest-city-search" placeholder="Cari kota..." class="w-full bg-dark-300 border border-dark-400 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" onkeyup="filterCities('dest')">
                                        </div>
                                    </div>
                                    <div id="dest-city-list" class="overflow-y-auto max-h-60">
                                        <div class="text-center text-gray-500 text-sm py-4">Pilih negara terlebih dahulu</div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="dest_country" name="dest_country">
                            <input type="hidden" id="dest_city" name="dest_city">
                            <input type="hidden" name="dest_zip">
                            <input type="hidden" name="dest_state">
                        </div>

                        <!-- Est Delivery Window -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Estimasi Pengiriman</label>
                            <div class="relative">
                                <input type="date" name="delivery_from" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <span class="text-xs text-gray-500 mt-1 block">sampai</span>
                                <input type="date" name="delivery_to" class="w-full mt-1 bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Label pre-transit belum ada estimasi pengiriman</p>
                        </div>

                        <!-- More Options (Collapsible) -->
                        <div>
                            <button type="button" onclick="toggleMoreOptions()" class="flex items-center justify-between w-full text-xs font-medium text-gray-400 uppercase mb-3">
                                <span>▶ Opsi Lainnya (Berat, Layanan)</span>
                            </button>
                            <div id="more-options" class="hidden space-y-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="number" name="weight_min" placeholder="Min gram" class="bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <input type="number" name="weight_max" placeholder="Max gram" class="bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Options -->
                        <div>
                            <label class="flex items-center justify-between cursor-pointer py-2">
                                <span class="text-sm">Perlu tanda tangan</span>
                                <input type="checkbox" name="signature_required" class="toggle-switch">
                            </label>
                            <label class="flex items-center justify-between cursor-pointer py-2">
                                <span class="text-sm">Foto saat pengiriman</span>
                                <input type="checkbox" name="photo_confirmed" class="toggle-switch">
                            </label>
                        </div>

                        <button onclick="applyFilters()" class="w-full bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium py-2.5 rounded-lg transition">
                            <i class="fas fa-search mr-2"></i>Cari
                        </button>
                        <button onclick="resetFilters()" class="w-full mt-2 bg-dark-300 hover:bg-dark-400 text-white font-medium py-2.5 rounded-lg transition">
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
                            <span class="text-gray-400 text-sm">Total Tracking</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500/20 to-purple-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-purple-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold"><?php echo number_format($stats['total']); ?></div>
                        <div class="text-xs text-gray-500 mt-1">Available numbers</div>
                    </div>

                    <div class="glass-effect rounded-xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-400 text-sm">FedEx</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-truck text-blue-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold"><?php echo number_format($stats['fedex']); ?></div>
                        <div class="text-xs text-gray-500 mt-1">Packages</div>
                    </div>

                    <div class="glass-effect rounded-xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-400 text-sm">DHL</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-yellow-500/20 to-yellow-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shipping-fast text-yellow-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold"><?php echo number_format($stats['dhl']); ?></div>
                        <div class="text-xs text-gray-500 mt-1">Packages</div>
                    </div>

                    <div class="glass-effect rounded-xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-400 text-sm">UPS</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500/20 to-green-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-truck-fast text-green-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold"><?php echo number_format($stats['ups']); ?></div>
                        <div class="text-xs text-gray-500 mt-1">Packages</div>
                    </div>
                </div>

                <!-- Search and Table -->
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
                        <h2 class="text-lg font-semibold">Cari nomor resi</h2>
                        <div class="flex items-center space-x-3">
                            <div class="relative flex-1 md:w-80">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                                <input type="text" id="search-input" placeholder="Cari berdasarkan tujuan, asal..." class="w-full bg-dark-300 border border-dark-400 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <button onclick="searchTracking()" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium px-6 py-2.5 rounded-lg transition whitespace-nowrap">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                        </div>
                    </div>

                    <div class="text-sm text-gray-400 mb-4">
                        <span id="result-count">~100 matches</span>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-dark-400">
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Carrier</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Status</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Origin</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Destination</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Shipment</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Weight</th>
                                    <th class="text-right py-3 px-4 text-xs font-medium text-gray-400 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tracking-results">
                                <tr>
                                    <td colspan="7" class="text-center py-12 text-gray-500">
                                        <i class="fas fa-search text-3xl mb-3"></i>
                                        <div>Gunakan filter dan klik "Cari" untuk menemukan nomor resi</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex items-center justify-center">
                        <button id="load-more-btn" onclick="loadMore()" class="text-purple-400 hover:text-purple-300 text-sm font-medium transition hidden">
                            Load more
                        </button>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Reveal Modal -->
    <div id="reveal-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center">
        <div class="glass-effect rounded-2xl p-8 max-w-lg w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Reveal Tracking Number</h3>
            <p class="text-gray-400 mb-6">This will spend 1 credit to reveal the tracking number.</p>
            <div id="reveal-content" class="mb-6"></div>
            <div class="flex gap-3">
                <button onclick="closeRevealModal()" class="flex-1 bg-dark-300 hover:bg-dark-400 text-white font-medium py-2.5 rounded-lg transition">
                    Cancel
                </button>
                <button id="confirm-reveal-btn" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium py-2.5 rounded-lg transition">
                    Confirm & Reveal
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
