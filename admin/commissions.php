<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_admin();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'mark_paid':
                $commission_id = (int)$_POST['commission_id'];
                $payment_notes = sanitize($_POST['payment_notes'] ?? '');
                
                // Handle payment proof upload
                $payment_proof = null;
                if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                    $payment_proof = handle_image_upload($_FILES['payment_proof'], 'uploads/payment_proofs');
                    if (!$payment_proof) {
                        throw new Exception("Gagal mengunggah bukti pembayaran");
                    }
                }

                $stmt = $pdo->prepare("
                    UPDATE commissions 
                    SET status = 'paid', 
                        payment_date = NOW(),
                        payment_proof = ?,
                        payment_notes = ?
                    WHERE id = ?
                ");
                $stmt->execute([$payment_proof, $payment_notes, $commission_id]);
                
                $_SESSION['flash_message'] = "Komisi berhasil ditandai sebagai dibayar.";
                $_SESSION['flash_type'] = "success";
                break;

            case 'bulk_pay':
                $commission_ids = $_POST['commission_ids'] ?? [];
                if (!empty($commission_ids)) {
                    $placeholders = str_repeat('?,', count($commission_ids) - 1) . '?';
                    $stmt = $pdo->prepare("
                        UPDATE commissions 
                        SET status = 'paid',
                            payment_date = NOW()
                        WHERE id IN ($placeholders)
                    ");
                    $stmt->execute($commission_ids);
                    
                    $_SESSION['flash_message'] = "Komisi terpilih berhasil ditandai sebagai dibayar.";
                    $_SESSION['flash_type'] = "success";
                }
                break;
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = "Terjadi kesalahan: " . $e->getMessage();
        $_SESSION['flash_type'] = "error";
    }
    
    header('Location: commissions.php');
    exit();
}

// Get filters
$status = $_GET['status'] ?? 'all';
$reseller = $_GET['reseller'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$query = "
    SELECT 
        c.*,
        o.order_code,
        o.total_amount as order_amount,
        u.username as reseller_username,
        rp.full_name as reseller_name,
        rb.bank_name,
        rb.account_number,
        rb.account_holder
    FROM commissions c
    JOIN orders o ON c.order_id = o.id
    JOIN users u ON c.reseller_id = u.id
    JOIN reseller_profiles rp ON u.id = rp.user_id
    LEFT JOIN reseller_banks rb ON u.id = rb.user_id
    WHERE 1=1
";

$params = [];

if ($status !== 'all') {
    $query .= " AND c.status = ?";
    $params[] = $status;
}

if ($reseller) {
    $query .= " AND c.reseller_id = ?";
    $params[] = $reseller;
}

if ($date_from) {
    $query .= " AND DATE(c.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $query .= " AND DATE(c.created_at) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$commissions = $stmt->fetchAll();

// Get resellers for filter
$resellers = $pdo->query("
    SELECT DISTINCT u.id, u.username, rp.full_name
    FROM users u
    JOIN reseller_profiles rp ON u.id = rp.user_id
    WHERE u.role = 'reseller'
    ORDER BY rp.full_name
")->fetchAll();

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Kelola Komisi</h1>
        
        <!-- Bulk Actions -->
        <div>
            <button onclick="showBulkPayModal()" 
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-money-bill-wave mr-2"></i>Bayar Komisi Terpilih
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Dibayar</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Reseller</label>
                <select name="reseller" class="w-full border rounded px-3 py-2">
                    <option value="">Semua Reseller</option>
                    <?php foreach ($resellers as $r): ?>
                        <option value="<?php echo $r['id']; ?>" 
                                <?php echo $reseller == $r['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                       class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                       class="w-full border rounded px-3 py-2">
            </div>
        </form>
    </div>

    <!-- Commissions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-4 px-6 py-3">
                        <input type="checkbox" id="select-all" class="rounded">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Reseller
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Order
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Komisi
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
                <?php foreach ($commissions as $commission): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <?php if ($commission['status'] === 'pending'): ?>
                                <input type="checkbox" 
                                       name="commission_ids[]" 
                                       value="<?php echo $commission['id']; ?>" 
                                       class="commission-checkbox rounded">
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($commission['reseller_name']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($commission['bank_name']); ?> - 
                                <?php echo htmlspecialchars($commission['account_number']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($commission['order_code']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                Rp <?php echo number_format($commission['order_amount'], 0, ',', '.'); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                Rp <?php echo number_format($commission['amount'], 0, ',', '.'); ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                <?php echo date('d/m/Y', strtotime($commission['created_at'])); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                <?php echo $commission['status'] === 'paid' 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo $commission['status'] === 'paid' ? 'Dibayar' : 'Pending'; ?>
                            </span>
                            <?php if ($commission['payment_date']): ?>
                                <div class="text-xs text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($commission['payment_date'])); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <?php if ($commission['status'] === 'pending'): ?>
                                <button onclick="showPaymentModal(<?php echo $commission['id']; ?>)"
                                        class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-money-bill-wave"></i> Bayar
                                </button>
                            <?php else: ?>
                                <?php if ($commission['payment_proof']): ?>
                                    <a href="<?php echo htmlspecialchars($commission['payment_proof']); ?>" 
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-receipt"></i> Bukti
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Pembayaran Komisi</h3>
            <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" id="paymentForm">
            <input type="hidden" name="action" value="mark_paid">
            <input type="hidden" name="commission_id" id="commission_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Bukti Pembayaran
                </label>
                <input type="file" name="payment_proof" accept="image/*" class="w-full">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan Pembayaran
                </label>
                <textarea name="payment_notes" rows="3" 
                          class="w-full border rounded px-3 py-2"></textarea>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" 
                        onclick="closePaymentModal()"
                        class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Konfirmasi Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Payment Modal -->
<div id="bulkPayModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Pembayaran Komisi Massal</h3>
            <button onclick="closeBulkPayModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" id="bulkPayForm">
            <input type="hidden" name="action" value="bulk_pay">
            <div id="selectedCommissions"></div>
            
            <p class="mb-4">
                Anda akan menandai semua komisi terpilih sebagai dibayar. 
                Pastikan pembayaran sudah dilakukan sebelum melanjutkan.
            </p>

            <div class="flex justify-end space-x-2">
                <button type="button" 
                        onclick="closeBulkPayModal()"
                        class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Konfirmasi Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showPaymentModal(commissionId) {
    document.getElementById('commission_id').value = commissionId;
    document.getElementById('paymentModal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

function showBulkPayModal() {
    const checkboxes = document.querySelectorAll('.commission-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Pilih komisi yang akan dibayar terlebih dahulu');
        return;
    }

    const selectedCommissions = document.getElementById('selectedCommissions');
    selectedCommissions.innerHTML = '';
    
    checkboxes.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'commission_ids[]';
        input.value = checkbox.value;
        selectedCommissions.appendChild(input);
    });

    document.getElementById('bulkPayModal').classList.remove('hidden');
}

function closeBulkPayModal() {
    document.getElementById('bulkPayModal').classList.add('hidden');
}

// Select all functionality
document.getElementById('select-all').addEventListener('change', function(e) {
    document.querySelectorAll('.commission-checkbox').forEach(checkbox => {
        checkbox.checked = e.target.checked;
    });
});

// Close modals when clicking outside
document.getElementById('paymentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentModal();
    }
});

document.getElementById('bulkPayModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBulkPayModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
