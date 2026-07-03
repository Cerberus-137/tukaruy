<?php
// Configuration file for Tukeruy

// API Configuration
define('API_BASE_URL', 'https://v2.tracktaco.com');
define('API_KEY', 'tt_test_jg7QMZc33E93iis_fKwxot5j43bxUw0KymEAMZmYEm0'); // Sandbox API key

// Database configuration (optional for caching)
define('DB_HOST', 'localhost');
define('DB_NAME', 'tukeruy');
define('DB_USER', 'root');
define('DB_PASS', '');

// App configuration
define('ITEMS_PER_PAGE', 25);
define('MAX_ITEMS_PER_PAGE', 50);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
