<?php
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'security/rate_limiter.php';

$success = '';
$error = '';

// Cloudflare Turnstile config (same as login/register)
define('TURNSTILE_SITE_KEY', '0x4AAAAAADv5iD6IFqguAWUU');
define('TURNSTILE_SECRET_KEY', '0x4AAAAAADv5iGUdF-BTe-Rgo6BLfApsm4Q');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting
    if (!checkRateLimit('contact', 3, 600)) { // 3 attempts per 10 minutes
        $error = 'Too many submissions. Please try again later.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $captchaToken = $_POST['cf-turnstile-response'] ?? '';
        
        if (empty($name) || empty($email) || empty($message)) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (empty($captchaToken)) {
            $error = 'Please complete the CAPTCHA verification.';
        } else {
            // Verify CAPTCHA
            $captchaValid = verifyCaptcha($captchaToken, TURNSTILE_SECRET_KEY);
            if (!$captchaValid) {
                error_log('Contact form CAPTCHA verification failed');
                $error = 'CAPTCHA verification failed. Please try again.';
            } else {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("
                    INSERT INTO contact_submissions (name, email, subject, message, ip_address, user_agent)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $name,
                    $email,
                    $subject,
                    $message,
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
                
                $success = 'Thank you for contacting us! We will get back to you within 24 hours.';
                resetRateLimit('contact');
                
                // Clear form
                $_POST = [];
            } catch (Exception $e) {
                error_log('Contact form error: ' . $e->getMessage());
                $error = 'Failed to send message. Please try again later.';
            }
        }
        }
    }
}

/**
 * Verify Cloudflare Turnstile CAPTCHA token
 */
function verifyCaptcha($token, $secretKey) {
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    
    $data = [
        'secret' => $secretKey,
        'response' => $token
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === false) {
        error_log('CAPTCHA verification failed: Unable to reach Cloudflare API');
        return false;
    }
    
    $decoded = json_decode($result, true);
    
    if (!isset($decoded['success']) || !$decoded['success']) {
        error_log('CAPTCHA verification failed: ' . json_encode($decoded));
        return false;
    }
    
    return true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .gradient-text { background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shipping-fast text-white"></i>
                    </div>
                    <span class="text-2xl font-bold gradient-text">Tukarkuy</span>
                </a>
                <div class="flex items-center space-x-6">
                    <a href="/" class="text-gray-600 hover:text-gray-900 font-medium transition">Home</a>
                    <a href="/blog" class="text-gray-600 hover:text-gray-900 font-medium transition">Blog</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="/track" class="bg-gradient-to-r from-purple-500 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">Dashboard</a>
                    <?php else: ?>
                        <a href="/login" class="text-gray-700 font-semibold transition">Login</a>
                        <a href="/register" class="bg-gradient-to-r from-purple-500 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 bg-gradient-to-r from-purple-600 to-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl font-black mb-4">Get In Touch</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Have questions? We're here to help. Send us a message and we'll respond as soon as possible.</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Contact Info -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-envelope text-purple-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Email Us</h3>
                        <p class="text-gray-600 mb-3">Our team will respond within 24 hours</p>
                        <a href="mailto:support@tukarkuy.web.id" class="text-purple-600 font-semibold hover:text-purple-700">support@tukarkuy.web.id</a>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-comment-dots text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Live Chat</h3>
                        <p class="text-gray-600 mb-3">Available Monday-Friday, 9AM-5PM</p>
                        <button class="text-blue-600 font-semibold hover:text-blue-700">Start Chat</button>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-book text-green-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Help Center</h3>
                        <p class="text-gray-600 mb-3">Browse our FAQ and documentation</p>
                        <a href="/blog" class="text-green-600 font-semibold hover:text-green-700">Visit Help Center</a>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                        <h2 class="text-3xl font-bold mb-6">Send Us a Message</h2>
                        
                        <?php if ($success): ?>
                        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                            <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                            <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                                    <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="John Doe">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                                    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="john@example.com">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
                                <input type="text" name="subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="How can we help?">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Message *</label>
                                <textarea name="message" required rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="Tell us more about your inquiry..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" class="w-full bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-bold py-4 rounded-lg transition transform hover:scale-105 shadow-lg">
                                <i class="fas fa-paper-plane mr-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                        <li><a href="/#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="/#pricing" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="/track" class="hover:text-white transition">Tracking</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/blog" class="hover:text-white transition">Blog</a></li>
                        <li><a href="/contact" class="hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/privacy-policy" class="hover:text-white transition">Privacy</a></li>
                        <li><a href="/tos" class="hover:text-white transition">Terms</a></li>
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
