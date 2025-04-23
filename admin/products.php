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
            case 'toggle_availability':
                $product_id = (int)$_POST['product_id'];
                $is_available = (int)$_POST['is_available'];
                
                $stmt = $pdo->prepare("UPDATE products SET is_available = ? WHERE id = ?");
                $stmt->execute([$is_available, $product_id]);
                
                $_SESSION['flash_message'] = "Status produk berhasil diperbarui.";
                $_SESSION['flash_type'] = "success";
                break;

            case 'delete':
                $product_id = (int)$_POST['product_id'];
                
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                
                $_SESSION['flash_message'] = "Produk berhasil dihapus.";
                $_SESSION['flash_type'] = "success";
                break;
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = "Terjadi kesalahan: " . $e->getMessage();
        $_SESSION['flash_type'] = "error";
    }
    
    header('Location: products.php');
    exit();
}

// Get filters
$category_id = $_GET['category'] ?? null;
$search = $_GET['search'] ?? '';
$stock = $_GET['stock'] ?? 'all';
$availability = $_GET['availability'] ?? 'all';

// Fetch categories
$categories = $pdo->query("SELECT * FROM product_categories ORDER BY name")->fetchAll();

// Build query
$query = "
    SELECT p.*, pc.name as category_name
    FROM products p
    JOIN product_categories pc ON p.category_id = pc.id
    WHERE 1=1
";

$params = [];

if ($category_id) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
}

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.code LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($stock === 'low') {
    $query .= " AND p.stock <= 3";
} elseif ($stock === 'out') {
    $query .= " AND p.stock = 0";
}

if ($availability !== 'all') {
    $query .= " AND p.is_available = ?";
    $params[] = ($availability === 'available' ? 1 : 0);
}

$query .= " ORDER BY p.category_id, p.name";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Kelola Produk</h1>
        
        <a href="product_add.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>Tambah Produk
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                <select name="category" class="w-full border rounded px-3 py-2">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Stok</label>
                <select name="stock" class="w-full border rounded px-3 py-2">
                    <option value="all" <?php echo $stock === 'all' ? 'selected' : ''; ?>>Semua</option>
                    <option value="low" <?php echo $stock === 'low' ? 'selected' : ''; ?>>Stok Menipis</option>
                    <option value="out" <?php echo $stock === 'out' ? 'selected' : ''; ?>>Habis</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="availability" class="w-full border rounded px-3 py-2">
                    <option value="all" <?php echo $availability === 'all' ? 'selected' : ''; ?>>Semua</option>
                    <option value="available" <?php echo $availability === 'available' ? 'selected' : ''; ?>>Tersedia</option>
                    <option value="unavailable" <?php echo $availability === 'unavailable' ? 'selected' : ''; ?>>Tidak Tersedia</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <div class="flex">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Cari produk..."
                           class="flex-1 border rounded-l px-3 py-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="relative h-48">
                    <?php if ($product['image1']): ?>
                        <img src="<?php echo htmlspecialchars($product['image1']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="absolute top-2 right-2 space-x-1">
                        <?php if ($product['image2']): ?>
                            <span class="bg-black bg-opacity-50 text-white p-1 rounded">
                                <i class="fas fa-images"></i>
                            </span>
                        <?php endif; ?>
                        <?php if ($product['video_url']): ?>
                            <span class="bg-black bg-opacity-50 text-white p-1 rounded">
                                <i class="fas fa-video"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="text-lg font-semibold">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h3>
                            <p class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($product['code']); ?>
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full 
                            <?php echo $product['is_available'] 
                                ? 'bg-green-100 text-green-800' 
                                : 'bg-red-100 text-red-800'; ?>">
                            <?php echo $product['is_available'] ? 'Tersedia' : 'Tidak Tersedia'; ?>
                        </span>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-tag w-6"></i>
                            <span><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-weight-hanging w-6"></i>
                            <span><?php echo number_format($product['weight'], 1); ?> kg</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-money-bill w-6"></i>
                            <span>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-box w-6"></i>
                            <span>Stok: <?php echo $product['stock']; ?></span>
                        </div>
                        <?php if ($product['location']): ?>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-map-marker-alt w-6"></i>
                                <span><?php echo htmlspecialchars($product['location']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="space-x-2">
                            <a href="product_edit.php?id=<?php echo $product['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <form method="POST" class="inline" 
                                  onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>

                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="toggle_availability">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="is_available" 
                                   value="<?php echo $product['is_available'] ? '0' : '1'; ?>">
                            <button type="submit" 
                                    class="<?php echo $product['is_available'] 
                                        ? 'bg-red-100 text-red-800 hover:bg-red-200' 
                                        : 'bg-green-100 text-green-800 hover:bg-green-200'; ?> 
                                    px-3 py-1 rounded-full text-sm">
                                <?php echo $product['is_available'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
