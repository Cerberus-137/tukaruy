<?php
// Authentication helper functions

require_once 'config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, email, first_name, last_name, company, role, tickets, created_at, last_login FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Login user
function loginUser($email, $password, $rememberMe = false) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id, email, password, first_name, last_name, role, tickets FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'message' => 'Email or password is incorrect'];
    }
    
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Email or password is incorrect'];
    }
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_tickets'] = $user['tickets'];
    
    // Handle Remember Me - set cookie for 30 days
    if ($rememberMe) {
        $token = bin2hex(random_bytes(32)); // Generate secure random token
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store token in database (you may want to create a remember_tokens table)
        // For now, we'll use a simple cookie approach
        setcookie('remember_token', $token, $expiry, '/', '', true, true); // HttpOnly, Secure
        setcookie('remember_user', $user['id'], $expiry, '/', '', true, true);
        
        // Store in session for tracking
        $_SESSION['remember_me'] = true;
    }
    
    return ['success' => true, 'user' => $user];
}

// Register new user
function registerUser($email, $password, $firstName, $lastName, $company = null) {
    $pdo = getDBConnection();
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, company, role, tickets) VALUES (?, ?, ?, ?, ?, 'user', 0)");
    
    try {
        $stmt->execute([$email, $hashedPassword, $firstName, $lastName, $company]);
        $userId = $pdo->lastInsertId();
        
        return ['success' => true, 'user_id' => $userId];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

// Logout user
function logoutUser() {
    session_destroy();
    session_start();
}

// Require login (redirect if not logged in)
function requireLogin($redirectTo = '/login.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

// Require admin (redirect if not admin)
function requireAdmin($redirectTo = '/track') {
    if (!isAdmin()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

// Update user tickets
function updateUserTickets($userId, $tickets) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET tickets = tickets + ? WHERE id = ?");
    $stmt->execute([$tickets, $userId]);
    
    // Update session
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
        $_SESSION['user_tickets'] = ($user['tickets'] ?? 0) + $tickets;
    }
    
    return true;
}

// Use ticket
function useTicket($userId, $tnId, $trackingNumber, $carrier) {
    $pdo = getDBConnection();
    
    try {
        $pdo->beginTransaction();
        
        // Check if user has tickets
        $stmt = $pdo->prepare("SELECT tickets FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user || $user['tickets'] < 1) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Insufficient tickets'];
        }
        
        // Deduct ticket
        $stmt = $pdo->prepare("UPDATE users SET tickets = tickets - 1 WHERE id = ?");
        $stmt->execute([$userId]);
        
        // Log usage
        $stmt = $pdo->prepare("INSERT INTO ticket_usage (user_id, tn_id, tracking_number, carrier) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $tnId, $trackingNumber, $carrier]);
        
        $pdo->commit();
        
        // Update session
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            $_SESSION['user_tickets'] = $user['tickets'] - 1;
        }
        
        return ['success' => true, 'remaining_tickets' => $user['tickets'] - 1];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Failed to use ticket: ' . $e->getMessage()];
    }
}

// Change password
function changePassword($userId, $currentPassword, $newPassword) {
    $pdo = getDBConnection();
    
    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $userId]);
    
    return ['success' => true, 'message' => 'Password changed successfully'];
}
