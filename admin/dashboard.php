<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_admin();

// Fetch stats
$total_resellers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'reseller'")->fetchColumn();
$total_sales = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_commission = $pdo->query("SELECT IFNULL(SUM(amount),0) FROM commissions")->fetchColumn();

include '../includes/header.php';
?>

<h2 class="text-3xl font-bold mb-6">Admin Dashboard</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-xl font-semibold mb-2">Total Resellers</h3>
        <p class="text-4xl font-bold text-green-600"><?php echo $total_resellers; ?></p>
    </div>
    <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-xl font-semibold mb-2">Total Sales</h3>
        <p class="text-4xl font-bold text-green-600"><?php echo $total_sales; ?></p>
    </div>
    <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-xl font-semibold mb-2">Total Commission</h3>
        <p class="text-4xl font-bold text-green-600">Rp <?php echo number_format($total_commission, 0, ',', '.'); ?></p>
    </div>
</div>

<div class="space-x-4">
    <a href="resellers.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Manage Resellers</a>
    <a href="products.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Manage Products</a>
    <a href="orders.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Manage Orders</a>
    <a href="commissions.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Manage Commissions</a>
</div>

<?php include '../includes/footer.php'; ?>
