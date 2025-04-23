<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$errors = [];

// Redirect if already logged in
if (is_logged_in()) {
    $redirect = $_SESSION['role'] === 'admin' ? '/admin/dashboard.php' : '/reseller/dashboard.php';
    header('Location: ' . $redirect);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username) {
        $errors[] = "Username wajib diisi";
    }
    if (!$password) {
        $errors[] = "Password wajib diisi";
    }

    if (empty($errors)) {
        try {
            // Check credentials and account status
            $stmt = $pdo->prepare("
                SELECT id, username, password, role, is_active 
                FROM users 
                WHERE (username = ? OR email = ?)
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['is_active'] || $user['role'] === 'admin') {
                    // Start session and store user data
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Log login attempt
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                    
                    // Redirect based on role
                    $redirect = $user['role'] === 'admin' ? '/admin/dashboard.php' : '/reseller/dashboard.php';
                    header('Location: ' . $redirect);
                    exit();
                } else {
                    $errors[] = "Akun Anda belum diaktifkan. Silakan tunggu persetujuan admin.";
                }
            } else {
                $errors[] = "Username/Email atau password salah";
            }
        } catch (Exception $e) {
            $errors[] = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
}

include '../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Masuk ke Akun Anda
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Atau
                <a href="register.php" class="font-medium text-green-600 hover:text-green-500">
                    daftar sebagai reseller
                </a>
            </p>
        </div>

        <?php if (isset($_GET['registered'])): ?>
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            Pendaftaran berhasil! Silakan login setelah akun Anda diaktifkan oleh admin.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Terjadi kesalahan:
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Username atau Email</label>
                    <input id="username" name="username" type="text" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm" 
                           placeholder="Username atau Email">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm" 
                           placeholder="Password">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember_me" type="checkbox" 
                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                        Ingat saya
                    </label>
                </div>

                <div class="text-sm">
                    <a href="forgot_password.php" class="font-medium text-green-600 hover:text-green-500">
                        Lupa password?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt"></i>
                    </span>
                    Masuk
                </button>
            </div>
        </form>

        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-gray-50 text-gray-500">
                        Butuh bantuan?
                    </span>
                </div>
            </div>

            <div class="mt-6 text-center">
                <a href="https://wa.me/6281234567890" target="_blank" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200">
                    <i class="fab fa-whatsapp mr-2"></i>
                    Hubungi Admin
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
