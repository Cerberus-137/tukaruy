<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Require login
requireLogin('/login.php');

$user = getCurrentUser();

// Don't call API on page load - stats will be loaded via JavaScript
// This prevents page lag from slow API calls
$stats = [
    'total' => 0,
    'fedex' => 0,
    'dhl' => 0,
    'ups' => 0
];
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tukarkuy - Dashboard Pelacakan Pengiriman</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr for date range picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
        
        /* Modern Segmented Buttons */
        .segmented-btn {
            padding: 12px 16px;
            background: rgba(26, 26, 26, 0.6);
            border: 1.5px solid rgba(74, 74, 74, 0.4);
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #9ca3af;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }
        .segmented-btn:hover {
            background: rgba(58, 58, 58, 0.6);
            border-color: rgba(139, 92, 246, 0.4);
            color: #e5e7eb;
            transform: translateY(-1px);
        }
        .segmented-btn.active {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(59, 130, 246, 0.2));
            border-color: #8b5cf6;
            color: #c084fc;
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
        }
        
        /* Modern Dropdown */
        .modern-dropdown {
            width: 100%;
            background: rgba(26, 26, 26, 0.6);
            border: 1.5px solid rgba(74, 74, 74, 0.4);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.875rem;
            color: #e5e7eb;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }
        .modern-dropdown:hover {
            background: rgba(58, 58, 58, 0.6);
            border-color: rgba(139, 92, 246, 0.5);
        }
        .modern-dropdown:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }
        .modern-dropdown:active {
            transform: scale(0.98);
        }
        
        /* Ship Date Button - Ensure clickability */
        #ship-date-trigger {
            position: relative !important;
            z-index: 10 !important;
            pointer-events: auto !important;
            cursor: pointer !important;
        }
        #ship-date-trigger * {
            pointer-events: none !important; /* Prevent child elements from blocking clicks */
        }
        
        /* Modern Input */
        .modern-input {
            width: 100%;
            background: rgba(26, 26, 26, 0.6);
            border: 1.5px solid rgba(74, 74, 74, 0.4);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.875rem;
            color: #e5e7eb;
            transition: all 0.15s ease;
        }
        .modern-input:hover {
            border-color: rgba(139, 92, 246, 0.5);
        }
        .modern-input:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }
        .modern-input::placeholder {
            color: #6b7280;
        }
        .country-item {
            padding: 8px 12px;
            cursor: pointer;
            transition: background 0.15s ease;
            font-size: 0.875rem;
        }
        .country-item:hover {
            background: rgba(139, 92, 246, 0.2);
        }
        .country-item.selected {
            background: rgba(139, 92, 246, 0.3);
            color: #c084fc;
        }
        #country-dropdown-menu {
            animation: slideDown 0.2s ease-out;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(26, 26, 26, 0.5);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(139, 92, 246, 0.5);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(139, 92, 246, 0.7);
        }
        .loading-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        .badge-info {
            position: fixed;
            top: 100px;
            right: 20px;
            background: rgba(139, 92, 246, 0.9);
            backdrop-filter: blur(10px);
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.3);
            animation: slideInRight 0.3s ease-out;
            z-index: 1000;
        }
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Flatpickr Dark Theme */
        .flatpickr-calendar {
            background: #1a1a1a !important;
            border: 1px solid rgba(139, 92, 246, 0.3) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5) !important;
        }
        .flatpickr-months {
            background: #2a2a2a !important;
        }
        .flatpickr-month {
            color: #fff !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months {
            background: #2a2a2a !important;
            color: #fff !important;
        }
        .flatpickr-current-month input.cur-year {
            color: #fff !important;
        }
        .flatpickr-weekday {
            color: #9ca3af !important;
        }
        .flatpickr-day {
            color: #fff !important;
        }
        .flatpickr-day:hover {
            background: rgba(139, 92, 246, 0.3) !important;
            border-color: rgba(139, 92, 246, 0.5) !important;
        }
        .flatpickr-day.selected {
            background: rgba(139, 92, 246, 0.8) !important;
            border-color: #8b5cf6 !important;
        }
        .flatpickr-day.inRange {
            background: rgba(139, 92, 246, 0.2) !important;
            border: 0 !important;
            box-shadow: none !important;
        }
        .flatpickr-day.disabled {
            color: #4a4a4a !important;
        }
        .flatpickr-day.today {
            border-color: rgba(59, 130, 246, 0.8) !important;
        }
        .numInputWrapper:hover {
            background: rgba(139, 92, 246, 0.1) !important;
        }
        /* Custom ship date count badges */
        .flatpickr-day.has-tn::after {
            content: attr(data-count);
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: rgba(139, 92, 246, 0.8);
            color: #fff;
            font-size: 8px;
            padding: 1px 3px;
            border-radius: 3px;
            font-weight: 600;
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
                        <span class="text-xl font-bold">Tukarkuy</span>
                    </div>
                    <div class="hidden md:flex items-center space-x-6 text-sm">
                        <a href="/track" class="text-white font-medium">Pelacakan</a>
                        <a href="#" class="text-gray-400 hover:text-white transition" onclick="showHistoryModal()">Riwayat</a>
                        <a href="/tickets" class="text-gray-400 hover:text-white transition">Top Up</a>
                        <a href="/settings" class="text-gray-400 hover:text-white transition">Pengaturan</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-400">Tickets: <span class="text-white font-semibold" id="credits-display"><?php echo number_format($user['tickets']); ?></span></span>
                    <div class="relative">
                        <button id="user-menu-btn" class="w-8 h-8 rounded-lg bg-dark-300 hover:bg-dark-400 transition flex items-center justify-center">
                            <i class="fas fa-user text-sm"></i>
                        </button>
                        <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-dark-200 border border-dark-400 rounded-lg shadow-lg hidden z-50">
                            <div class="p-3 border-b border-dark-400">
                                <p class="text-sm font-medium"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                <p class="text-xs text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <a href="/settings" class="block px-3 py-2 text-sm hover:bg-dark-300 transition user-menu-link">
                                <i class="fas fa-cog mr-2"></i>Pengaturan
                            </a>
                            <a href="/tickets" class="block px-3 py-2 text-sm hover:bg-dark-300 transition user-menu-link">
                                <i class="fas fa-ticket mr-2"></i>Top Up
                            </a>
                            <?php if ($user['role'] === 'admin'): ?>
                            <a href="/admin" class="block px-3 py-2 text-sm hover:bg-dark-300 transition text-purple-400 user-menu-link">
                                <i class="fas fa-shield-alt mr-2"></i>Admin Panel
                            </a>
                            <?php endif; ?>
                            <a href="/logout" class="block px-3 py-2 text-sm hover:bg-dark-300 transition text-red-400 user-menu-link">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-24 px-6 pb-6">
        <div class="max-w-[1600px] mx-auto flex gap-6">
            
            <!-- Filter Sidebar - REDESIGNED -->
            <aside class="w-80">
                <div class="glass-effect rounded-2xl p-6 sticky top-24">
                    <h3 class="font-bold text-xl mb-6">FILTERS</h3>
                    
                    <div class="space-y-6">
                        <!-- Carrier - Segmented Buttons -->
                        <div>
                            <label class="text-xs font-bold text-gray-400 mb-3 block uppercase tracking-wider">CARRIER</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" class="segmented-btn active" data-type="carrier" data-value="all">
                                    <i class="fas fa-boxes text-sm mr-2"></i>All
                                </button>
                                <button type="button" class="segmented-btn" data-type="carrier" data-value="fedex">
                                    <i class="fas fa-truck text-sm mr-2"></i>FedEx
                                </button>
                                <button type="button" class="segmented-btn" data-type="carrier" data-value="dhl">
                                    <i class="fas fa-plane text-sm mr-2"></i>DHL
                                </button>
                                <button type="button" class="segmented-btn" data-type="carrier" data-value="ups">
                                    <i class="fas fa-shipping-fast text-sm mr-2"></i>UPS
                                </button>
                            </div>
                        </div>

                        <!-- Status - Segmented Buttons -->
                        <div>
                            <label class="text-xs font-bold text-gray-400 mb-3 block uppercase tracking-wider">STATUS</label>
                            <div class="grid grid-cols-1 gap-2">
                                <button type="button" class="segmented-btn" data-type="status" data-value="pre-transit">
                                    <i class="fas fa-circle text-xs text-purple-400 mr-2"></i>Pre Transit
                                </button>
                                <button type="button" class="segmented-btn" data-type="status" data-value="transit">
                                    <i class="fas fa-circle text-xs text-blue-400 mr-2"></i>Transit
                                </button>
                                <button type="button" class="segmented-btn" data-type="status" data-value="delivered">
                                    <i class="fas fa-circle text-xs text-green-400 mr-2"></i>Delivered
                                </button>
                            </div>
                        </div>

                        <!-- Origin -->
                        <div>
                            <label class="text-xs font-bold text-gray-400 mb-3 block uppercase tracking-wider">ORIGIN</label>
                            <div class="space-y-3">
                                <div class="relative">
                                    <div id="origin-country-dropdown-trigger" class="modern-dropdown">
                                        <span id="selected-origin-country-display" class="text-gray-400">Any country</span>
                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                    </div>
                                    <input type="hidden" id="origin_country" value="">
                                    
                                    <!-- Dropdown Menu -->
                                    <div id="origin-country-dropdown-menu" class="hidden absolute z-10 w-full mt-1 bg-dark-200 border border-dark-400 rounded-lg shadow-lg max-h-64 overflow-hidden">
                                        <div class="p-2 border-b border-dark-400">
                                            <input type="text" id="origin-country-search" placeholder="Search country..." class="w-full bg-dark-300 border border-dark-400 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-purple-500">
                                        </div>
                                        <div id="origin-country-list" class="overflow-y-auto max-h-52">
                                            <!-- Countries will be populated by JS -->
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="relative">
                                    <div id="origin-city-dropdown-trigger" class="modern-dropdown">
                                        <span id="selected-origin-city-display" class="text-gray-400">Any city</span>
                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                    </div>
                                    <input type="hidden" id="origin_city" value="">
                                    
                                    <!-- City Dropdown Menu -->
                                    <div id="origin-city-dropdown-menu" class="hidden absolute z-10 w-full mt-1 bg-dark-200 border border-dark-400 rounded-lg shadow-lg max-h-64 overflow-hidden">
                                        <div class="p-2 border-b border-dark-400">
                                            <input type="text" id="origin-city-search" placeholder="Search city..." class="w-full bg-dark-300 border border-dark-400 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-purple-500">
                                        </div>
                                        <div id="origin-city-list" class="overflow-y-auto max-h-52">
                                            <div class="p-4 text-center text-sm text-gray-500">
                                                Select a country first
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ship Date Window - Enhanced Calendar -->
                        <div>
                            <label class="text-xs font-bold text-gray-400 mb-3 block uppercase tracking-wider flex items-center justify-between">
                                <span><i class="fas fa-calendar-alt mr-2 text-purple-400"></i>SHIP DATE WINDOW</span>
                                <button type="button" onclick="clearShipDateRange()" class="text-xs text-purple-400 hover:text-purple-300 transition" title="Clear">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </label>
                            <div class="relative" style="z-index: 1;">
                                <button 
                                    type="button" 
                                    id="ship-date-trigger" 
                                    onclick="toggleShipDateCalendar()"
                                    class="w-full bg-[#1a1a1a99] border border-[#4a4a4a66] rounded-[10px] px-4 py-3 text-sm text-left transition-all duration-150 hover:bg-[#3a3a3a99] hover:border-[#8b5cf680] focus:outline-none focus:border-purple-500 focus:shadow-[0_0_0_3px_rgba(139,92,246,0.1)] flex items-center justify-between"
                                    style="cursor: pointer !important; pointer-events: auto !important; position: relative; z-index: 10;">
                                    <span id="selected-ship-date-display" class="text-gray-400">Select date range...</span>
                                    <i class="fas fa-calendar-alt text-xs text-purple-400"></i>
                                </button>
                                <input type="hidden" id="ship_from" value="">
                                <input type="hidden" id="ship_to" value="">
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>Click to open calendar with availability
                            </div>
                        </div>

                        <!-- Destination -->
                        <div>
                            <label class="text-xs font-bold text-gray-400 mb-3 block uppercase tracking-wider">DESTINATION</label>
                            <div class="space-y-3">
                                <div class="relative">
                                    <div id="country-dropdown-trigger" class="modern-dropdown">
                                        <span id="selected-country-display">United States (US)</span>
                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                    </div>
                                    <input type="hidden" id="dest_country" value="US">
                                    
                                    <!-- Dropdown Menu -->
                                    <div id="country-dropdown-menu" class="hidden absolute z-10 w-full mt-1 bg-dark-200 border border-dark-400 rounded-lg shadow-lg max-h-64 overflow-hidden">
                                        <div class="p-2 border-b border-dark-400">
                                            <input type="text" id="country-search" placeholder="Search country..." class="w-full bg-dark-300 border border-dark-400 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-purple-500">
                                        </div>
                                        <div id="country-list" class="overflow-y-auto max-h-52">
                                            <!-- Countries will be populated by JS -->
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="relative">
                                    <div id="dest-state-dropdown-trigger" class="modern-dropdown">
                                        <span id="selected-dest-state-display" class="text-gray-400">Any state</span>
                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                    </div>
                                    <input type="hidden" id="dest_state" value="">
                                    
                                    <!-- State Dropdown Menu -->
                                    <div id="dest-state-dropdown-menu" class="hidden absolute z-10 w-full mt-1 bg-dark-200 border border-dark-400 rounded-lg shadow-lg max-h-64 overflow-hidden">
                                        <div class="p-2 border-b border-dark-400">
                                            <input type="text" id="dest-state-search" placeholder="Search state..." class="w-full bg-dark-300 border border-dark-400 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-purple-500">
                                        </div>
                                        <div id="dest-state-list" class="overflow-y-auto max-h-52">
                                            <div class="p-4 text-center text-sm text-gray-500">
                                                Select a country first
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex gap-3">
                                    <div class="flex-1 relative">
                                        <div id="dest-city-dropdown-trigger" class="modern-dropdown">
                                            <span id="selected-dest-city-display" class="text-gray-400">Any city</span>
                                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                        </div>
                                        <input type="hidden" id="dest_city" value="">
                                        
                                        <!-- City Dropdown Menu -->
                                        <div id="dest-city-dropdown-menu" class="hidden absolute z-10 w-full mt-1 bg-dark-200 border border-dark-400 rounded-lg shadow-lg max-h-64 overflow-hidden">
                                            <div class="p-2 border-b border-dark-400">
                                                <input type="text" id="dest-city-search" placeholder="Search city..." class="w-full bg-dark-300 border border-dark-400 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-purple-500">
                                            </div>
                                            <div id="dest-city-list" class="overflow-y-auto max-h-52">
                                                <div class="p-4 text-center text-sm text-gray-500">
                                                    Select a country first
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="w-28">
                                        <input type="text" id="dest_zip" placeholder="ZIP" class="modern-input" maxlength="10">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Est. Delivery Window -->
                        <div>
                            <label class="text-xs font-bold text-gray-400 mb-3 block uppercase tracking-wider">
                                <i class="fas fa-clock mr-2 text-purple-400"></i>EST. DELIVERY WINDOW
                            </label>
                            <div class="relative">
                                <input type="date" id="delivery_from" class="modern-input">
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>Pre-transit labels have no estimate yet
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-4 space-y-3 border-t border-dark-400">
                            <button onclick="applyFilters()" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3.5 rounded-xl transition-all duration-200 flex items-center justify-center shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
                                <i class="fas fa-search mr-2"></i>Search Tracking Numbers
                            </button>
                            
                            <button onclick="resetFilters()" class="w-full bg-dark-300/50 hover:bg-dark-300 text-gray-300 font-medium py-3 rounded-xl transition-all duration-200 flex items-center justify-center border border-dark-400">
                                <i class="fas fa-redo mr-2"></i>Reset Filters
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1">
                
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="glass-effect rounded-xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-400 text-sm">Total Resi</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500/20 to-purple-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-purple-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold mb-1" id="stat-total">
                            <i class="fas fa-spinner fa-spin text-purple-400 text-xl"></i>
                        </div>
                        <div class="text-xs text-gray-500">Tersedia</div>
                    </div>

                    <div class="glass-effect rounded-xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-400 text-sm">FedEx</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-truck text-blue-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold mb-1" id="stat-fedex">
                            <i class="fas fa-spinner fa-spin text-blue-400 text-xl"></i>
                        </div>
                        <div class="text-xs text-gray-500">Paket</div>
                    </div>

                    <div class="glass-effect rounded-xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-400 text-sm">DHL</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-yellow-500/20 to-yellow-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shipping-fast text-yellow-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold mb-1" id="stat-dhl">
                            <i class="fas fa-spinner fa-spin text-yellow-400 text-xl"></i>
                        </div>
                        <div class="text-xs text-gray-500">Paket</div>
                    </div>

                    <div class="glass-effect rounded-xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-400 text-sm">UPS</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500/20 to-green-600/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-truck-fast text-green-400"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold mb-1" id="stat-ups">
                            <i class="fas fa-spinner fa-spin text-green-400 text-xl"></i>
                        </div>
                        <div class="text-xs text-gray-500">Paket</div>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="glass-effect rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Candidates</h2>
                        <span class="text-sm text-gray-400" id="result-count">~384 matches</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-dark-400">
                                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Carrier</th>
                                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Origin</th>
                                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Destination</th>
                                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Shipment</th>
                                    <th class="text-left py-4 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Weight</th>
                                    <th class="text-right py-4 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody id="results-table">
                                <tr>
                                    <td colspan="7" class="text-center py-12">
                                        <i class="fas fa-spinner fa-spin text-3xl text-purple-500"></i>
                                        <div class="mt-3 text-gray-500">Loading data...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-center">
                        <button id="load-more" onclick="loadMore()" class="hidden bg-dark-300 hover:bg-dark-400 text-purple-400 font-medium px-6 py-3 rounded-lg transition">
                            Load more
                        </button>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- History Modal -->
    <div id="history-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center">
        <div class="glass-effect rounded-2xl p-8 max-w-4xl w-full mx-4 max-h-[80vh] overflow-hidden">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold">Riwayat Reveal</h3>
                <button onclick="closeHistoryModal()" class="w-8 h-8 rounded-lg bg-dark-300 hover:bg-dark-400 transition flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="overflow-y-auto max-h-96">
                <div id="history-content">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-3xl text-purple-500"></i>
                        <div class="mt-3 text-gray-500">Loading history...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ship Date Calendar Modal - SIMPLIFIED -->
    <div id="ship-date-calendar-modal" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-dark-200 rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden border border-dark-400">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-dark-400">
                <div>
                    <h3 class="text-xl font-bold">Select Ship Date Range</h3>
                    <p class="text-sm text-gray-400 mt-1">Choose start and end dates</p>
                </div>
                <button onclick="closeShipDateCalendar()" class="w-10 h-10 rounded-lg bg-dark-300 hover:bg-dark-400 transition flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Body - Simple Date Inputs -->
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <!-- From Date -->
                    <div>
                        <label class="text-sm font-semibold text-gray-400 block mb-2">
                            <i class="fas fa-calendar mr-2"></i>From Date
                        </label>
                        <input 
                            type="date" 
                            id="ship-date-from" 
                            class="w-full bg-dark-300 border border-dark-400 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500"
                        />
                    </div>
                    
                    <!-- To Date -->
                    <div>
                        <label class="text-sm font-semibold text-gray-400 block mb-2">
                            <i class="fas fa-calendar mr-2"></i>To Date
                        </label>
                        <input 
                            type="date" 
                            id="ship-date-to" 
                            class="w-full bg-dark-300 border border-dark-400 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500"
                        />
                    </div>
                </div>
                
                <!-- Display Selected Range -->
                <div class="mt-4 p-4 bg-dark-300 rounded-lg border border-purple-500/30">
                    <div class="text-sm text-gray-400">
                        <span id="calendar-selected-range">No date selected</span>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="flex items-center justify-between p-6 border-t border-dark-400">
                <button onclick="closeShipDateCalendar()" class="px-4 py-2 bg-dark-400 hover:bg-dark-300 rounded-lg transition text-sm font-medium">
                    Cancel
                </button>
                <button onclick="applyShipDateRange()" class="px-6 py-2 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 rounded-lg transition text-sm font-semibold">
                    Apply Date Range
                </button>
            </div>
        </div>
    </div>
                </div>
            </div>
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
