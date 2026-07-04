<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Logout user
logoutUser();

// Redirect to home
header('Location: /?logout=1');
exit;
