<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_reseller();

$reseller_id = $_SESSION['user_id'];

// Fetch sales stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE reseller_id = ?");
$stmt->execute([$reseller_id]);
$sales_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT IFNULL(SUM(amount),0) FROM commissions WHERE reseller_id = ?");
$stmt->execute([$reseller_id]);
$commission_total = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT buyer_name) FROM orders WHERE reseller_id = ?");
$stmt->execute([$reseller_id]);
$customer_count = $stmt->fetchColumn();

$affiliate_link = get_affiliate_link($reseller_id);

include '../includes/header.php';
?>

<h2 class="text-3xl font-bold mb-6">Reseller Dashboard</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-xl font-semibold mb-2">Sales Count</h3>
        <p class="text-4xl font-bold text-green-600"><?php echo $sales_count; ?></p>
    </div>
    <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-xl font-semibold mb-2">Total Commission</h3>
        <p class="text-4xl font-bold text-green-600">Rp <?php echo number_format($commission_total, 0, ',', '.'); ?></p>
    </div>
    <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-xl font-semibold mb-2">Customers</h3>
        <p class="text-4xl font-bold text-green-600"><?php echo $customer_count; ?></p>
    </div>
</div>

<div class="mb-6">
    <h3 class="text-xl font-semibold mb-2">Your Unique Affiliate Link</h3>
    <input type="text" readonly class="w-full p-2 border rounded" value="<?php echo htmlspecialchars($affiliate_link); ?>" />
</div>

<div class="space-x-4">
    <a href="/catalog.php?ref=<?php echo urlencode($reseller_id); ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">View Catalog</a>
    <a href="marketing.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Marketing Kit</a>
    <a href="stats.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">View Stats</a>
</div>

<?php include '../includes/footer.php'; ?>
