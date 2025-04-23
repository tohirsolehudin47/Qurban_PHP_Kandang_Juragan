<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Qurban Reseller Affiliate</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <a href="/index.php" class="text-xl font-bold text-green-700">Qurban Reseller</a>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/auth/logout.php" class="text-red-600 hover:underline">Logout</a>
            <?php else: ?>
                <a href="/auth/login.php" class="text-green-600 hover:underline mr-4">Login</a>
                <a href="/auth/register.php" class="text-green-600 hover:underline">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="flex-grow container mx-auto p-4">
