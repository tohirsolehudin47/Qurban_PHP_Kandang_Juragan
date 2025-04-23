<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_admin();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'reseller'");
    $stmt->execute([$id]);
    header('Location: resellers.php');
    exit();
}

// Fetch all resellers
$stmt = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role = 'reseller' ORDER BY created_at DESC");
$resellers = $stmt->fetchAll();

include '../includes/header.php';
?>

<h2 class="text-3xl font-bold mb-6">Manage Resellers</h2>

<table class="min-w-full bg-white rounded shadow">
    <thead>
        <tr>
            <th class="py-2 px-4 border-b">ID</th>
            <th class="py-2 px-4 border-b">Name</th>
            <th class="py-2 px-4 border-b">Email</th>
            <th class="py-2 px-4 border-b">Registered At</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($resellers as $reseller): ?>
            <tr>
                <td class="py-2 px-4 border-b"><?php echo $reseller['id']; ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($reseller['name']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($reseller['email']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo $reseller['created_at']; ?></td>
                <td class="py-2 px-4 border-b">
                    <a href="reseller_edit.php?id=<?php echo $reseller['id']; ?>" class="text-blue-600 hover:underline mr-2">Edit</a>
                    <a href="resellers.php?delete=<?php echo $reseller['id']; ?>" onclick="return confirm('Are you sure?');" class="text-red-600 hover:underline">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
