<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_reseller();

$stmt = $pdo->query("SELECT * FROM marketing_kit ORDER BY uploaded_at DESC");
$kits = $stmt->fetchAll();

include '../includes/header.php';
?>

<h2 class="text-3xl font-bold mb-6">Marketing Kit</h2>

<?php if (empty($kits)): ?>
    <p>No marketing materials available.</p>
<?php else: ?>
    <ul class="list-disc pl-6">
        <?php foreach ($kits as $kit): ?>
            <li class="mb-2">
                <a href="<?php echo htmlspecialchars($kit['file_url']); ?>" target="_blank" class="text-blue-600 hover:underline">
                    <?php echo htmlspecialchars($kit['file_name']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
