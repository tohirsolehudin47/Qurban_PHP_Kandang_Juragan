<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_reseller() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'reseller';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /auth/login.php');
        exit();
    }
}

function require_admin() {
    if (!is_admin()) {
        header('HTTP/1.1 403 Forbidden');
        echo "Access denied.";
        exit();
    }
}

function require_reseller() {
    if (!is_reseller()) {
        header('HTTP/1.1 403 Forbidden');
        echo "Access denied.";
        exit();
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Generate unique affiliate link for reseller
function get_affiliate_link($reseller_id) {
    $base_url = "http://yourdomain.com/catalog.php";
    return $base_url . "?ref=" . urlencode($reseller_id);
}

// Track affiliate clicks
function track_click($pdo, $reseller_id, $product_id) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $pdo->prepare("INSERT INTO clicks (reseller_id, product_id, ip_address) VALUES (?, ?, ?)");
    $stmt->execute([$reseller_id, $product_id, $ip]);
}
?>
