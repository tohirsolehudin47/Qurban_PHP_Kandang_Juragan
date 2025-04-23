<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_admin();

// Fetch orders with product and reseller info
$stmt = $pdo->query("SELECT o.id, o.buyer_name, o.buyer_phone, o.order_date, p.name AS product_name, u.name AS reseller_name
                     FROM orders o
                     JOIN products p ON o.product_id = p.id
                     JOIN users u ON o.reseller_id = u.id
                     ORDER BY o.order_date DESC");
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<h2 class="text-3xl font-bold mb-6">Manage Orders</h2>

<table class="min-w-full bg-white rounded shadow">
    <thead>
        <tr>
            <th class="py-2 px-4 border-b">Order ID</th>
            <th class="py-2 px-4 border-b">Buyer Name</th>
            <th class="py-2 px-4 border-b">Buyer Phone</th>
            <th class="py-2 px-4 border-b">Product</th>
            <th class="py-2 px-4 border-b">Reseller</th>
            <th class="py-2 px-4 border-b">Order Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td class="py-2 px-4 border-b"><?php echo $order['id']; ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($order['buyer_phone']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($order['product_name']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($order['reseller_name']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo $order['order_date']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
