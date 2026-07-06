<?php
/**
 * Rate Limiter - Prevent Brute Force Attacks
 * 
 * Usage:
 * require_once 'security/rate_limiter.php';
 * 
 * if (!checkRateLimit('login', 5, 300)) {
 *     die('Too many attempts. Please try again later.');
 * }
 */

/**
 * Check rate limit for specific action
 * 
 * @param string $action Action identifier (e.g., 'login', 'register', 'api_call')
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if within limit, false if exceeded
 */
function checkRateLimit($action, $maxAttempts = 5, $timeWindow = 300) {
    $ip = getUserIP();
    $key = "rate_limit_{$action}_{$ip}";
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $now = time();
    
    // Initialize or get attempts
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 1,
            'first_attempt' => $now,
            'blocked_until' => null
        ];
        return true;
    }
    
    $data = $_SESSION[$key];
    
    // Check if currently blocked
    if ($data['blocked_until'] && $now < $data['blocked_until']) {
        return false;
    }
    
    // Reset if time window passed
    if ($now - $data['first_attempt'] > $timeWindow) {
        $_SESSION[$key] = [
            'attempts' => 1,
            'first_attempt' => $now,
            'blocked_until' => null
        ];
        return true;
    }
    
    // Increment attempts
    $_SESSION[$key]['attempts']++;
    
    // Block if exceeded
    if ($_SESSION[$key]['attempts'] > $maxAttempts) {
        $_SESSION[$key]['blocked_until'] = $now + $timeWindow;
        
        // Log attack attempt
        error_log("SECURITY: Rate limit exceeded for action '$action' from IP: $ip");
        
        return false;
    }
    
    return true;
}

/**
 * Reset rate limit for specific action (e.g., after successful login)
 */
function resetRateLimit($action) {
    $ip = getUserIP();
    $key = "rate_limit_{$action}_{$ip}";
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    unset($_SESSION[$key]);
}

/**
 * Get user IP address (handles proxies)
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        // Cloudflare
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Proxy
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        // Nginx
        return $_SERVER['HTTP_X_REAL_IP'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Get remaining time until unblock
 */
function getRateLimitTimeRemaining($action) {
    $ip = getUserIP();
    $key = "rate_limit_{$action}_{$ip}";
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION[$key]) || !$_SESSION[$key]['blocked_until']) {
        return 0;
    }
    
    $remaining = $_SESSION[$key]['blocked_until'] - time();
    return max(0, $remaining);
}
