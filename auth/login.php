<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email) $errors[] = "Valid email is required.";
    if (!$password) $errors[] = "Password is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, password, role, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            if ($user['role'] === 'admin') {
                header('Location: /admin/dashboard.php');
            } else {
                header('Location: /reseller/dashboard.php');
            }
            exit();
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2 class="text-2xl font-bold mb-4">Login</h2>

<?php if (isset($_GET['registered'])): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        Registration successful. Please login.
    </div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li>- <?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" class="max-w-md bg-white p-6 rounded shadow">
    <label class="block mb-2">Email</label>
    <input type="email" name="email" class="w-full border p-2 mb-4" required />

    <label class="block mb-2">Password</label>
    <input type="password" name="password" class="w-full border p-2 mb-4" required />

    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mt-4">Login</button>
</form>

<?php include '../includes/footer.php'; ?>
