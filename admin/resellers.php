<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_admin();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reseller_id = $_POST['reseller_id'] ?? null;
    $action = $_POST['action'] ?? '';

    if ($reseller_id && $action) {
        try {
            switch ($action) {
                case 'approve':
                    $stmt = $pdo->prepare("
                        UPDATE users SET is_active = 1 WHERE id = ? AND role = 'reseller'
                    ");
                    $stmt->execute([$reseller_id]);
                    $_SESSION['flash_message'] = "Reseller berhasil diaktifkan.";
                    $_SESSION['flash_type'] = "success";
                    break;

                case 'reject':
                    $stmt = $pdo->prepare("
                        UPDATE users SET is_active = 0 WHERE id = ? AND role = 'reseller'
                    ");
                    $stmt->execute([$reseller_id]);
                    $_SESSION['flash_message'] = "Reseller berhasil dinonaktifkan.";
                    $_SESSION['flash_type'] = "success";
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'reseller'");
                    $stmt->execute([$reseller_id]);
                    $_SESSION['flash_message'] = "Reseller berhasil dihapus.";
                    $_SESSION['flash_type'] = "success";
                    break;
            }
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Terjadi kesalahan: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
        }
        
        header('Location: resellers.php');
        exit();
    }
}

// Get filters
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'desc';

// Build query
$query = "
    SELECT 
        u.*,
        rp.full_name,
        rp.city,
        rp.province,
        rp.sales_target,
        rb.bank_name,
        rb.account_number,
        (SELECT COUNT(*) FROM orders WHERE reseller_id = u.id) as total_orders,
        (SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE reseller_id = u.id) as total_commission
    FROM users u
    LEFT JOIN reseller_profiles rp ON u.id = rp.user_id
    LEFT JOIN reseller_banks rb ON u.id = rb.user_id
    WHERE u.role = 'reseller'
";

if ($status !== 'all') {
    $query .= " AND u.is_active = " . ($status === 'active' ? '1' : '0');
}

if ($search) {
    $query .= " AND (
        u.username LIKE :search 
        OR u.email LIKE :search 
        OR rp.full_name LIKE :search 
        OR rp.city LIKE :search
    )";
}

$query .= " ORDER BY u.$sort $order";

$stmt = $pdo->prepare($query);

if ($search) {
    $searchTerm = "%$search%";
    $stmt->bindParam(':search', $searchTerm);
}

$stmt->execute();
$resellers = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Kelola Reseller</h1>
        
        <!-- Filters -->
        <div class="flex space-x-4">
            <form class="flex space-x-4">
                <input type="text" 
                       name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Cari reseller..."
                       class="border rounded px-3 py-2">
                
                <select name="status" class="border rounded px-3 py-2">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                </select>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </form>
        </div>
    </div>

    <!-- Resellers Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Reseller
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Kontak
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Lokasi
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Performa
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($resellers as $reseller): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <?php if ($reseller['profile_image']): ?>
                                        <img class="h-10 w-10 rounded-full" 
                                             src="<?php echo htmlspecialchars($reseller['profile_image']); ?>" 
                                             alt="">
                                    <?php else: ?>
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-500"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($reseller['full_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @<?php echo htmlspecialchars($reseller['username']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <?php echo htmlspecialchars($reseller['email']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($reseller['phone']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <?php echo htmlspecialchars($reseller['city']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($reseller['province']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <?php echo $reseller['total_orders']; ?> pesanan
                            </div>
                            <div class="text-sm text-gray-500">
                                Rp <?php echo number_format($reseller['total_commission'], 0, ',', '.'); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $reseller['is_active'] 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo $reseller['is_active'] ? 'Aktif' : 'Pending'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <div class="flex space-x-2">
                                <button onclick="showResellerDetails(<?php echo $reseller['id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <?php if (!$reseller['is_active']): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="reseller_id" value="<?php echo $reseller['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="reseller_id" value="<?php echo $reseller['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus reseller ini?');">
                                    <input type="hidden" name="reseller_id" value="<?php echo $reseller['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Reseller Details -->
<div id="resellerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Detail Reseller</h3>
            <button onclick="closeResellerModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="resellerDetails">
            <!-- Details will be loaded here -->
        </div>
    </div>
</div>

<script>
function showResellerDetails(resellerId) {
    // In a real implementation, this would fetch details via AJAX
    document.getElementById('resellerModal').classList.remove('hidden');
}

function closeResellerModal() {
    document.getElementById('resellerModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('resellerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeResellerModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
