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
                            <div class="space-y-2">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="carrier[]" value="all" checked class="w-4 h-4 rounded bg-dark-300 border-dark-400">
                                    <span class="text-sm">All</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="carrier[]" value="fedex" class="w-4 h-4 rounded bg-dark-300 border-dark-400">
                                    <span class="text-sm">FedEx</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="carrier[]" value="dhl" class="w-4 h-4 rounded bg-dark-300 border-dark-400">
                                    <span class="text-sm">DHL</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="carrier[]" value="ups" class="w-4 h-4 rounded bg-dark-300 border-dark-400">
                                    <span class="text-sm">UPS</span>
                                </label>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Status</label>
                            <div class="space-y-2">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="status[]" value="pre-transit" class="w-4 h-4 rounded bg-dark-300 border-dark-400">
                                    <span class="text-sm">Pre Transit</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="status[]" value="transit" class="w-4 h-4 rounded bg-dark-300 border-dark-400">
                                    <span class="text-sm">Transit</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="status[]" value="delivered" class="w-4 h-4 rounded bg-dark-300 border-dark-400">
                                    <span class="text-sm">Delivered</span>
                                </label>
                            </div>
                        </div>

                        <!-- Destination -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Destination</label>
                            <select name="dest_country" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">Any country</option>
                                <option value="US">United States</option>
                                <option value="GB">United Kingdom</option>
                                <option value="CA">Canada</option>
                            </select>
                            <input type="text" name="dest_city" placeholder="City" class="w-full mt-2 bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">Est. Delivery</label>
                            <input type="date" name="delivery_from" class="w-full bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <input type="date" name="delivery_to" class="w-full mt-2 bg-dark-300 border border-dark-400 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <!-- Advanced Options -->
                        <div>
                            <label class="text-xs font-medium text-gray-400 uppercase mb-3 block">More Options</label>
                            <div class="space-y-2">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm">Signature required</span>
                                    <input type="checkbox" name="signature_required" class="toggle-switch">
                                </label>
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm">Photo on delivery</span>
                                    <input type="checkbox" name="photo_confirmed" class="toggle-switch">
                                </label>
                            </div>
                        </div>

                        <button onclick="applyFilters()" class="w-full bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium py-2.5 rounded-lg transition">
                            Apply Filters
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
                        <h2 class="text-lg font-semibold">Find tracking numbers</h2>
                        <div class="flex items-center space-x-3">
                            <div class="relative flex-1 md:w-80">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                                <input type="text" id="search-input" placeholder="Search by destination, origin..." class="w-full bg-dark-300 border border-dark-400 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <button onclick="searchTracking()" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium px-6 py-2.5 rounded-lg transition whitespace-nowrap">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                        </div>
                    </div>

                    <div class="text-sm text-gray-400 mb-4">
                        <span id="result-count">100+ matches</span>
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
                                        <div>Use filters and search to find tracking numbers</div>
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
