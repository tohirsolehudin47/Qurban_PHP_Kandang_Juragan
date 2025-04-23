<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $profession = sanitize($_POST['profession'] ?? '');
    $sales_plan = sanitize($_POST['sales_plan'] ?? '');
    $has_farm = sanitize($_POST['has_farm'] ?? '');
    $interested_partner = sanitize($_POST['interested_partner'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $bank_account = sanitize($_POST['bank_account'] ?? '');
    $bank_name = sanitize($_POST['bank_name'] ?? '');

    if (!$name) $errors[] = "Name is required.";
    if (!$email) $errors[] = "Valid email is required.";
    if (!$password || strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if (!$phone) $errors[] = "Phone number is required.";

    if (empty($errors)) {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'reseller', NOW())");
            $stmt->execute([$name, $email, $hashed_password]);
            header('Location: login.php?registered=1');
            exit();
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2 class="text-2xl font-bold mb-4">Register as Reseller</h2>

<?php if ($errors): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li>- <?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" class="max-w-lg bg-white p-6 rounded shadow">
    <label class="block mb-2">Name</label>
    <input type="text" name="name" class="w-full border p-2 mb-4" required />

    <label class="block mb-2">Email</label>
    <input type="email" name="email" class="w-full border p-2 mb-4" required />

    <label class="block mb-2">Password</label>
    <input type="password" name="password" class="w-full border p-2 mb-4" required />

    <label class="block mb-2">Phone / WhatsApp</label>
    <input type="text" name="phone" class="w-full border p-2 mb-4" required />

    <label class="block mb-2">Profession</label>
    <input type="text" name="profession" class="w-full border p-2 mb-4" />

    <label class="block mb-2">Sales Plan</label>
    <select name="sales_plan" class="w-full border p-2 mb-4">
        <option value="">Select</option>
        <option value="Offline">Offline</option>
        <option value="Online">Online</option>
        <option value="Offline & Online">Offline & Online</option>
    </select>

    <label class="block mb-2">Do you have a cattle farm?</label>
    <select name="has_farm" class="w-full border p-2 mb-4">
        <option value="">Select</option>
        <option value="Yes">Yes</option>
        <option value="No">No</option>
    </select>

    <label class="block mb-2">If yes, are you interested in becoming a business partner?</label>
    <select name="interested_partner" class="w-full border p-2 mb-4">
        <option value="">Select</option>
        <option value="Interested">Interested</option>
        <option value="Not Interested">Not Interested</option>
    </select>

    <label class="block mb-2">Address</label>
    <textarea name="address" class="w-full border p-2 mb-4"></textarea>

    <h3 class="text-lg font-semibold mb-2">Bank Data (for commission payout)</h3>

    <label class="block mb-2">Bank Account Number</label>
    <input type="text" name="bank_account" class="w-full border p-2 mb-4" />

    <label class="block mb-2">Bank Name</label>
    <input type="text" name="bank_name" class="w-full border p-2 mb-4" />

    <label class="block mb-2">
        <input type="checkbox" required />
        I agree to the <a href="https://app.nusaqu.id/syarat-dan-ketentuan" target="_blank" class="text-blue-600 underline">Terms and Conditions</a>
    </label>

    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mt-4">Register</button>
</form>

<?php include '../includes/footer.php'; ?>
