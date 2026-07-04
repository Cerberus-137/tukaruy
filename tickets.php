<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Require login
requireLogin('/login.php');

$user = getCurrentUser();
$packages = TICKET_PACKAGES;
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Tickets - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .glass-effect {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .package-card {
            transition: all 0.3s ease;
        }
        .package-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(139, 92, 246, 0.3);
        }
        .package-card.popular {
            border: 2px solid #8b5cf6;
            position: relative;
        }
    </style>
</head>
<body class="bg-black text-gray-100 min-h-screen">
    
    <!-- Top Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4 bg-black/80 backdrop-blur-md border-b border-gray-800">
        <div class="max-w-[1600px] mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <a href="/track.php" class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shipping-fast text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-bold">Tukeruy</span>
                    </a>
                    <div class="hidden md:flex items-center space-x-6 text-sm">
                        <a href="/track.php" class="text-gray-400 hover:text-white transition">Tracking</a>
                        <a href="/tickets.php" class="text-white font-medium">Buy Tickets</a>
                        <a href="/settings.php" class="text-gray-400 hover:text-white transition">Settings</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-400">Tickets: <span class="text-white font-semibold"><?php echo number_format($user['tickets']); ?></span></span>
                    <div class="relative group">
                        <button class="w-8 h-8 rounded-lg bg-dark-300 hover:bg-dark-400 transition flex items-center justify-center">
                            <i class="fas fa-user text-sm"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-slate-800 border border-slate-700 rounded-lg shadow-lg hidden group-hover:block">
                            <a href="/settings.php" class="block px-4 py-2 text-sm hover:bg-slate-700 transition">Settings</a>
                            <a href="/logout.php" class="block px-4 py-2 text-sm text-red-400 hover:bg-slate-700 transition">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-24 px-6 pb-12">
        <div class="max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold mb-4">Billing & Credits</h1>
                <p class="text-xl text-gray-400">Pick a credit pack and start tracking</p>
            </div>

            <!-- Current Balance -->
            <div class="max-w-md mx-auto mb-12">
                <div class="glass-effect rounded-2xl p-8 text-center">
                    <p class="text-sm text-gray-400 mb-2">Current balance</p>
                    <div class="text-5xl font-bold bg-gradient-to-r from-purple-400 to-blue-400 bg-clip-text text-transparent mb-4">
                        <?php echo number_format($user['tickets']); ?>
                    </div>
                    <p class="text-sm text-gray-400">credits</p>
                </div>
            </div>

            <!-- Package Selection -->
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-center mb-4">Pick a credit pack</h2>
                <p class="text-center text-sm text-gray-400 mb-8">
                    <i class="fas fa-info-circle mr-2"></i>
                    QRIS payment has a maximum of Rp 499,000. For larger amounts, use Saweria or contact admin for custom packages.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <?php foreach ($packages as $credits => $package): ?>
                    <div class="package-card glass-effect rounded-2xl p-6 cursor-pointer <?php echo $credits == 10 ? 'popular' : ''; ?>" 
                         onclick="selectPackage(<?php echo $credits; ?>, <?php echo $package['price']; ?>, <?php echo $package['total']; ?>, <?php echo $package['bonus']; ?>)">
                        
                        <?php if ($credits == 10): ?>
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-purple-500 to-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                            MOST POPULAR
                        </div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-4">
                            <?php if ($package['bonus'] > 0): ?>
                            <div class="inline-block bg-green-500/20 text-green-400 text-xs font-semibold px-3 py-1 rounded-full mb-3">
                                Save <?php echo round(($package['bonus'] / $credits) * 100); ?>%
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-center space-x-2 mb-2">
                                <i class="fas fa-ticket text-2xl text-purple-400"></i>
                                <span class="text-xs text-gray-400">Pack</span>
                            </div>
                            
                            <div class="text-4xl font-bold text-white mb-2">
                                <?php echo $credits; ?>
                            </div>
                            
                            <div class="text-sm text-gray-400 mb-1">
                                <?php echo number_format($package['price']); ?> IDR
                            </div>
                            
                            <div class="text-xs text-gray-500">
                                Rp <?php echo number_format($package['price'] / $credits); ?> / credit
                            </div>
                        </div>
                        
                        <div class="space-y-2 text-sm">
                            <?php if ($package['bonus'] > 0): ?>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-400">Base credits:</span>
                                <span class="text-white"><?php echo $credits; ?></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-green-400">Bonus:</span>
                                <span class="text-green-400">+<?php echo $package['bonus']; ?></span>
                            </div>
                            <div class="border-t border-gray-700 pt-2 flex items-center justify-between text-xs">
                                <span class="text-gray-400 font-semibold">Total:</span>
                                <span class="text-white font-bold"><?php echo $package['total']; ?> credits</span>
                            </div>
                            <?php else: ?>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-400">Total credits:</span>
                                <span class="text-white font-bold"><?php echo $package['total']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                </div>
            </div>

            <!-- Info -->
            <div class="max-w-2xl mx-auto mt-12">
                <div class="glass-effect rounded-2xl p-6">
                    <h3 class="font-semibold mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-400 mr-2"></i>
                        How it works
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-400 mr-3 mt-1"></i>
                            <span>Each credit allows you to reveal <strong class="text-white">1 tracking number</strong></span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-400 mr-3 mt-1"></i>
                            <span>Credits never expire - use them whenever you need</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-400 mr-3 mt-1"></i>
                            <span>Larger packages include <strong class="text-white">bonus credits</strong> for better value</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-400 mr-3 mt-1"></i>
                            <span>Secure payment via <strong class="text-white">QRIS</strong> (max Rp 499,000) or <strong class="text-white">Saweria</strong></span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-ticket text-purple-400 mr-3 mt-1"></i>
                            <span>Need custom packages? <a href="mailto:support@tukaruy.online" class="text-purple-400 hover:text-purple-300 underline font-medium">Contact Admin</a> for bulk discounts and custom amounts</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

    <!-- Checkout Modal -->
    <div id="checkout-modal" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50 hidden items-center justify-center p-6">
        <div class="glass-effect rounded-2xl p-8 max-w-lg w-full">
            <h3 class="text-2xl font-bold mb-6">Checkout</h3>
            
            <div id="checkout-content">
                <!-- Will be populated by JavaScript -->
            </div>
            
            <div class="mt-6 flex gap-3">
                <button onclick="closeCheckout()" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 rounded-lg transition">
                    Cancel
                </button>
                <button id="pay-button" onclick="processPayment()" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium py-3 rounded-lg transition">
                    Pay Now
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Modal (QRIS) -->
    <div id="payment-modal" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50 hidden items-center justify-center p-6">
        <div class="glass-effect rounded-2xl p-8 max-w-md w-full text-center">
            <h3 class="text-2xl font-bold mb-4">Scan QRIS Code</h3>
            
            <div id="qris-container" class="mb-6">
                <!-- QRIS code will be displayed here -->
            </div>
            
            <div id="payment-info" class="space-y-3 mb-6">
                <!-- Payment info will be displayed here -->
            </div>
            
            <button onclick="closePayment()" class="w-full bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 rounded-lg transition">
                Cancel Payment
            </button>
        </div>
    </div>

    <script>
        let selectedPackage = null;
        let paymentCheckInterval = null;

        function selectPackage(credits, price, total, bonus) {
            selectedPackage = { credits, price, total, bonus };
            
            // Check if price exceeds QRIS limit
            const qrisMaxAmount = 499000;
            const isOverQrisLimit = price > qrisMaxAmount;
            
            const content = `
                <div class="bg-slate-800 rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-lg font-semibold">${credits} Credits Pack</span>
                        <span class="text-2xl font-bold text-purple-400">Rp ${price.toLocaleString()}</span>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">List price (Rp ${(BASE_PRICE_PER_CREDIT).toLocaleString()} × ${credits}):</span>
                            <span>Rp ${(credits * BASE_PRICE_PER_CREDIT).toLocaleString()}</span>
                        </div>
                        ${bonus > 0 ? `
                        <div class="flex justify-between text-green-400">
                            <span>Bonus credits:</span>
                            <span>+${bonus}</span>
                        </div>
                        ` : ''}
                        <div class="border-t border-gray-700 pt-2 flex justify-between font-bold">
                            <span>Total credits:</span>
                            <span class="text-purple-400">${total} credits</span>
                        </div>
                    </div>
                </div>
                
                ${isOverQrisLimit ? `
                <div class="bg-orange-500/10 border border-orange-500/30 rounded-lg p-4 mb-4">
                    <p class="text-sm text-orange-300">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Note:</strong> This package exceeds QRIS limit (Rp 499,000). Please use Saweria payment method.
                    </p>
                </div>
                ` : ''}
                
                <!-- Payment Method Selection -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-400 mb-3">Choose Payment Method:</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="payment-method-option ${isOverQrisLimit ? 'opacity-50 cursor-not-allowed' : ''}" data-method="qrispay">
                            <input type="radio" id="method-qrispay" name="payment_method" value="qrispay" ${!isOverQrisLimit ? 'checked' : 'disabled'} class="sr-only">
                            <label for="method-qrispay" class="block p-4 border border-gray-600 rounded-lg ${!isOverQrisLimit ? 'cursor-pointer hover:border-purple-500' : 'cursor-not-allowed'} transition ${!isOverQrisLimit ? 'selected' : ''}">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-qrcode text-blue-400"></i>
                                    <div>
                                        <div class="font-medium">QRIS Pay</div>
                                        <div class="text-xs text-gray-400">Scan QR with e-wallet</div>
                                        ${isOverQrisLimit ? '<div class="text-xs text-orange-400 mt-1">Max Rp 499,000</div>' : ''}
                                    </div>
                                </div>
                            </label>
                        </div>
                        
                        <div class="payment-method-option" data-method="saweria">
                            <input type="radio" id="method-saweria" name="payment_method" value="saweria" ${isOverQrisLimit ? 'checked' : ''} class="sr-only">
                            <label for="method-saweria" class="block p-4 border border-gray-600 rounded-lg cursor-pointer hover:border-purple-500 transition ${isOverQrisLimit ? 'selected' : ''}">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-heart text-pink-400"></i>
                                    <div>
                                        <div class="font-medium">Saweria</div>
                                        <div class="text-xs text-gray-400">Donate via Saweria</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="payment-method-info mb-4">
                    <div id="info-qrispay" class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 ${isOverQrisLimit ? 'hidden' : ''}">
                        <p class="text-sm text-blue-300">
                            <i class="fas fa-info-circle mr-2"></i>
                            You will pay using <strong>QRIS</strong>. Scan the QR code with your banking app or e-wallet.
                        </p>
                    </div>
                    
                    <div id="info-saweria" class="bg-pink-500/10 border border-pink-500/30 rounded-lg p-4 ${!isOverQrisLimit ? 'hidden' : ''}">
                        <p class="text-sm text-pink-300">
                            <i class="fas fa-info-circle mr-2"></i>
                            You will be redirected to <strong>Saweria</strong> to complete your donation payment.
                        </p>
                    </div>
                </div>
                
                <div class="text-left space-y-2 text-sm text-gray-400">
                    <div class="flex justify-between">
                        <span>Payment method:</span>
                        <span class="text-white font-medium" id="selected-method-display">${isOverQrisLimit ? 'Saweria' : 'QRIS'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>You pay:</span>
                        <span class="text-white font-bold text-lg">Rp ${price.toLocaleString()}</span>
                    </div>
                </div>
            `;
            
            document.getElementById('checkout-content').innerHTML = content;
            document.getElementById('checkout-modal').classList.remove('hidden');
            document.getElementById('checkout-modal').classList.add('flex');
            
            // Setup payment method selection
            setupPaymentMethodSelection(isOverQrisLimit);
        }
        
        function setupPaymentMethodSelection(isOverQrisLimit) {
            const paymentOptions = document.querySelectorAll('.payment-method-option');
            const methodDisplays = {
                'qrispay': 'QRIS',
                'saweria': 'Saweria'
            };
            
            paymentOptions.forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                const label = option.querySelector('label');
                
                if (radio.disabled) return; // Skip disabled options
                
                radio.addEventListener('change', function() {
                    // Update visual selection
                    paymentOptions.forEach(opt => {
                        const optRadio = opt.querySelector('input[type="radio"]');
                        const optLabel = opt.querySelector('label');
                        if (!optRadio.disabled) {
                            optLabel.classList.remove('selected', 'border-purple-500', 'bg-purple-500/10');
                            optLabel.classList.add('border-gray-600');
                        }
                    });
                    
                    if (this.checked) {
                        label.classList.remove('border-gray-600');
                        label.classList.add('selected', 'border-purple-500', 'bg-purple-500/10');
                    }
                    
                    // Update method display
                    document.getElementById('selected-method-display').textContent = methodDisplays[this.value];
                    
                    // Show/hide method info
                    document.querySelectorAll('[id^="info-"]').forEach(info => info.classList.add('hidden'));
                    document.getElementById('info-' + this.value).classList.remove('hidden');
                });
            });
            
            // Initialize selected option
            const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
            if (checkedRadio) {
                checkedRadio.dispatchEvent(new Event('change'));
            }
        }

        function closeCheckout() {
            document.getElementById('checkout-modal').classList.add('hidden');
            document.getElementById('checkout-modal').classList.remove('flex');
        }

        async function processPayment() {
            if (!selectedPackage) return;
            
            const payButton = document.getElementById('pay-button');
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            payButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            payButton.disabled = true;
            
            try {
                const response = await fetch('/api/payment/create.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        credits: selectedPackage.credits,
                        amount: selectedPackage.price,
                        total: selectedPackage.total,
                        payment_method: selectedMethod
                    })
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    console.error('Non-JSON response:', textResponse);
                    throw new Error('Server returned non-JSON response. Please check server logs.');
                }
                
                const data = await response.json();
                
                console.log('Payment response:', data); // Debug log
                
                if (!data.success) {
                    throw new Error(data.error || 'Payment failed');
                }
                
                // Close checkout modal
                closeCheckout();
                
                if (selectedMethod === 'saweria') {
                    // Handle Saweria payment
                    if (!data.saweria) {
                        throw new Error('No Saweria data received');
                    }
                    showSaweriaModal(data.saweria);
                } else {
                    // Handle QRIS payment
                    if (!data.qris) {
                        throw new Error('No QRIS data received');
                    }
                    showPaymentModal(data.qris);
                }
                
            } catch (error) {
                console.error('Payment error:', error); // Debug log
                alert('Payment failed: ' + error.message);
                payButton.innerHTML = 'Pay Now';
                payButton.disabled = false;
            }
        }

        function showPaymentModal(qris) {
            console.log('showPaymentModal called with qris:', qris); // Debug log
            
            const qrisContainer = document.getElementById('qris-container');
            const paymentInfo = document.getElementById('payment-info');
            
            // Validate QRIS data
            if (!qris || typeof qris !== 'object') {
                console.error('Invalid QRIS data:', qris);
                qrisContainer.innerHTML = `
                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-6 text-center">
                        <i class="fas fa-exclamation-circle text-4xl text-red-400 mb-3"></i>
                        <p class="text-sm text-red-300">
                            <strong>Invalid QRIS response format</strong><br>
                            Please try again or contact admin for assistance.
                        </p>
                        <div class="mt-4">
                            <button onclick="closePayment(); location.reload();" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                                Try Again
                            </button>
                        </div>
                    </div>
                `;
                paymentInfo.innerHTML = `
                    <div class="text-center">
                        <p class="text-sm text-red-400">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            QRIS data format invalid. Please contact admin via ticket.
                        </p>
                    </div>
                `;
                document.getElementById('payment-modal').classList.remove('hidden');
                document.getElementById('payment-modal').classList.add('flex');
                return;
            }
            
            // Check if QRIS image URL exists and is valid
            if (!qris.qris_image_url || qris.qris_image_url === '' || qris.qris_image_url === null) {
                console.error('QRIS image URL missing or empty:', qris.qris_image_url);
                qrisContainer.innerHTML = `
                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-6 text-center">
                        <i class="fas fa-exclamation-circle text-4xl text-red-400 mb-3"></i>
                        <p class="text-sm text-red-300">
                            <strong>QRIS code not available</strong><br>
                            The QR code image was not generated.<br>
                            ${qris.qris_id ? 'Payment ID: ' + qris.qris_id : ''}
                        </p>
                        <div class="mt-4">
                            <button onclick="closePayment(); location.reload();" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                                Try Again
                            </button>
                        </div>
                    </div>
                `;
                
                paymentInfo.innerHTML = `
                    <div class="text-left bg-slate-800 rounded-lg p-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-400">Amount:</span>
                            <span class="font-bold">Rp ${(qris.amount || 0).toLocaleString()}</span>
                        </div>
                        ${qris.payment_reference ? `
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-400">Reference:</span>
                            <span class="font-mono text-sm">${qris.payment_reference}</span>
                        </div>
                        ` : ''}
                        ${qris.qris_id ? `
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-400">QRIS ID:</span>
                            <span class="font-mono text-sm">${qris.qris_id}</span>
                        </div>
                        ` : ''}
                        <div class="flex justify-between">
                            <span class="text-gray-400">Status:</span>
                            <span class="text-red-400">Failed - No QR Image</span>
                        </div>
                    </div>
                    <p class="text-sm text-red-400">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        QRIS generation failed. Please contact admin via ticket.
                    </p>
                `;
                
                document.getElementById('payment-modal').classList.remove('hidden');
                document.getElementById('payment-modal').classList.add('flex');
                return;
            }
            
            // Display QRIS code successfully
            qrisContainer.innerHTML = `
                <div class="bg-white p-6 rounded-xl inline-block shadow-2xl">
                    <img src="${qris.qris_image_url}" alt="QRIS Code" class="w-72 h-72" 
                         onerror="this.parentElement.innerHTML='<div class=\\'text-red-500 p-6 text-center\\'>Failed to load QR code<br><small>${qris.qris_image_url}</small></div>'">
                </div>
            `;
            
            const expiresIn = Math.floor((qris.expires_in_seconds || 900) / 60);
            paymentInfo.innerHTML = `
                <div class="text-left bg-slate-800 rounded-lg p-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-400">Amount:</span>
                        <span class="font-bold">Rp ${(qris.amount || 0).toLocaleString()}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-400">Reference:</span>
                        <span class="font-mono text-sm">${qris.payment_reference || 'N/A'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Expires in:</span>
                        <span class="text-yellow-400" id="countdown">${expiresIn} minutes</span>
                    </div>
                </div>
                <p class="text-sm text-gray-400 text-center">
                    <i class="fas fa-mobile-alt mr-2"></i>
                    Open your mobile banking app and scan the QR code above
                </p>
            `;
            
            document.getElementById('payment-modal').classList.remove('hidden');
            document.getElementById('payment-modal').classList.add('flex');
            
            // Start checking payment status
            startPaymentCheck(qris.qris_id);
        }

        function startPaymentCheck(qrisId) {
            paymentCheckInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/api/payment/check.php?qris_id=${qrisId}`);
                    const data = await response.json();
                    
                    if (data.status === 'paid') {
                        clearInterval(paymentCheckInterval);
                        closePayment();
                        showSuccessMessage(data.tickets);
                    }
                } catch (error) {
                    console.error('Payment check error:', error);
                }
            }, 3000); // Check every 3 seconds
        }

        function closePayment() {
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
            }
            document.getElementById('payment-modal').classList.add('hidden');
            document.getElementById('payment-modal').classList.remove('flex');
        }

        function showSaweriaModal(saweria) {
            // For Saweria, we show a redirect modal instead of QR code
            const qrisContainer = document.getElementById('qris-container');
            const paymentInfo = document.getElementById('payment-info');
            
            // Update modal title
            document.querySelector('#payment-modal h3').textContent = 'Saweria Payment';
            
            qrisContainer.innerHTML = `
                <div class="bg-gradient-to-br from-pink-500 to-purple-600 p-8 rounded-lg text-center">
                    <i class="fas fa-heart text-6xl text-white mb-4"></i>
                    <h4 class="text-xl font-bold text-white mb-2">Ready to Pay</h4>
                    <p class="text-pink-100">Click the button below to continue to Saweria</p>
                </div>
            `;
            
            paymentInfo.innerHTML = `
                <div class="text-left bg-slate-800 rounded-lg p-4 mb-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-400">Amount:</span>
                        <span class="font-bold">Rp ${saweria.amount.toLocaleString()}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-400">Reference:</span>
                        <span class="font-mono text-sm">${saweria.payment_reference}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Donation ID:</span>
                        <span class="font-mono text-sm">${saweria.donation_id}</span>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="${saweria.payment_url}" target="_blank" class="flex-1 bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white font-medium py-3 px-4 rounded-lg transition text-center">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        Pay via Saweria
                    </a>
                </div>
                <p class="text-sm text-gray-400 mt-3 text-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    After payment, credits will be added automatically
                </p>
            `;
            
            document.getElementById('payment-modal').classList.remove('hidden');
            document.getElementById('payment-modal').classList.add('flex');
            
            // Start checking payment status for Saweria
            startSaweriaPaymentCheck(saweria.donation_id);
        }
        
        function startSaweriaPaymentCheck(donationId) {
            paymentCheckInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/api/payment/check.php?saweria_id=${donationId}`);
                    const data = await response.json();
                    
                    if (data.status === 'paid') {
                        clearInterval(paymentCheckInterval);
                        closePayment();
                        showSuccessMessage(data.tickets);
                    }
                } catch (error) {
                    console.error('Saweria payment check error:', error);
                }
            }, 5000); // Check every 5 seconds for Saweria
        }

        const BASE_PRICE_PER_CREDIT = <?php echo BASE_PRICE_PER_CREDIT; ?>;
        
        function showSuccessMessage(tickets) {
            alert(`Payment successful! ${tickets} credits have been added to your account.`);
            location.reload();
        }
        
        // Add CSS for payment method selection
        const style = document.createElement('style');
        style.textContent = `
            .payment-method-option label.selected {
                border-color: #8b5cf6 !important;
                background-color: rgba(139, 92, 246, 0.1) !important;
            }
        `;
        document.head.appendChild(style);
    </script>

</body>
</html>
