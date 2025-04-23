<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Get user info before destroying session
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'unknown';
$role = $_SESSION['role'] ?? 'unknown';

// Clear all session data
session_start();
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Set flash message for next page
session_start();
$_SESSION['flash_message'] = "Anda telah berhasil keluar dari sistem.";
$_SESSION['flash_type'] = "success";

// Redirect to login page
header('Location: /auth/login.php');
exit();
?>
