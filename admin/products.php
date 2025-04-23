<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_admin();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: products.php');
    exit();
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();

include '../includes/header.php';
?>

<h2 class="text-3xl font-bold mb-6">Manage Products</h2>

<a href="product_add.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mb-4 inline-block">Add New Product</a>

<table class="min-w-full bg-white rounded shadow">
    <thead>
        <tr>
            <th class="py-2 px-4 border-b">ID</th>
            <th class="py-2 px-4 border-b">Name</th>
            <th class="py-2 px-4 border-b">Category</th>
            <th class="py-2 px-4 border-b">Price</th>
            <th class="py-2 px-4 border-b">Created At</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td class="py-2 px-4 border-b"><?php echo $product['id']; ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($product['name']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($product['category']); ?></td>
                <td class="py-2 px-4 border-b">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                <td class="py-2 px-4 border-b"><?php echo $product['created_at']; ?></td>
                <td class="py-2 px-4 border-b">
                    <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:underline mr-2">Edit</a>
                    <a href="products.php?delete=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure?');" class="text-red-600 hover:underline">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
