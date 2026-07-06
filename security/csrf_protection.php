<?php
/**
 * CSRF Protection Helper
 * 
 * Usage in forms:
 * <?php require_once 'security/csrf_protection.php'; ?>
 * <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
 * 
 * Usage in form processors:
 * require_once 'security/csrf_protection.php';
 * if (!verifyCSRFToken($_POST['csrf_token'])) {
 *     die('CSRF token validation failed');
 * }
 */

/**
 * Generate CSRF token and store in session
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token']) || !isset($token)) {
        return false;
    }
    
    // Use hash_equals to prevent timing attacks
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate CSRF token (call after successful form submission)
 */
function regenerateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
