<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_admin();

// Fetch commissions with order and reseller info
$stmt = $pdo->query("SELECT c.id, c.amount, c.created_at, u.name AS reseller_name, o.id AS order_id
                     FROM commissions c
                     JOIN users u ON c.reseller_id = u.id
                     JOIN orders o ON c.order_id = o.id
                     ORDER BY c.created_at DESC");
$commissions = $stmt->fetchAll();

include '../includes/header.php';
?>

<h2 class="text-3xl font-bold mb-6">Manage Commissions</h2>

<table class="min-w-full bg-white rounded shadow">
    <thead>
        <tr>
            <th class="py-2 px-4 border-b">Commission ID</th>
            <th class="py-2 px-4 border-b">Reseller</th>
            <th class="py-2 px-4 border-b">Order ID</th>
            <th class="py-2 px-4 border-b">Amount</th>
            <th class="py-2 px-4 border-b">Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($commissions as $commission): ?>
            <tr>
                <td class="py-2 px-4 border-b"><?php echo $commission['id']; ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($commission['reseller_name']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo $commission['order_id']; ?></td>
                <td class="py-2 px-4 border-b">Rp <?php echo number_format($commission['amount'], 0, ',', '.'); ?></td>
                <td class="py-2 px-4 border-b"><?php echo $commission['created_at']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
