<?php
// Database connection settings
$host = 'localhost';
$db   = 'qurban_app';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Log error details
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Show user-friendly error message
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');
?>
