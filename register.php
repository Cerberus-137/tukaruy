<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Cloudflare Turnstile config
define('TURNSTILE_SITE_KEY', '0x4AAAAAADv5iD6IFqguAWUU');
define('TURNSTILE_SECRET_KEY', '0x4AAAAAADv5iGUdF-BTe-Rgo6BLfApsm4Q');

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /track');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $company = $_POST['company'] ?? null;
    $captchaToken = $_POST['cf-turnstile-response'] ?? '';
    
    // Validation
    if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        $error = 'Please fill in all required fields';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Verify CAPTCHA token if present
        $captchaValid = true;
        if (!empty($captchaToken)) {
            $captchaValid = verifyCaptcha($captchaToken, TURNSTILE_SECRET_KEY);
        } else {
            // Log warning but allow bypass if CAPTCHA fails to load
            error_log('Warning: CAPTCHA token not received - possible widget load failure');
            // Set to false if you want to enforce CAPTCHA
            // $captchaValid = false;
        }
        
        if (!$captchaValid && !empty($captchaToken)) {
            $error = 'CAPTCHA verification failed. Please try again.';
        } else {
            $result = registerUser($email, $password, $firstName, $lastName, $company);
            
            if ($result['success']) {
                header('Location: /login?registered=1');
                exit;
            } else {
                $error = $result['message'];
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
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        .glass-effect {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen p-6">
    
    <div class="max-w-6xl mx-auto py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            <!-- Left Side - Info -->
            <div class="text-white">
                <a href="/" class="inline-flex items-center space-x-3 mb-8">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shipping-fast text-white text-xl"></i>
                    </div>
                    <span class="text-3xl font-bold">Tukarkuy</span>
                </a>

                <h1 class="text-4xl font-bold mb-6">Quick Sign-Up, Instant Access!</h1>

                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-rocket text-purple-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Just a Few Clicks Away</h3>
                            <p class="text-gray-400">Our streamlined sign-up process means you're just a few clicks away from experiencing the full power of tracktaco. It's quick, easy, and hassle-free!</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-flask text-blue-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Test Our Platform Instantly</h3>
                            <p class="text-gray-400">As soon as you sign up, you'll gain immediate access to our platform. You can explore its full functionality and when you're ready, you can purchase credits to start using our services.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-gem text-green-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Discover the Value</h3>
                            <p class="text-gray-400">When you are ready, explore our various credit packages, designed to offer you the best value and efficiency for your needs. From 100 to 25,000 credits, find the perfect fit for your project or business.</p>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-700">
                        <h3 class="font-semibold text-lg mb-3">Why Choose Us?</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-400 mr-3"></i>
                                Dedicated Support: Our team is here to support you every step of the way, ensuring a seamless experience from sign-up to daily usage.
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-400 mr-3"></i>
                                Attractive Bulk Discounts: Take advantage of our bulk discounts on credit packages, saving you more as you scale up.
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-400 mr-3"></i>
                                Dedicated to Your Privacy: At tracktaco, your privacy is our top priority. We also employ SSL (Secure Socket Layer) technology to create a secure connection for all your transactions.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="glass-effect rounded-2xl p-8 shadow-2xl">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-white mb-2">Create account</h2>
                    <p class="text-gray-400 text-sm">Sign up to get started.</p>
                </div>

                <?php if ($error): ?>
                <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">First name</label>
                                <input 
                                    type="text" 
                                    name="first_name" 
                                    value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                                    class="w-full bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Last name</label>
                                <input 
                                    type="text" 
                                    name="last_name" 
                                    value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                                    class="w-full bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition"
                                    required
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Company (optional)</label>
                            <input 
                                type="text" 
                                name="company" 
                                value="<?php echo htmlspecialchars($_POST['company'] ?? ''); ?>"
                                class="w-full bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                            <input 
                                type="email" 
                                name="email" 
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                placeholder="kaso117@gmail.com"
                                class="w-full bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                            <input 
                                type="password" 
                                name="password" 
                                placeholder="••••••••"
                                class="w-full bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition"
                                required
                                minlength="8"
                            >
                            <p class="mt-1 text-xs text-gray-400">At least 8 characters.</p>
                        </div>

                        <!-- Cloudflare Turnstile CAPTCHA -->
                        <div class="cf-turnstile" data-sitekey="<?php echo TURNSTILE_SITE_KEY; ?>" data-theme="dark"></div>

                        <button 
                            type="submit"
                            class="w-full bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-semibold py-3 rounded-lg transition"
                        >
                            Create account
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-400">
                        Already have one? 
                        <a href="/login" class="text-purple-400 hover:text-purple-300 font-medium transition">Sign in</a>
                    </p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
