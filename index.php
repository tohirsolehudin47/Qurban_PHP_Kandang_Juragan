<?php
session_start();
include 'includes/header.php';
?>

<div class="text-center mt-20">
    <h1 class="text-4xl font-bold mb-4">Welcome to Qurban Reseller Affiliate Platform</h1>
    <?php if (isset($_SESSION['user_id'])): ?>
        <p class="mb-4">Hello, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        <a href="/auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Logout</a>
    <?php else: ?>
        <a href="/auth/login.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mr-4">Login</a>
        <a href="/auth/register.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Register</a>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
