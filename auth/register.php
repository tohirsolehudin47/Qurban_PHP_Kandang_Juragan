<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic user information
    $username = sanitize($_POST['username'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');

    // Reseller profile information
    $full_name = sanitize($_POST['full_name'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $province = sanitize($_POST['province'] ?? '');
    $postal_code = sanitize($_POST['postal_code'] ?? '');
    $id_card_number = sanitize($_POST['id_card_number'] ?? '');
    $sales_target = sanitize($_POST['sales_target'] ?? 'medium');

    // Bank information
    $bank_name = sanitize($_POST['bank_name'] ?? '');
    $account_number = sanitize($_POST['account_number'] ?? '');
    $account_holder = sanitize($_POST['account_holder'] ?? '');

    // Validation
    if (!$username) $errors[] = "Username wajib diisi";
    if (!$email) $errors[] = "Email wajib diisi";
    if (!$password) $errors[] = "Password wajib diisi";
    if (strlen($password) < 6) $errors[] = "Password minimal 6 karakter";
    if ($password !== $confirm_password) $errors[] = "Konfirmasi password tidak sesuai";
    if (!$phone) $errors[] = "Nomor telepon wajib diisi";
    if (!$full_name) $errors[] = "Nama lengkap wajib diisi";
    if (!$bank_name) $errors[] = "Nama bank wajib diisi";
    if (!$account_number) $errors[] = "Nomor rekening wajib diisi";
    if (!$account_holder) $errors[] = "Nama pemilik rekening wajib diisi";

    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username atau email sudah terdaftar";
        }
    }

    // Handle file uploads
    $profile_image = null;
    $id_card_image = null;

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $profile_image = handle_image_upload($_FILES['profile_image'], 'uploads/profiles');
        if (!$profile_image) {
            $errors[] = "Gagal mengunggah foto profil";
        }
    }

    if (isset($_FILES['id_card_image']) && $_FILES['id_card_image']['error'] === UPLOAD_ERR_OK) {
        $id_card_image = handle_image_upload($_FILES['id_card_image'], 'uploads/id_cards');
        if (!$id_card_image) {
            $errors[] = "Gagal mengunggah foto KTP";
        }
    }

    // Process registration if no errors
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, phone, role, profile_image, is_active, created_at)
                VALUES (?, ?, ?, ?, 'reseller', ?, 0, NOW())
            ");
            $stmt->execute([
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $phone,
                $profile_image
            ]);
            $user_id = $pdo->lastInsertId();

            // Insert reseller profile
            $stmt = $pdo->prepare("
                INSERT INTO reseller_profiles (
                    user_id, full_name, address, city, province, postal_code,
                    id_card_number, id_card_image, sales_target, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $user_id, $full_name, $address, $city, $province, $postal_code,
                $id_card_number, $id_card_image, $sales_target
            ]);

            // Insert bank information
            $stmt = $pdo->prepare("
                INSERT INTO reseller_banks (user_id, bank_name, account_number, account_holder, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$user_id, $bank_name, $account_number, $account_holder]);

            $pdo->commit();
            
            $_SESSION['flash_message'] = "Pendaftaran berhasil! Silakan tunggu persetujuan admin.";
            $_SESSION['flash_type'] = "success";
            
            header('Location: login.php');
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
}

include '../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Daftar Sebagai Reseller</h2>

    <?php if ($errors): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6">
        <!-- Basic Information -->
        <div class="mb-6">
            <h3 class="text-xl font-semibold mb-4">Informasi Dasar</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">No. Telepon/WhatsApp</label>
                    <input type="tel" name="phone" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Foto Profil</label>
                    <input type="file" name="profile_image" accept="image/*" class="w-full">
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="mb-6">
            <h3 class="text-xl font-semibold mb-4">Informasi Profil</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="full_name" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Alamat</label>
                    <textarea name="address" class="w-full border rounded px-3 py-2" rows="3"></textarea>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Kota</label>
                    <input type="text" name="city" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Provinsi</label>
                    <input type="text" name="province" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Kode Pos</label>
                    <input type="text" name="postal_code" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Target Penjualan</label>
                    <select name="sales_target" class="w-full border rounded px-3 py-2">
                        <option value="low">Rendah (1-5 ekor/bulan)</option>
                        <option value="medium" selected>Menengah (6-15 ekor/bulan)</option>
                        <option value="high">Tinggi (>15 ekor/bulan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Nomor KTP</label>
                    <input type="text" name="id_card_number" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Foto KTP</label>
                    <input type="file" name="id_card_image" accept="image/*" class="w-full">
                </div>
            </div>
        </div>

        <!-- Bank Information -->
        <div class="mb-6">
            <h3 class="text-xl font-semibold mb-4">Informasi Bank</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 mb-2">Nama Bank</label>
                    <input type="text" name="bank_name" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Nomor Rekening</label>
                    <input type="text" name="account_number" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Nama Pemilik Rekening</label>
                    <input type="text" name="account_holder" class="w-full border rounded px-3 py-2" required>
                </div>
            </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" required class="mr-2">
                <span class="text-gray-700">
                    Saya menyetujui <a href="/terms.php" class="text-blue-600 hover:underline" target="_blank">Syarat dan Ketentuan</a>
                </span>
            </label>
        </div>

        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
            Daftar Sekarang
        </button>
    </form>

    <p class="mt-4 text-center text-gray-600">
        Sudah punya akun? 
        <a href="login.php" class="text-blue-600 hover:underline">Masuk di sini</a>
    </p>
</div>

<?php include '../includes/footer.php'; ?>
