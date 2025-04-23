<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_reseller();

$reseller_id = $_SESSION['user_id'];

// Fetch reseller profile
$profile = get_reseller_profile($pdo, $reseller_id);

// Fetch statistics
$stats = get_reseller_stats($pdo, $reseller_id);

// Fetch recent orders
$recent_orders = $pdo->prepare("
    SELECT o.*, c.name as customer_name, p.name as product_name
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.reseller_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");
$recent_orders->execute([$reseller_id]);
$orders = $recent_orders->fetchAll();

// Fetch affiliate links
$stmt = $pdo->prepare("
    SELECT al.*, 
           (SELECT COUNT(*) FROM link_clicks WHERE affiliate_link_id = al.id) as click_count
    FROM affiliate_links al
    WHERE al.reseller_id = ?
    ORDER BY al.created_at DESC
");
$stmt->execute([$reseller_id]);
$affiliate_links = $stmt->fetchAll();

// Fetch recent clicks
$stmt = $pdo->prepare("
    SELECT lc.*, al.link_type, al.full_url
    FROM link_clicks lc
    JOIN affiliate_links al ON lc.affiliate_link_id = al.id
    WHERE al.reseller_id = ?
    ORDER BY lc.clicked_at DESC
    LIMIT 5
");
$stmt->execute([$reseller_id]);
$recent_clicks = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Welcome Section -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">
                    Selamat Datang, <?php echo htmlspecialchars($profile['full_name']); ?>!
                </h1>
                <p class="text-gray-600">
                    Status: 
                    <span class="inline-block px-2 py-1 text-xs rounded
                        <?php echo $profile['approved_at'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                        <?php echo $profile['approved_at'] ? 'Aktif' : 'Menunggu Persetujuan'; ?>
                    </span>
                </p>
            </div>
            <img src="<?php echo $_SESSION['profile_image'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($profile['full_name']); ?>" 
                 alt="Profile" 
                 class="w-16 h-16 rounded-full">
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Total Penjualan</h3>
                <i class="fas fa-shopping-cart text-green-600"></i>
            </div>
            <p class="text-3xl font-bold text-green-600"><?php echo $stats['total_sales']; ?></p>
            <p class="text-sm text-gray-600 mt-2">transaksi</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Total Komisi</h3>
                <i class="fas fa-money-bill-wave text-green-600"></i>
            </div>
            <p class="text-3xl font-bold text-green-600">
                Rp <?php echo number_format($stats['total_commission'], 0, ',', '.'); ?>
            </p>
            <p class="text-sm text-gray-600 mt-2">pendapatan</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Total Klik</h3>
                <i class="fas fa-mouse-pointer text-blue-600"></i>
            </div>
            <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total_clicks']; ?></p>
            <p class="text-sm text-gray-600 mt-2">affiliate link</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Konversi</h3>
                <i class="fas fa-chart-line text-purple-600"></i>
            </div>
            <p class="text-3xl font-bold text-purple-600">
                <?php 
                echo $stats['total_clicks'] > 0 
                    ? number_format(($stats['total_sales'] / $stats['total_clicks']) * 100, 1) 
                    : '0.0';
                ?>%
            </p>
            <p class="text-sm text-gray-600 mt-2">rata-rata</p>
        </div>
    </div>

    <!-- Affiliate Links -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Link Affiliate Anda</h2>
            <button onclick="generateNewLink()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-plus mr-2"></i>Buat Link Baru
            </button>
        </div>
        
        <div class="space-y-4">
            <?php foreach ($affiliate_links as $link): ?>
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-semibold">
                                <?php echo ucfirst($link['link_type']); ?> Link
                                <span class="text-sm text-gray-500">(<?php echo $link['unique_code']; ?>)</span>
                            </p>
                            <input type="text" 
                                   value="<?php echo htmlspecialchars($link['full_url']); ?>" 
                                   class="text-sm text-gray-600 w-full bg-gray-50 px-3 py-2 mt-2 rounded" 
                                   readonly>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-green-600"><?php echo $link['click_count']; ?></p>
                            <p class="text-sm text-gray-600">klik</p>
                        </div>
                    </div>
                    <div class="mt-4 space-x-2">
                        <button onclick="copyToClipboard('<?php echo $link['full_url']; ?>')" 
                                class="text-blue-600 hover:underline">
                            <i class="fas fa-copy mr-1"></i>Copy
                        </button>
                        <a href="https://wa.me/?text=<?php echo urlencode($link['full_url']); ?>" 
                           target="_blank"
                           class="text-green-600 hover:underline">
                            <i class="fab fa-whatsapp mr-1"></i>Share WhatsApp
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold mb-4">Pesanan Terbaru</h3>
            <?php if ($orders): ?>
                <div class="space-y-4">
                    <?php foreach ($orders as $order): ?>
                        <div class="border-b pb-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-semibold"><?php echo htmlspecialchars($order['order_code']); ?></p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars($order['customer_name']); ?> - 
                                        <?php echo htmlspecialchars($order['product_name']); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold">
                                        Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                    </p>
                                    <span class="inline-block px-2 py-1 text-xs rounded
                                        <?php echo match($order['status']) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        }; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600">Belum ada pesanan</p>
            <?php endif; ?>
        </div>

        <!-- Recent Clicks -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold mb-4">Aktivitas Klik Terbaru</h3>
            <?php if ($recent_clicks): ?>
                <div class="space-y-4">
                    <?php foreach ($recent_clicks as $click): ?>
                        <div class="border-b pb-4">
                            <p class="font-semibold">
                                <?php echo ucfirst($click['link_type']); ?> Link
                            </p>
                            <p class="text-sm text-gray-600">
                                <?php echo date('d M Y H:i', strtotime($click['clicked_at'])); ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                IP: <?php echo $click['ip_address']; ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600">Belum ada aktivitas klik</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="/catalog.php" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-shopping-bag text-3xl text-green-600 mr-4"></i>
                <div>
                    <h3 class="text-lg font-semibold">Katalog Produk</h3>
                    <p class="text-gray-600">Lihat semua produk qurban</p>
                </div>
            </div>
        </a>

        <a href="/reseller/marketing.php" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-bullhorn text-3xl text-blue-600 mr-4"></i>
                <div>
                    <h3 class="text-lg font-semibold">Marketing Kit</h3>
                    <p class="text-gray-600">Download materi promosi</p>
                </div>
            </div>
        </a>

        <a href="/reseller/stats.php" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <i class="fas fa-chart-bar text-3xl text-purple-600 mr-4"></i>
                <div>
                    <h3 class="text-lg font-semibold">Statistik Detail</h3>
                    <p class="text-gray-600">Analisis performa Anda</p>
                </div>
            </div>
        </a>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Link berhasil disalin!');
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
}

function generateNewLink() {
    // Implement link generation logic
    alert('Fitur akan segera tersedia!');
}
</script>

<?php include '../includes/footer.php'; ?>
