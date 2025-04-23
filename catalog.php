<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

session_start();

$ref = $_GET['ref'] ?? null;
if ($ref) {
    // Track clicks for all products viewed on catalog page for this reseller
    // For simplicity, track clicks for each product on page load
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'reseller'");
    $stmt->execute([$ref]);
    if ($stmt->fetch()) {
        // Track click for each product
        $products = $pdo->query("SELECT id FROM products")->fetchAll();
        foreach ($products as $product) {
            track_click($pdo, $ref, $product['id']);
        }
    }
}

include 'includes/header.php';

// Fetch products grouped by category
$stmt = $pdo->query("SELECT * FROM products ORDER BY category, name");
$products = $stmt->fetchAll();

$grouped = [];
foreach ($products as $product) {
    $grouped[$product['category']][] = $product;
}
?>

<h2 class="text-3xl font-bold mb-6">Qurban Product Catalog</h2>

<?php foreach ($grouped as $category => $items): ?>
    <h3 class="text-2xl font-semibold mb-4"><?php echo htmlspecialchars($category); ?></h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <?php foreach ($items as $product): ?>
            <div class="bg-white rounded shadow p-4 flex flex-col">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="mb-4 h-48 object-cover rounded" />
                <h4 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h4>
                <p class="mb-2"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <p class="font-bold mb-4">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                <?php
                $wa_message = "Halo Admin, saya tertarik dengan produk Qurban: " . $product['name'];
                if ($ref) {
                    $wa_message .= " (Kode Reseller: $ref)";
                }
                $wa_link = "https://wa.me/6281545842432?text=" . urlencode($wa_message);
                ?>
                <a href="<?php echo $wa_link; ?>" target="_blank" class="mt-auto bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-center">
                    <i class="fab fa-whatsapp mr-2"></i> Order via WhatsApp
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<?php include 'includes/footer.php'; ?>
