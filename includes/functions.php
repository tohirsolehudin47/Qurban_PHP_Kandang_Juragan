<?php
session_start();

// Authentication Functions
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
    require_login();
    if (!is_admin()) {
        header('HTTP/1.1 403 Forbidden');
        echo "Access denied.";
        exit();
    }
}

function require_reseller() {
    require_login();
    if (!is_reseller()) {
        header('HTTP/1.1 403 Forbidden');
        echo "Access denied.";
        exit();
    }
}

// Data Sanitization
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Generate Random Code
function generate_unique_code($prefix = '', $length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = $prefix;
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// Affiliate Link Functions
function create_affiliate_link($pdo, $reseller_id, $type) {
    $unique_code = generate_unique_code('REF', 6);
    $base_url = "http://" . $_SERVER['HTTP_HOST'];
    
    switch ($type) {
        case 'qurban':
            $full_url = "$base_url/catalog.php?ref=$unique_code";
            break;
        case 'savings':
            $full_url = "$base_url/savings.php?ref=$unique_code";
            break;
        case 'catalog':
            $full_url = "$base_url/catalog.php?ref=$unique_code";
            break;
        default:
            throw new Exception("Invalid link type");
    }

    $stmt = $pdo->prepare("INSERT INTO affiliate_links (reseller_id, link_type, unique_code, full_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$reseller_id, $type, $unique_code, $full_url]);
    
    return $full_url;
}

// Track affiliate link click
function track_affiliate_click($pdo, $affiliate_link_id) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $referrer = $_SERVER['HTTP_REFERER'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO link_clicks (affiliate_link_id, ip_address, user_agent, referrer) VALUES (?, ?, ?, ?)");
    $stmt->execute([$affiliate_link_id, $ip, $user_agent, $referrer]);
}

// WhatsApp Functions
function get_whatsapp_template($pdo, $template_name, $variables = []) {
    $stmt = $pdo->prepare("SELECT content FROM whatsapp_templates WHERE name = ?");
    $stmt->execute([$template_name]);
    $template = $stmt->fetchColumn();

    if (!$template) {
        return false;
    }

    foreach ($variables as $key => $value) {
        $template = str_replace("{{{$key}}}", $value, $template);
    }

    return $template;
}

function track_whatsapp_click($pdo, $reseller_id, $type, $reference_id = null) {
    $stmt = $pdo->prepare("INSERT INTO whatsapp_tracking (reseller_id, type, reference_id) VALUES (?, ?, ?)");
    $stmt->execute([$reseller_id, $type, $reference_id]);
}

// Commission Functions
function calculate_commission($pdo, $product_id, $quantity = 1) {
    $stmt = $pdo->prepare("
        SELECT cr.amount 
        FROM products p 
        JOIN commission_rates cr ON p.category_id = cr.category_id 
        WHERE p.id = ? AND p.weight BETWEEN cr.min_weight AND cr.max_weight
    ");
    $stmt->execute([$product_id]);
    $rate = $stmt->fetchColumn();

    return $rate * $quantity;
}

// Image Upload Function
function handle_image_upload($file, $destination_path) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }

    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        return false;
    }

    $filename = uniqid() . '_' . basename($file['name']);
    $upload_path = __DIR__ . '/../public/' . $destination_path;
    
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $upload_path . '/' . $filename)) {
        return $destination_path . '/' . $filename;
    }

    return false;
}

// Format Currency
function format_currency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Get Reseller Profile
function get_reseller_profile($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT rp.*, rb.bank_name, rb.account_number, rb.account_holder
        FROM reseller_profiles rp
        LEFT JOIN reseller_banks rb ON rp.user_id = rb.user_id
        WHERE rp.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Get Reseller Stats
function get_reseller_stats($pdo, $reseller_id) {
    // Total sales
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE reseller_id = ?");
    $stmt->execute([$reseller_id]);
    $total_sales = $stmt->fetchColumn();

    // Total commission
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE reseller_id = ?");
    $stmt->execute([$reseller_id]);
    $total_commission = $stmt->fetchColumn();

    // Total clicks
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM link_clicks lc 
        JOIN affiliate_links al ON lc.affiliate_link_id = al.id 
        WHERE al.reseller_id = ?
    ");
    $stmt->execute([$reseller_id]);
    $total_clicks = $stmt->fetchColumn();

    return [
        'total_sales' => $total_sales,
        'total_commission' => $total_commission,
        'total_clicks' => $total_clicks
    ];
}

// Generate Order Code
function generate_order_code() {
    return 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -5));
}

?>
