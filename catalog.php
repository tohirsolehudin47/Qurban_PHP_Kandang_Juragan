<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get affiliate code from URL
$ref_code = $_GET['ref'] ?? null;
$reseller_id = null;

// Track affiliate click if ref code exists
if ($ref_code) {
    $stmt = $pdo->prepare("
        SELECT id, reseller_id 
        FROM affiliate_links 
        WHERE unique_code = ? AND link_type = 'catalog'
    ");
    $stmt->execute([$ref_code]);
    $affiliate_link = $stmt->fetch();
    
    if ($affiliate_link) {
        $reseller_id = $affiliate_link['reseller_id'];
        track_affiliate_click($pdo, $affiliate_link['id']);
    }
}

// Get selected category
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Fetch categories
$categories = $pdo->query("SELECT * FROM product_categories ORDER BY name")->fetchAll();

// Build product query
$query = "
    SELECT p.*, pc.name as category_name 
    FROM products p 
    JOIN product_categories pc ON p.category_id = pc.id 
    WHERE p.is_available = 1
";
if ($category_id) {
    $query .= " AND p.category_id = " . $category_id;
}
$query .= " ORDER BY p.category_id, p.name";

$products = $pdo->query($query)->fetchAll();

// Group products by category
$grouped_products = [];
foreach ($products as $product) {
    $grouped_products[$product['category_name']][] = $product;
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Category Filter -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-4">Katalog Hewan Qurban</h2>
        <div class="flex flex-wrap gap-2">
            <a href="catalog.php<?php echo $ref_code ? "?ref=$ref_code" : ''; ?>" 
               class="px-4 py-2 rounded-full <?php echo !$category_id ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                Semua
            </a>
            <?php foreach ($categories as $category): ?>
                <a href="catalog.php?category=<?php echo $category['id']; ?><?php echo $ref_code ? "&ref=$ref_code" : ''; ?>" 
                   class="px-4 py-2 rounded-full <?php echo $category_id === $category['id'] ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Products Grid -->
    <?php foreach ($grouped_products as $category_name => $category_products): ?>
        <div class="mb-12">
            <h3 class="text-xl font-semibold mb-6"><?php echo htmlspecialchars($category_name); ?></h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($category_products as $product): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <!-- Product Images Slider -->
                        <div class="relative h-64">
                            <?php if ($product['image1']): ?>
                                <img src="<?php echo htmlspecialchars($product['image1']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="w-full h-full object-cover">
                            <?php endif; ?>
                            <?php if ($product['video_url']): ?>
                                <a href="<?php echo htmlspecialchars($product['video_url']); ?>" 
                                   target="_blank"
                                   class="absolute top-2 right-2 bg-black bg-opacity-50 text-white p-2 rounded-full">
                                    <i class="fas fa-play"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Product Details -->
                        <div class="p-4">
                            <h4 class="text-lg font-semibold mb-2">
                                <?php echo htmlspecialchars($product['name']); ?>
                                <span class="text-sm font-normal text-gray-500">(<?php echo htmlspecialchars($product['code']); ?>)</span>
                            </h4>
                            
                            <div class="mb-4">
                                <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-weight-hanging w-6"></i>
                                    <span><?php echo number_format($product['weight'], 1); ?> kg</span>
                                </div>
                                <?php if ($product['age']): ?>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-calendar w-6"></i>
                                        <span><?php echo $product['age']; ?> bulan</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($product['location']): ?>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-map-marker-alt w-6"></i>
                                        <span><?php echo htmlspecialchars($product['location']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center justify-between mb-4">
                                <div class="text-xl font-bold text-green-600">
                                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    Stok: <?php echo $product['stock']; ?>
                                </div>
                            </div>

                            <?php
                            // Prepare WhatsApp message
                            $message = "Assalamualaikum, saya tertarik dengan produk qurban:\n\n";
                            $message .= "Nama: " . $product['name'] . "\n";
                            $message .= "Kode: " . $product['code'] . "\n";
                            $message .= "Harga: Rp " . number_format($product['price'], 0, ',', '.') . "\n\n";
                            
                            if ($reseller_id) {
                                $message .= "Kode Reseller: " . $ref_code . "\n";
                            }
                            
                            $message .= "Mohon informasi lebih lanjut.";
                            
                            $wa_link = "https://wa.me/6281234567890?text=" . urlencode($message);
                            ?>

                            <a href="<?php echo $wa_link; ?>" 
                               onclick="trackWhatsAppClick(<?php echo $product['id']; ?>)"
                               target="_blank"
                               class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fab fa-whatsapp mr-2"></i>
                                Pesan via WhatsApp
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function trackWhatsAppClick(productId) {
    // Track WhatsApp click
    fetch('/track_whatsapp.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            reseller_id: <?php echo $reseller_id ? $reseller_id : 'null'; ?>,
            type: 'product'
        })
    });
}
</script>

<?php include 'includes/footer.php'; ?>
