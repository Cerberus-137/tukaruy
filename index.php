<?php
session_start();
require_once 'config.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Track Numbers & Package Tracking Platform</title>
    <meta name="description" content="Get instant access to tracking numbers from FedEx, UPS, DHL and more. Advanced search filters, global coverage, and secure tracking.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .gradient-text { background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #312e81 100%); }
        .blob { animation: blob 7s infinite; }
        @keyframes blob { 0%, 100% { transform: translate(0px, 0px) scale(1); } 33% { transform: translate(30px, -50px) scale(1.1); } 66% { transform: translate(-20px, 20px) scale(0.9); } }
        .float { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
    </style>
</head>
<body class="bg-slate-50">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-xl border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-shipping-fast text-white text-lg"></i>
                    </div>
                    <span class="text-2xl font-bold gradient-text">Tukarkuy</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-700 hover:text-purple-600 font-medium transition">Features</a>
                    <a href="#pricing" class="text-gray-700 hover:text-purple-600 font-medium transition">Pricing</a>
                    <a href="/blog" class="text-gray-700 hover:text-purple-600 font-medium transition">Blog</a>
                    <a href="/contact" class="text-gray-700 hover:text-purple-600 font-medium transition">Contact</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="/track" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition shadow-lg">
                            Dashboard
                        </a>
                    <?php else: ?>
                        <a href="/login" class="text-gray-700 hover:text-gray-900 font-semibold transition">Login</a>
                        <a href="/register" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition shadow-lg">
                            Get Started
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 hero-gradient relative overflow-hidden">
        <!-- Animated background blobs -->
        <div class="absolute top-10 left-10 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 blob"></div>
        <div class="absolute top-20 right-10 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 blob" style="animation-delay: 2s;"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 blob" style="animation-delay: 4s;"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-white">
                    <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-md rounded-full mb-6 border border-white/20">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                        <span class="text-sm font-medium">🚀 Fast, Secure & Reliable</span>
                    </div>
                    <h1 class="text-5xl lg:text-7xl font-black mb-6 leading-tight">
                        Track Numbers<br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-400">Made Easy</span>
                    </h1>
                    <p class="text-xl text-gray-300 mb-8 leading-relaxed">
                        Get instant access to tracking numbers from major carriers worldwide. Advanced search, real-time tracking, and detailed package information at your fingertips.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/register" class="group relative inline-flex items-center justify-center px-8 py-4 bg-white text-purple-600 font-bold rounded-xl transition hover:scale-105 shadow-2xl">
                            <span>Start Free Trial</span>
                            <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition"></i>
                        </a>
                        <a href="#features" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white/30 hover:border-white/50 text-white font-bold rounded-xl transition hover:bg-white/10">
                            <i class="fas fa-play-circle mr-2"></i>
                            Watch Demo
                        </a>
                    </div>
                    <div class="mt-8 flex items-center space-x-6 text-sm">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 mr-2"></i>
                            <span>No credit card required</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 mr-2"></i>
                            <span>Cancel anytime</span>
                        </div>
                    </div>
                </div>
                <div class="relative float">
                    <div class="relative z-10 bg-white/10 backdrop-blur-xl rounded-3xl p-8 border border-white/20 shadow-2xl">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-purple-500/20 backdrop-blur-md rounded-xl border border-purple-400/30">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-truck text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-white">FedEx Express</p>
                                        <p class="text-sm text-purple-200">NEW YORK, NY</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-green-500 text-white rounded-full text-xs font-bold shadow-lg">Delivered</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-blue-500/20 backdrop-blur-md rounded-xl border border-blue-400/30">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-box text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-white">UPS Ground</p>
                                        <p class="text-sm text-blue-200">LOS ANGELES, CA</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-blue-400 text-white rounded-full text-xs font-bold shadow-lg">In Transit</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-pink-500/20 backdrop-blur-md rounded-xl border border-pink-400/30">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-shipping-fast text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-white">DHL Express</p>
                                        <p class="text-sm text-pink-200">CHICAGO, IL</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-yellow-400 text-gray-900 rounded-full text-xs font-bold shadow-lg">Processing</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-12 bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-black gradient-text mb-2">50K+</div>
                    <div class="text-gray-600 font-medium">Active Users</div>
                </div>
                <div>
                    <div class="text-4xl font-black gradient-text mb-2">1M+</div>
                    <div class="text-gray-600 font-medium">Packages Tracked</div>
                </div>
                <div>
                    <div class="text-4xl font-black gradient-text mb-2">15+</div>
                    <div class="text-gray-600 font-medium">Carriers Supported</div>
                </div>
                <div>
                    <div class="text-4xl font-black gradient-text mb-2">99.9%</div>
                    <div class="text-gray-600 font-medium">Uptime</div>
                </div>
            </div>
        </div>
    </section>

    <!-- What We Offer -->
    <section class="py-20 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-block px-4 py-2 bg-purple-100 text-purple-600 rounded-full text-sm font-bold mb-4">
                    🎯 WHAT WE OFFER
                </div>
                <h2 class="text-5xl font-black mb-4">Track Numbers Database</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Access real tracking numbers from major carriers worldwide with advanced search and filtering capabilities</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-16">
                <div>
                    <h3 class="text-3xl font-bold mb-6">Find Tracking Numbers Instantly</h3>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-search text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg mb-2">Advanced Search Filters</h4>
                                <p class="text-gray-600">Search by destination country, city, state, carrier type (FedEx, UPS, DHL), ship date, delivery date, and more.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-eye text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg mb-2">Reveal System</h4>
                                <p class="text-gray-600">Use credits to reveal full tracking numbers. Each credit reveals one tracking number with complete shipment details.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-database text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg mb-2">Fresh & Accurate Data</h4>
                                <p class="text-gray-600">Our database is constantly updated with real tracking numbers from active shipments worldwide.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
                    <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-2xl p-6 mb-6">
                        <h4 class="font-bold text-lg mb-4 flex items-center">
                            <i class="fas fa-filter text-purple-600 mr-2"></i>
                            Search Example
                        </h4>
                        <div class="space-y-3 text-sm">
                            <div class="bg-white rounded-lg p-3">
                                <span class="text-gray-600">Destination:</span>
                                <span class="font-semibold ml-2">New York, NY, USA</span>
                            </div>
                            <div class="bg-white rounded-lg p-3">
                                <span class="text-gray-600">Carrier:</span>
                                <span class="font-semibold ml-2">FedEx Express</span>
                            </div>
                            <div class="bg-white rounded-lg p-3">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-semibold ml-2 text-green-600">Delivered</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-900 text-green-400 font-mono text-sm rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-500">Results found:</span>
                            <span class="text-white font-bold">142 tracking numbers</span>
                        </div>
                        <div class="text-xs text-gray-500 mb-3">Click to reveal...</div>
                        <div class="space-y-2">
                            <div class="bg-gray-800 rounded px-3 py-2">7861••••••••6789</div>
                            <div class="bg-gray-800 rounded px-3 py-2">7861••••••••3421</div>
                            <div class="bg-gray-800 rounded px-3 py-2">7861••••••••9087</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-block px-4 py-2 bg-purple-100 text-purple-600 rounded-full text-sm font-bold mb-4">
                    ⚡ FEATURES
                </div>
                <h2 class="text-5xl font-black mb-4">Why Choose Tukarkuy?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Everything you need to find and track packages efficiently worldwide</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2 border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition shadow-lg">
                        <i class="fas fa-bolt text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Lightning Fast</h3>
                    <p class="text-gray-600 leading-relaxed">Get instant results with our optimized search engine. Find tracking numbers in milliseconds.</p>
                </div>

                <div class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2 border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition shadow-lg">
                        <i class="fas fa-globe-americas text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Global Coverage</h3>
                    <p class="text-gray-600 leading-relaxed">Access tracking numbers from FedEx, UPS, DHL, USPS and 15+ carriers across 60+ countries worldwide.</p>
                </div>

                <div class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2 border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition shadow-lg">
                        <i class="fas fa-shield-alt text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Bank-Level Security</h3>
                    <p class="text-gray-600 leading-relaxed">Your data is protected with enterprise-grade security protocols and encryption.</p>
                </div>

                <div class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2 border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition shadow-lg">
                        <i class="fas fa-filter text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Advanced Filters</h3>
                    <p class="text-gray-600 leading-relaxed">Filter by country, city, state, carrier, status, date ranges and more for precise results.</p>
                </div>

                <div class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2 border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition shadow-lg">
                        <i class="fas fa-coins text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Flexible Pricing</h3>
                    <p class="text-gray-600 leading-relaxed">Pay only for what you use with our credit-based system. Buy in bulk and save more.</p>
                </div>

                <div class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2 border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition shadow-lg">
                        <i class="fas fa-headset text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">24/7 Support</h3>
                    <p class="text-gray-600 leading-relaxed">Our dedicated support team is always ready to help you via email and chat.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-block px-4 py-2 bg-purple-100 text-purple-600 rounded-full text-sm font-bold mb-4">
                    💰 PRICING
                </div>
                <h2 class="text-5xl font-black mb-4">Simple, Transparent Pricing</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Choose a credit package that fits your needs. No subscriptions, no hidden fees.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto mb-12">
                <!-- Starter -->
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 hover:border-purple-500 transition">
                    <h3 class="text-2xl font-bold mb-2">Starter</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-black">5 Credits</span>
                    </div>
                    <div class="text-gray-600 mb-6">
                        <div class="text-2xl font-bold text-gray-900 mb-4">Rp 250.000</div>
                        <ul class="space-y-3 text-sm">
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>5 tracking reveals</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>All carriers access</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Advanced filters</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Never expires</li>
                        </ul>
                    </div>
                    <a href="/register" class="block text-center bg-gray-900 text-white font-bold py-3 rounded-lg hover:bg-gray-800 transition">
                        Get Started
                    </a>
                </div>

                <!-- Popular -->
                <div class="bg-gradient-to-br from-purple-600 to-blue-600 text-white rounded-2xl p-8 transform scale-105 shadow-2xl relative">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-yellow-400 text-gray-900 text-xs font-bold px-4 py-1 rounded-full">
                        MOST POPULAR
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Pro</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-black">10 Credits</span>
                        <span class="block text-sm opacity-90 mt-2">+1 Bonus Credit</span>
                    </div>
                    <div class="mb-6">
                        <div class="text-2xl font-bold mb-4">Rp 500.000</div>
                        <ul class="space-y-3 text-sm opacity-90">
                            <li class="flex items-center"><i class="fas fa-check mr-2"></i>11 tracking reveals (10+1 bonus)</li>
                            <li class="flex items-center"><i class="fas fa-check mr-2"></i>All carriers access</li>
                            <li class="flex items-center"><i class="fas fa-check mr-2"></i>Advanced filters</li>
                            <li class="flex items-center"><i class="fas fa-check mr-2"></i>Priority support</li>
                            <li class="flex items-center"><i class="fas fa-check mr-2"></i>Never expires</li>
                        </ul>
                    </div>
                    <a href="/register" class="block text-center bg-white text-purple-600 font-bold py-3 rounded-lg hover:bg-gray-100 transition">
                        Get Started
                    </a>
                </div>

                <!-- Business -->
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 hover:border-purple-500 transition">
                    <h3 class="text-2xl font-bold mb-2">Business</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-black">50 Credits</span>
                        <span class="block text-sm text-gray-600 mt-2">+10 Bonus Credits</span>
                    </div>
                    <div class="text-gray-600 mb-6">
                        <div class="text-2xl font-bold text-gray-900 mb-4">Rp 2.500.000</div>
                        <ul class="space-y-3 text-sm">
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>60 tracking reveals (50+10 bonus)</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>All carriers access</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Advanced filters</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Premium support</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Never expires</li>
                        </ul>
                    </div>
                    <a href="/register" class="block text-center bg-gray-900 text-white font-bold py-3 rounded-lg hover:bg-gray-800 transition">
                        Get Started
                    </a>
                </div>
            </div>

            <div class="text-center">
                <p class="text-gray-600 mb-4">Need more credits? <a href="/contact" class="text-purple-600 font-bold hover:text-purple-700">Contact us</a> for custom packages and bulk discounts.</p>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-block px-4 py-2 bg-purple-100 text-purple-600 rounded-full text-sm font-bold mb-4">
                    📖 HOW IT WORKS
                </div>
                <h2 class="text-5xl font-black mb-4">Get Started in 3 Easy Steps</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-3xl font-black text-white">1</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Sign Up & Buy Credits</h3>
                    <p class="text-gray-600">Create your free account and purchase a credit package that fits your needs.</p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-3xl font-black text-white">2</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Search with Filters</h3>
                    <p class="text-gray-600">Use our advanced filters to find tracking numbers matching your exact criteria.</p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-3xl font-black text-white">3</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Reveal & Track</h3>
                    <p class="text-gray-600">Use credits to reveal full tracking numbers and track packages in real-time.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-purple-600 to-blue-600 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-black mb-6">Ready to Get Started?</h2>
            <p class="text-xl opacity-90 mb-8">Join thousands of users who trust Tukarkuy for their tracking needs</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/register" class="bg-white text-purple-600 px-8 py-4 rounded-lg font-bold text-lg hover:bg-gray-100 transition shadow-xl">
                    Create Free Account
                </a>
                <a href="/contact" class="border-2 border-white text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-white/10 transition">
                    Contact Sales
                </a>
            </div>
        </div>
    </section>
