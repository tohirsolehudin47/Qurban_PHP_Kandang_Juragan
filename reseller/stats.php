<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_reseller();

$reseller_id = $_SESSION['user_id'];

// Fetch clicks count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM clicks WHERE reseller_id = ?");
$stmt->execute([$reseller_id]);
$clicks_count = $stmt->fetchColumn();

// Fetch sales count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE reseller_id = ?");
$stmt->execute([$reseller_id]);
$sales_count = $stmt->fetchColumn();

include '../includes/header.php';
?>

<h2 class="text-3xl font-bold mb-6">Your Stats</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-xl font-semibold mb-2">Affiliate Link Clicks</h3>
        <p class="text-4xl font-bold text-green-600"><?php echo $clicks_count; ?></p>
    </div>
    <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-xl font-semibold mb-2">Sales Made</h3>
        <p class="text-4xl font-bold text-green-600"><?php echo $sales_count; ?></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
