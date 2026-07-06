<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Require login
requireLogin('/login.php');

$user = getCurrentUser();
$error = '';
$success = '';
$profileSuccess = '';
$passwordSuccess = '';
$profileError = '';
$passwordError = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $company = $_POST['company'] ?? '';
    
    if (empty($firstName) || empty($lastName)) {
        $profileError = 'First name and last name are required';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, company = ? WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $company, $user['id']]);
            
            // Update session
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            
            $profileSuccess = 'Profile updated successfully';
            $user = getCurrentUser(); // Refresh user data
        } catch (Exception $e) {
            $profileError = 'Failed to update profile';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $passwordError = 'All password fields are required';
    } elseif ($newPassword !== $confirmPassword) {
        $passwordError = 'New passwords do not match';
    } elseif (strlen($newPassword) < 8) {
        $passwordError = 'New password must be at least 8 characters';
    } else {
        $result = changePassword($user['id'], $currentPassword, $newPassword);
        
        if ($result['success']) {
            $passwordSuccess = 'Password changed successfully';
        } else {
            $passwordError = $result['message'];
        }
    }
}

// Handle API settings update (admin only)
$apiSuccess = '';
$apiError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_api_settings') {
    if ($user['role'] !== 'admin') {
        $apiError = 'Access denied';
    } else {
        try {
            $pdo = getDBConnection();
            
            $tracktacoKey = $_POST['tracktaco_api_key'] ?? '';
            $qrispayToken = $_POST['qrispay_api_token'] ?? '';
            $saweriaToken = $_POST['saweria_api_token'] ?? '';
            $qrispayEnabled = isset($_POST['qrispay_enabled']) ? 1 : 0;
            $saweriaEnabled = isset($_POST['saweria_enabled']) ? 1 : 0;
            
            // Update or insert settings
            $settings = [
                'tracktaco_api_key' => $tracktacoKey,
                'qrispay_api_token' => $qrispayToken,
                'saweria_api_token' => $saweriaToken,
                'payment_methods_qrispay' => $qrispayEnabled,
                'payment_methods_saweria' => $saweriaEnabled
            ];
            
            foreach ($settings as $key => $value) {
                $stmt = $pdo->prepare("
                    INSERT INTO admin_settings (setting_key, setting_value, updated_by) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value), 
                    updated_by = VALUES(updated_by), 
                    updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$key, $value, $user['id']]);
            }
            
            $apiSuccess = 'API settings updated successfully';
        } catch (Exception $e) {
            $apiError = 'Failed to update API settings: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    </style>
</head>
<body class="bg-black text-gray-100 min-h-screen">
    
    <!-- Top Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4 bg-black/80 backdrop-blur-md border-b border-gray-800">
        <div class="max-w-[1600px] mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <a href="/track" class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shipping-fast text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-bold">Tukarkuy</span>
                    </a>
                    <div class="hidden md:flex items-center space-x-6 text-sm">
                        <a href="/track" class="text-gray-400 hover:text-white transition">Pelacakan</a>
                        <a href="/tickets" class="text-gray-400 hover:text-white transition">Top Up</a>
                        <a href="/settings" class="text-white font-medium">Pengaturan</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-400">Tickets: <span class="text-white font-semibold"><?php echo number_format($user['tickets']); ?></span></span>
                    <a href="/logout" class="text-red-400 hover:text-red-300 text-sm">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-24 px-6 pb-12">
        <div class="max-w-4xl mx-auto">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2">Account Settings</h1>
                <p class="text-gray-400">Manage your profile and password</p>
            </div>

            <div class="space-y-6">
                
                <!-- Profile Section -->
                <div class="glass-effect rounded-2xl p-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <i class="fas fa-user text-purple-400 text-xl"></i>
                        <h2 class="text-xl font-semibold">Profile</h2>
                    </div>

                    <?php if ($profileSuccess): ?>
                    <div class="mb-6 bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg text-sm">
                        <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($profileSuccess); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($profileError): ?>
                    <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($profileError); ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">First name</label>
                                <input 
                                    type="text" 
                                    name="first_name" 
                                    value="<?php echo htmlspecialchars($user['first_name']); ?>"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Last name</label>
                                <input 
                                    type="text" 
                                    name="last_name" 
                                    value="<?php echo htmlspecialchars($user['last_name']); ?>"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Company</label>
                            <input 
                                type="text" 
                                name="company" 
                                value="<?php echo htmlspecialchars($user['company'] ?? ''); ?>"
                                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                            >
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Email</label>
                            <input 
                                type="email" 
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                class="w-full bg-slate-900 border border-slate-800 rounded-lg px-4 py-3 text-gray-500 cursor-not-allowed"
                                disabled
                            >
                            <p class="mt-1 text-xs text-gray-500">Email cannot be changed</p>
                        </div>

                        <button 
                            type="submit"
                            class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium px-6 py-3 rounded-lg transition"
                        >
                            <i class="fas fa-save mr-2"></i>Save profile
                        </button>
                    </form>
                </div>

                <!-- Change Password Section -->
                <div class="glass-effect rounded-2xl p-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <i class="fas fa-key text-purple-400 text-xl"></i>
                        <h2 class="text-xl font-semibold">Change password</h2>
                    </div>

                    <?php if ($passwordSuccess): ?>
                    <div class="mb-6 bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg text-sm">
                        <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($passwordSuccess); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($passwordError): ?>
                    <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($passwordError); ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Current password</label>
                                <input 
                                    type="password" 
                                    name="current_password" 
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                                    required
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">New password</label>
                                <input 
                                    type="password" 
                                    name="new_password" 
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                                    required
                                    minlength="8"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Confirm new password</label>
                                <input 
                                    type="password" 
                                    name="confirm_password" 
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                                    required
                                    minlength="8"
                                >
                            </div>
                        </div>

                        <button 
                            type="submit"
                            class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium px-6 py-3 rounded-lg transition"
                        >
                            <i class="fas fa-lock mr-2"></i>Update password
                        </button>
                    </form>
                </div>

                <!-- Account Info -->
                <div class="glass-effect rounded-2xl p-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <i class="fas fa-info-circle text-purple-400 text-xl"></i>
                        <h2 class="text-xl font-semibold">Account Information</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-400 mb-1">Account Created</p>
                            <p class="font-medium"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400 mb-1">Last Login</p>
                            <p class="font-medium"><?php echo $user['last_login'] ? date('F d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400 mb-1">Account Type</p>
                            <p class="font-medium capitalize"><?php echo htmlspecialchars($user['role']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400 mb-1">Available Tickets</p>
                            <p class="font-medium text-purple-400"><?php echo number_format($user['tickets']); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if ($user['role'] === 'admin'): ?>
                <!-- API Settings (Admin Only) -->
                <div class="glass-effect rounded-2xl p-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <i class="fas fa-cog text-purple-400 text-xl"></i>
                        <h2 class="text-xl font-semibold">API Settings</h2>
                        <span class="bg-purple-500/20 text-purple-400 text-xs font-semibold px-2 py-1 rounded-full">Admin</span>
                    </div>

                    <?php if ($apiSuccess): ?>
                    <div class="mb-6 bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg text-sm">
                        <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($apiSuccess); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($apiError): ?>
                    <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($apiError); ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_api_settings">
                        
                        <!-- TrackTaco API -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-search mr-2 text-blue-400"></i>TrackTaco API Key
                            </label>
                            <input 
                                type="text" 
                                name="tracktaco_api_key" 
                                value="<?php echo htmlspecialchars(getAdminSetting('tracktaco_api_key', '')); ?>"
                                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition font-mono text-sm"
                                placeholder="tt_live_..."
                            >
                            <p class="mt-1 text-xs text-gray-500">Your TrackTaco API key for tracking number searches</p>
                        </div>

                        <!-- Payment Methods -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-300 mb-4">Payment Methods</h3>
                            
                            <!-- QRIS Pay -->
                            <div class="bg-slate-800/50 rounded-lg p-4 mb-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-qrcode text-blue-400"></i>
                                        <span class="font-medium">QRIS</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="qrispay_enabled" class="sr-only peer" <?php echo getAdminSetting('payment_methods_qrispay', '1') ? 'checked' : ''; ?>>
                                        <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300/25 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                    </label>
                                </div>
                                <input 
                                    type="text" 
                                    name="qrispay_api_token" 
                                    value="<?php echo htmlspecialchars(getAdminSetting('qrispay_api_token', '')); ?>"
                                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition font-mono text-sm"
                                    placeholder="cki_..."
                                >
                                <p class="mt-1 text-xs text-gray-500">QRISPay API token for QRIS payments</p>
                            </div>
                            
                            <!-- Saweria -->
                            <div class="bg-slate-800/50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-heart text-pink-400"></i>
                                        <span class="font-medium">Saweria</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="saweria_enabled" class="sr-only peer" <?php echo getAdminSetting('payment_methods_saweria', '1') ? 'checked' : ''; ?>>
                                        <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300/25 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                    </label>
                                </div>
                                <input 
                                    type="text" 
                                    name="saweria_api_token" 
                                    value="<?php echo htmlspecialchars(getAdminSetting('saweria_api_token', '')); ?>"
                                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition font-mono text-sm"
                                    placeholder="eyJ..."
                                >
                                <p class="mt-1 text-xs text-gray-500">Saweria JWT token for donation payments</p>
                            </div>
                        </div>

                        <button 
                            type="submit"
                            class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium px-6 py-3 rounded-lg transition"
                        >
                            <i class="fas fa-save mr-2"></i>Save API Settings
                        </button>
                    </form>
                </div>
                <?php endif; ?>

            </div>

        </div>
    </div>

</body>
</html>
