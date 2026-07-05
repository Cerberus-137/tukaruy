<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /track');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Easily Obtain Tracking Numbers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .gradient-text {
            background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-gradient {
            background: radial-gradient(circle at top right, rgba(139, 92, 246, 0.15), transparent 50%),
                        radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.15), transparent 50%);
        }
    </style>
</head>
<body class="bg-white text-gray-900">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shipping-fast text-white"></i>
                    </div>
                    <span class="text-2xl font-bold gradient-text">Tukarkuy</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/login" class="text-gray-600 hover:text-gray-900 font-medium transition">Log In</a>
                    <a href="/register" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 hero-gradient">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                        Easily obtain tracking numbers and track your packages
                    </h1>
                    <p class="text-xl text-gray-600 mb-8">
                        Use our tracking service to quickly track packages and obtain tracking numbers to the city and state or country of the recipient.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/register" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white px-8 py-4 rounded-lg font-semibold text-lg transition text-center">
                            Get Started Free
                        </a>
                        <a href="#features" class="border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-8 py-4 rounded-lg font-semibold text-lg transition text-center">
                            Learn More
                        </a>
                    </div>
                </div>
                <div class="relative">
                    <div class="relative z-10 bg-white rounded-2xl shadow-2xl p-8 border border-gray-200">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-truck text-purple-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold">FedEx Express</p>
                                        <p class="text-sm text-gray-500">AUSTIN, TX</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-medium">Transit</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-shipping-fast text-blue-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold">DHL Express</p>
                                        <p class="text-sm text-gray-500">NEW YORK, NY</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-sm font-medium">Delivered</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-green-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold">UPS Ground</p>
                                        <p class="text-sm text-gray-500">CHICAGO, IL</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-purple-100 text-purple-600 rounded-full text-sm font-medium">Pre-Transit</span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -top-4 -right-4 w-72 h-72 bg-purple-200 rounded-full blur-3xl opacity-50"></div>
                    <div class="absolute -bottom-4 -left-4 w-72 h-72 bg-blue-200 rounded-full blur-3xl opacity-50"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Why Choose Tukarkuy?</h2>
                <p class="text-xl text-gray-600">Fast, easy, and reliable tracking service</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-bolt text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Lightning Fast</h3>
                    <p class="text-gray-600">Get tracking numbers instantly with our high-speed API integration.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-globe text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Global Coverage</h3>
                    <p class="text-gray-600">Track packages from FedEx, UPS, DHL across 60+ countries worldwide.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Secure & Private</h3>
                    <p class="text-gray-600">Your data is encrypted and protected with enterprise-grade security.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition">
                    <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-filter text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Advanced Filters</h3>
                    <p class="text-gray-600">Filter by country, city, carrier, status, and date ranges.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-ticket text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Flexible Pricing</h3>
                    <p class="text-gray-600">Pay-as-you-go with affordable ticket packages. No subscriptions.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-headset text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">24/7 Support</h3>
                    <p class="text-gray-600">Our dedicated support team is here to help you anytime.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Simple, Transparent Pricing</h2>
                <p class="text-xl text-gray-600">Choose the package that fits your needs</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-lg border-2 border-gray-200 hover:border-purple-500 transition">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold mb-2">Starter</h3>
                        <div class="text-4xl font-bold gradient-text mb-2">Rp 50K</div>
                        <p class="text-gray-600">100 Tickets</p>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>100 Tracking Reveals</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>All Carriers</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Advanced Filters</span>
                        </li>
                    </ul>
                    <a href="/register" class="block w-full text-center bg-gray-900 hover:bg-gray-800 text-white py-3 rounded-lg font-semibold transition">
                        Get Started
                    </a>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-blue-600 p-8 rounded-2xl shadow-2xl transform scale-105 relative">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-yellow-400 text-gray-900 px-4 py-1 rounded-full text-sm font-bold">
                        MOST POPULAR
                    </div>
                    <div class="text-center mb-6 text-white">
                        <h3 class="text-2xl font-bold mb-2">Professional</h3>
                        <div class="text-4xl font-bold mb-2">Rp 200K</div>
                        <p>500 Tickets</p>
                    </div>
                    <ul class="space-y-3 mb-8 text-white">
                        <li class="flex items-center">
                            <i class="fas fa-check mr-3"></i>
                            <span>500 Tracking Reveals</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check mr-3"></i>
                            <span>All Carriers</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check mr-3"></i>
                            <span>Advanced Filters</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check mr-3"></i>
                            <span>Priority Support</span>
                        </li>
                    </ul>
                    <a href="/register" class="block w-full text-center bg-white hover:bg-gray-100 text-purple-600 py-3 rounded-lg font-semibold transition">
                        Get Started
                    </a>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-lg border-2 border-gray-200 hover:border-purple-500 transition">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold mb-2">Enterprise</h3>
                        <div class="text-4xl font-bold gradient-text mb-2">Rp 350K</div>
                        <p class="text-gray-600">1000 Tickets</p>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>1000 Tracking Reveals</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>All Carriers</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Advanced Filters</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Priority Support</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Best Value</span>
                        </li>
                    </ul>
                    <a href="/register" class="block w-full text-center bg-gray-900 hover:bg-gray-800 text-white py-3 rounded-lg font-semibold transition">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-purple-600 to-blue-600">
        <div class="max-w-4xl mx-auto px-6 text-center text-white">
            <h2 class="text-4xl font-bold mb-4">Ready to Get Started?</h2>
            <p class="text-xl mb-8 opacity-90">Join thousands of users tracking packages worldwide</p>
            <a href="/register" class="inline-block bg-white hover:bg-gray-100 text-purple-600 px-8 py-4 rounded-lg font-semibold text-lg transition">
                Create Free Account
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-shipping-fast text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-white">Tukarkuy</span>
                    </div>
                    <p class="text-sm">Track packages easily across multiple carriers worldwide.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Product</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="#pricing" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="/track" class="hover:text-white transition">Tracking</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">About</a></li>
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">Privacy</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms</a></li>
                        <li><a href="#" class="hover:text-white transition">Support</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-sm text-center">
                <p>&copy; 2026 Tukarkuy. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
