<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_admin();

// Fetch key statistics
$stats = [
    // Users/Resellers
    'total_resellers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'reseller'")->fetchColumn(),
    'active_resellers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'reseller' AND is_active = 1")->fetchColumn(),
    'pending_resellers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'reseller' AND is_active = 0")->fetchColumn(),
    
    // Products
    'total_products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'available_products' => $pdo->query("SELECT COUNT(*) FROM products WHERE is_available = 1")->fetchColumn(),
    
    // Orders
    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'completed_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn(),
    
    // Financial
    'total_sales' => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders")->fetchColumn(),
    'total_commissions' => $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM commissions")->fetchColumn(),
    'pending_commissions' => $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE status = 'pending'")->fetchColumn()
];

// Fetch recent orders
$recent_orders = $pdo->query("
    SELECT o.*, c.name as customer_name, u.username as reseller_name
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    LEFT JOIN users u ON o.reseller_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll();

// Fetch recent reseller registrations
$recent_registrations = $pdo->query("
    SELECT u.*, rp.full_name
    FROM users u
    JOIN reseller_profiles rp ON u.id = rp.user_id
    WHERE u.role = 'reseller'
    ORDER BY u.created_at DESC
    LIMIT 5
")->fetchAll();

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Dashboard Admin</h1>
        <div class="space-x-2">
            <a href="/admin/products.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-plus mr-2"></i>Tambah Produk
            </a>
            <a href="/admin/resellers.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-users mr-2"></i>Kelola Reseller
            </a>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Sales Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Total Penjualan</h3>
                <i class="fas fa-chart-line text-green-600 text-xl"></i>
            </div>
            <p class="text-3xl font-bold text-green-600">
                Rp <?php echo number_format($stats['total_sales'], 0, ',', '.'); ?>
            </p>
            <p class="text-sm text-gray-600 mt-2">
                <?php echo $stats['total_orders']; ?> pesanan
            </p>
        </div>

        <!-- Reseller Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Reseller Aktif</h3>
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
            <p class="text-3xl font-bold text-blue-600">
                <?php echo $stats['active_resellers']; ?>
            </p>
            <p class="text-sm text-gray-600 mt-2">
                <?php echo $stats['pending_resellers']; ?> menunggu persetujuan
            </p>
        </div>

        <!-- Commission Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Total Komisi</h3>
                <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
            </div>
            <p class="text-3xl font-bold text-yellow-600">
                Rp <?php echo number_format($stats['total_commissions'], 0, ',', '.'); ?>
            </p>
            <p class="text-sm text-gray-600 mt-2">
                Rp <?php echo number_format($stats['pending_commissions'], 0, ',', '.'); ?> pending
            </p>
        </div>

        <!-- Product Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Produk Tersedia</h3>
                <i class="fas fa-box text-purple-600 text-xl"></i>
            </div>
            <p class="text-3xl font-bold text-purple-600">
                <?php echo $stats['available_products']; ?>
            </p>
            <p class="text-sm text-gray-600 mt-2">
                dari <?php echo $stats['total_products']; ?> total produk
            </p>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold mb-4">Pesanan Terbaru</h3>
            <div class="space-y-4">
                <?php foreach ($recent_orders as $order): ?>
                    <div class="flex items-center justify-between border-b pb-4">
                        <div>
                            <p class="font-semibold"><?php echo htmlspecialchars($order['order_code']); ?></p>
                            <p class="text-sm text-gray-600">
                                <?php echo htmlspecialchars($order['customer_name']); ?>
                                <?php if ($order['reseller_name']): ?>
                                    via <?php echo htmlspecialchars($order['reseller_name']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
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
                <?php endforeach; ?>
            </div>
            <a href="/admin/orders.php" class="inline-block mt-4 text-blue-600 hover:underline">
                Lihat semua pesanan <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <!-- Recent Registrations -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold mb-4">Pendaftaran Reseller Terbaru</h3>
            <div class="space-y-4">
                <?php foreach ($recent_registrations as $registration): ?>
                    <div class="flex items-center justify-between border-b pb-4">
                        <div>
                            <p class="font-semibold"><?php echo htmlspecialchars($registration['full_name']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($registration['email']); ?></p>
                        </div>
                        <div>
                            <span class="inline-block px-2 py-1 text-xs rounded
                                <?php echo $registration['is_active'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo $registration['is_active'] ? 'Aktif' : 'Pending'; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="/admin/resellers.php" class="inline-block mt-4 text-blue-600 hover:underline">
                Lihat semua reseller <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-xl font-semibold mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="/admin/resellers.php?status=pending" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100">
                <i class="fas fa-user-clock text-yellow-600 text-2xl mr-3"></i>
                <div>
                    <p class="font-semibold">Persetujuan Reseller</p>
                    <p class="text-sm text-gray-600"><?php echo $stats['pending_resellers']; ?> pending</p>
                </div>
            </a>
            <a href="/admin/orders.php?status=pending" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100">
                <i class="fas fa-clipboard-list text-blue-600 text-2xl mr-3"></i>
                <div>
                    <p class="font-semibold">Pesanan Pending</p>
                    <p class="text-sm text-gray-600"><?php echo $stats['pending_orders']; ?> pesanan</p>
                </div>
            </a>
            <a href="/admin/commissions.php?status=pending" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100">
                <i class="fas fa-money-bill-wave text-green-600 text-2xl mr-3"></i>
                <div>
                    <p class="font-semibold">Komisi Pending</p>
                    <p class="text-sm text-gray-600">Rp <?php echo number_format($stats['pending_commissions'], 0, ',', '.'); ?></p>
                </div>
            </a>
            <a href="/admin/products.php?stock=low" class="flex items-center p-4 bg-red-50 rounded-lg hover:bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-3"></i>
                <div>
                    <p class="font-semibold">Stok Menipis</p>
                    <p class="text-sm text-gray-600">Cek sekarang</p>
                </div>
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
