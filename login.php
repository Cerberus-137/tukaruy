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
    $captchaToken = $_POST['cf-turnstile-response'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } elseif (empty($captchaToken)) {
        $error = 'Please complete the CAPTCHA';
    } else {
        // Verify CAPTCHA token
        $captchaValid = verifyCaptcha($captchaToken, TURNSTILE_SECRET_KEY);
        
        if (!$captchaValid) {
            $error = 'CAPTCHA verification failed. Please try again.';
        } else {
            $result = loginUser($email, $password);
            
            if ($result['success']) {
                header('Location: /track');
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
    <title>Sign In - <?php echo SITE_NAME; ?></title>
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
<body class="min-h-screen flex items-center justify-center p-6">
    
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center space-x-3 mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shipping-fast text-white text-xl"></i>
                </div>
                <span class="text-3xl font-bold text-white">Tukarkuy</span>
            </a>
        </div>

        <!-- Login Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-white mb-2">Sign in</h1>
                <p class="text-gray-400 text-sm">Welcome back to TrackTaco.</p>
            </div>

            <?php if ($error): ?>
            <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
            <div class="mb-6 bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg text-sm">
                <i class="fas fa-check-circle mr-2"></i>Account created successfully! Please sign in.
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="space-y-4">
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
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-300">Password</label>
                            <a href="#" class="text-sm text-purple-400 hover:text-purple-300 transition">Forgot?</a>
                        </div>
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="••••••••"
                            class="w-full bg-slate-700/50 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition"
                            required
                        >
                    </div>

                    <!-- Cloudflare Turnstile CAPTCHA -->
                    <div class="cf-turnstile" data-sitekey="<?php echo TURNSTILE_SITE_KEY; ?>" data-theme="dark"></div>

                    <button 
                        type="submit"
                        class="w-full bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-semibold py-3 rounded-lg transition"
                    >
                        Sign in
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-400">
                    New here? 
                    <a href="/register" class="text-purple-400 hover:text-purple-300 font-medium transition">Create an account</a>
                </p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="mt-6 text-center">
            <a href="/" class="text-sm text-gray-400 hover:text-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to home
            </a>
        </div>
    </div>

</body>
</html>
