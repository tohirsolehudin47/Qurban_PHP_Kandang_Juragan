<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_reseller();

$reseller_id = $_SESSION['user_id'];

// Get date range filters
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today

// Fetch overall statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT o.id) as total_orders,
        COUNT(DISTINCT o.customer_id) as total_customers,
        COALESCE(SUM(o.total_amount), 0) as total_sales,
        COALESCE(SUM(c.amount), 0) as total_commission
    FROM orders o
    LEFT JOIN commissions c ON o.id = c.order_id
    WHERE o.reseller_id = ?
    AND o.created_at BETWEEN ? AND ?
");
$stmt->execute([$reseller_id, $date_from, $date_to]);
$overall_stats = $stmt->fetch();

// Fetch click statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_clicks,
        COUNT(DISTINCT ip_address) as unique_clicks
    FROM link_clicks lc
    JOIN affiliate_links al ON lc.affiliate_link_id = al.id
    WHERE al.reseller_id = ?
    AND lc.clicked_at BETWEEN ? AND ?
");
$stmt->execute([$reseller_id, $date_from, $date_to]);
$click_stats = $stmt->fetch();

// Calculate conversion rate
$conversion_rate = $click_stats['total_clicks'] > 0 
    ? ($overall_stats['total_orders'] / $click_stats['total_clicks']) * 100 
    : 0;

// Fetch sales by product category
$stmt = $pdo->prepare("
    SELECT 
        pc.name as category_name,
        COUNT(oi.id) as items_sold,
        COALESCE(SUM(oi.price * oi.quantity), 0) as category_sales
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN product_categories pc ON p.category_id = pc.id
    WHERE o.reseller_id = ?
    AND o.created_at BETWEEN ? AND ?
    GROUP BY pc.id, pc.name
");
$stmt->execute([$reseller_id, $date_from, $date_to]);
$category_stats = $stmt->fetchAll();

// Fetch monthly trends
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(o.created_at, '%Y-%m') as month,
        COUNT(DISTINCT o.id) as orders,
        COALESCE(SUM(o.total_amount), 0) as sales,
        COALESCE(SUM(c.amount), 0) as commission
    FROM orders o
    LEFT JOIN commissions c ON o.id = c.order_id
    WHERE o.reseller_id = ?
    AND o.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
    ORDER BY month DESC
");
$stmt->execute([$reseller_id]);
$monthly_trends = $stmt->fetchAll();

// Fetch recent activities
$stmt = $pdo->prepare("
    (SELECT 
        'order' as type,
        o.created_at as date,
        o.order_code as reference,
        o.total_amount as amount
    FROM orders o
    WHERE o.reseller_id = ?
    AND o.created_at BETWEEN ? AND ?)
    UNION ALL
    (SELECT 
        'click' as type,
        lc.clicked_at as date,
        al.link_type as reference,
        NULL as amount
    FROM link_clicks lc
    JOIN affiliate_links al ON lc.affiliate_link_id = al.id
    WHERE al.reseller_id = ?
    AND lc.clicked_at BETWEEN ? AND ?)
    ORDER BY date DESC
    LIMIT 10
");
$stmt->execute([$reseller_id, $date_from, $date_to, $reseller_id, $date_from, $date_to]);
$recent_activities = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Statistik Performa</h1>
        
        <!-- Date Range Filter -->
        <form class="flex space-x-4">
            <div>
                <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                       class="border rounded px-3 py-2">
            </div>
            <div>
                <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                       class="border rounded px-3 py-2">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Filter
            </button>
        </form>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Total Penjualan</h3>
                <i class="fas fa-shopping-cart text-green-600"></i>
            </div>
            <p class="text-3xl font-bold text-green-600">
                Rp <?php echo number_format($overall_stats['total_sales'], 0, ',', '.'); ?>
            </p>
            <p class="text-sm text-gray-600 mt-2">
                <?php echo $overall_stats['total_orders']; ?> pesanan
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Total Komisi</h3>
                <i class="fas fa-money-bill-wave text-green-600"></i>
            </div>
            <p class="text-3xl font-bold text-green-600">
                Rp <?php echo number_format($overall_stats['total_commission'], 0, ',', '.'); ?>
            </p>
            <p class="text-sm text-gray-600 mt-2">
                <?php echo $overall_stats['total_customers']; ?> pelanggan
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Total Klik</h3>
                <i class="fas fa-mouse-pointer text-blue-600"></i>
            </div>
            <p class="text-3xl font-bold text-blue-600">
                <?php echo number_format($click_stats['total_clicks']); ?>
            </p>
            <p class="text-sm text-gray-600 mt-2">
                <?php echo number_format($click_stats['unique_clicks']); ?> klik unik
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Konversi</h3>
                <i class="fas fa-chart-line text-purple-600"></i>
            </div>
            <p class="text-3xl font-bold text-purple-600">
                <?php echo number_format($conversion_rate, 1); ?>%
            </p>
            <p class="text-sm text-gray-600 mt-2">
                rata-rata konversi
            </p>
        </div>
    </div>

    <!-- Sales by Category -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Penjualan per Kategori</h2>
            <div class="space-y-4">
                <?php foreach ($category_stats as $stat): ?>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium"><?php echo htmlspecialchars($stat['category_name']); ?></p>
                            <p class="text-sm text-gray-600">
                                <?php echo $stat['items_sold']; ?> item terjual
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium">
                                Rp <?php echo number_format($stat['category_sales'], 0, ',', '.'); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Aktivitas Terbaru</h2>
            <div class="space-y-4">
                <?php foreach ($recent_activities as $activity): ?>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            <?php echo $activity['type'] === 'order' 
                                ? 'bg-green-100 text-green-600' 
                                : 'bg-blue-100 text-blue-600'; ?>">
                            <i class="fas <?php echo $activity['type'] === 'order' 
                                ? 'fa-shopping-cart' 
                                : 'fa-mouse-pointer'; ?>"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium">
                                <?php echo $activity['type'] === 'order' 
                                    ? 'Pesanan: ' . htmlspecialchars($activity['reference'])
                                    : 'Klik pada ' . ucfirst($activity['reference']) . ' Link'; ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($activity['date'])); ?>
                                <?php if ($activity['amount']): ?>
                                    - Rp <?php echo number_format($activity['amount'], 0, ',', '.'); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Tren Bulanan</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Bulan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pesanan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Penjualan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Komisi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($monthly_trends as $trend): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo date('F Y', strtotime($trend['month'] . '-01')); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo $trend['orders']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                Rp <?php echo number_format($trend['sales'], 0, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                Rp <?php echo number_format($trend['commission'], 0, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
