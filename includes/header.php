<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qurban App - Platform Qurban dan Tabungan Terpercaya</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="/" class="flex items-center">
                    <img src="/assets/img/logo.png" alt="Qurban App Logo" class="h-8 w-auto">
                    <span class="ml-2 text-xl font-bold text-green-700">Qurban App</span>
                </a>

                <!-- Navigation Menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="/admin/dashboard.php" class="text-gray-600 hover:text-green-700">Dashboard</a>
                            <a href="/admin/resellers.php" class="text-gray-600 hover:text-green-700">Reseller</a>
                            <a href="/admin/products.php" class="text-gray-600 hover:text-green-700">Produk</a>
                            <a href="/admin/orders.php" class="text-gray-600 hover:text-green-700">Pesanan</a>
                            <a href="/admin/commissions.php" class="text-gray-600 hover:text-green-700">Komisi</a>
                        <?php elseif ($_SESSION['role'] === 'reseller'): ?>
                            <a href="/reseller/dashboard.php" class="text-gray-600 hover:text-green-700">Dashboard</a>
                            <a href="/catalog.php" class="text-gray-600 hover:text-green-700">Katalog</a>
                            <a href="/savings.php" class="text-gray-600 hover:text-green-700">Tabungan</a>
                            <a href="/reseller/marketing.php" class="text-gray-600 hover:text-green-700">Marketing Kit</a>
                            <a href="/reseller/stats.php" class="text-gray-600 hover:text-green-700">Statistik</a>
                        <?php endif; ?>
                        
                        <!-- User Menu Dropdown -->
                        <div class="relative ml-3">
                            <button type="button" class="flex items-center text-gray-600 hover:text-green-700" id="user-menu-button">
                                <span class="mr-2"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                <i class="fas fa-user-circle text-xl"></i>
                            </button>
                            <!-- Dropdown menu -->
                            <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1" id="user-menu">
                                <a href="/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-cog mr-2"></i>Profil
                                </a>
                                <a href="/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/auth/login.php" class="text-green-600 hover:text-green-700">
                            <i class="fas fa-sign-in-alt mr-1"></i>Masuk
                        </a>
                        <a href="/auth/register.php" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            <i class="fas fa-user-plus mr-1"></i>Daftar
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-600 hover:text-green-700" id="mobile-menu-button">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div class="hidden md:hidden" id="mobile-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="/admin/dashboard.php" class="block py-2 text-gray-600 hover:text-green-700">Dashboard</a>
                        <a href="/admin/resellers.php" class="block py-2 text-gray-600 hover:text-green-700">Reseller</a>
                        <a href="/admin/products.php" class="block py-2 text-gray-600 hover:text-green-700">Produk</a>
                        <a href="/admin/orders.php" class="block py-2 text-gray-600 hover:text-green-700">Pesanan</a>
                        <a href="/admin/commissions.php" class="block py-2 text-gray-600 hover:text-green-700">Komisi</a>
                    <?php elseif ($_SESSION['role'] === 'reseller'): ?>
                        <a href="/reseller/dashboard.php" class="block py-2 text-gray-600 hover:text-green-700">Dashboard</a>
                        <a href="/catalog.php" class="block py-2 text-gray-600 hover:text-green-700">Katalog</a>
                        <a href="/savings.php" class="block py-2 text-gray-600 hover:text-green-700">Tabungan</a>
                        <a href="/reseller/marketing.php" class="block py-2 text-gray-600 hover:text-green-700">Marketing Kit</a>
                        <a href="/reseller/stats.php" class="block py-2 text-gray-600 hover:text-green-700">Statistik</a>
                    <?php endif; ?>
                    <a href="/profile.php" class="block py-2 text-gray-600 hover:text-green-700">Profil</a>
                    <a href="/auth/logout.php" class="block py-2 text-red-600 hover:text-red-700">Keluar</a>
                <?php else: ?>
                    <a href="/auth/login.php" class="block py-2 text-gray-600 hover:text-green-700">Masuk</a>
                    <a href="/auth/register.php" class="block py-2 text-gray-600 hover:text-green-700">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container mx-auto px-4 py-8 flex-grow">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $_SESSION['flash_type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php 
                echo $_SESSION['flash_message'];
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_type']);
                ?>
            </div>
        <?php endif; ?>

<script>
// Toggle user menu dropdown
document.getElementById('user-menu-button')?.addEventListener('click', function() {
    document.getElementById('user-menu').classList.toggle('hidden');
});

// Toggle mobile menu
document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
    document.getElementById('mobile-menu').classList.toggle('hidden');
});

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('#user-menu-button')) {
        document.getElementById('user-menu')?.classList.add('hidden');
    }
    if (!event.target.closest('#mobile-menu-button') && !event.target.closest('#mobile-menu')) {
        document.getElementById('mobile-menu')?.classList.add('hidden');
    }
});
</script>
