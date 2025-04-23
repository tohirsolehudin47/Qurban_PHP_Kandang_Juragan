<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_admin();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $action = $_POST['action'] ?? '';
    
    if ($order_id && $action) {
        try {
            switch ($action) {
                case 'update_status':
                    $status = $_POST['status'];
                    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $order_id]);

                    // If order completed, create commission
                    if ($status === 'completed') {
                        $stmt = $pdo->prepare("
                            SELECT o.reseller_id, o.id as order_id, oi.product_id, oi.quantity
                            FROM orders o
                            JOIN order_items oi ON o.id = oi.order_id
                            WHERE o.id = ? AND o.reseller_id IS NOT NULL
                        ");
                        $stmt->execute([$order_id]);
                        $order_data = $stmt->fetch();

                        if ($order_data) {
                            $commission_amount = calculate_commission($pdo, $order_data['product_id'], $order_data['quantity']);
                            
                            $stmt = $pdo->prepare("
                                INSERT INTO commissions (order_id, reseller_id, amount, status, created_at)
                                VALUES (?, ?, ?, 'pending', NOW())
                            ");
                            $stmt->execute([$order_id, $order_data['reseller_id'], $commission_amount]);
                        }
                    }

                    $_SESSION['flash_message'] = "Status pesanan berhasil diperbarui.";
                    $_SESSION['flash_type'] = "success";
                    break;
            }
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Terjadi kesalahan: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
        }
        
        header('Location: orders.php');
        exit();
    }
}

// Get filters
$status = $_GET['status'] ?? 'all';
$reseller = $_GET['reseller'] ?? 'all';
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$query = "
    SELECT 
        o.*,
        c.name as customer_name,
        c.phone as customer_phone,
        u.username as reseller_username,
        rp.full_name as reseller_name,
        GROUP_CONCAT(
            CONCAT(p.name, ' (', oi.quantity, ')')
            SEPARATOR ', '
        ) as items
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    LEFT JOIN users u ON o.reseller_id = u.id
    LEFT JOIN reseller_profiles rp ON u.id = rp.user_id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE 1=1
";

$params = [];

if ($status !== 'all') {
    $query .= " AND o.status = ?";
    $params[] = $status;
}

if ($reseller !== 'all') {
    $query .= " AND o.reseller_id " . ($reseller === 'direct' ? 'IS NULL' : 'IS NOT NULL');
}

if ($search) {
    $query .= " AND (
        o.order_code LIKE ? OR 
        c.name LIKE ? OR 
        c.phone LIKE ? OR
        u.username LIKE ? OR
        rp.full_name LIKE ?
    )";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
}

if ($date_from) {
    $query .= " AND DATE(o.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $query .= " AND DATE(o.created_at) <= ?";
    $params[] = $date_to;
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Kelola Pesanan</h1>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                    <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Dikirim</option>
                    <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sumber</label>
                <select name="reseller" class="w-full border rounded px-3 py-2">
                    <option value="all" <?php echo $reseller === 'all' ? 'selected' : ''; ?>>Semua</option>
                    <option value="reseller" <?php echo $reseller === 'reseller' ? 'selected' : ''; ?>>Via Reseller</option>
                    <option value="direct" <?php echo $reseller === 'direct' ? 'selected' : ''; ?>>Langsung</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <div class="flex">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Cari pesanan..."
                           class="flex-1 border rounded-l px-3 py-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($order['order_code']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </div>
                            <?php if ($order['reseller_username']): ?>
                                <div class="text-xs text-blue-600">
                                    via <?php echo htmlspecialchars($order['reseller_name']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($order['customer_name']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($order['customer_phone']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <?php echo htmlspecialchars($order['items']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                DP: Rp <?php echo number_format($order['deposit_amount'], 0, ',', '.'); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                <?php echo match($order['status']) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'delivered' => 'bg-purple-100 text-purple-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                }; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <div class="flex space-x-2">
                                <button onclick="showOrderDetails(<?php echo $order['id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" 
                                                onchange="this.form.submit()"
                                                class="text-sm border rounded px-2 py-1">
                                            <option value="">Ubah Status</option>
                                            <option value="confirmed">Konfirmasi</option>
                                            <option value="delivered">Kirim</option>
                                            <option value="completed">Selesai</option>
                                            <option value="cancelled">Batal</option>
                                        </select>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Order Details -->
<div id="orderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Detail Pesanan</h3>
            <button onclick="closeOrderModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="orderDetails">
            <!-- Details will be loaded here -->
        </div>
    </div>
</div>

<script>
function showOrderDetails(orderId) {
    // In a real implementation, this would fetch details via AJAX
    document.getElementById('orderModal').classList.remove('hidden');
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('orderModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeOrderModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
