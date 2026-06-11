<?php
// login.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Catat log aktivitas
            log_activity($pdo, 'LOGIN', "User {$user['username']} logged in.");

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IT Inventory Stock</title>
    <link rel="stylesheet" href="assets/css/output.css">
    <link rel="stylesheet" href="assets/fonts/inter/index.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md border-t-4 border-emerald-800">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-800 mb-4">
                <i class="fas fa-boxes text-3xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">IT Stock PT Guardian Pharmatama</h2>
            <p class="text-sm text-gray-500 mt-2">Login ke Sistem Inventaris</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 p-3 rounded mb-4 text-sm flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo h($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" id="username" name="username" class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required autofocus>
                </div>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" id="password" name="password" class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-800 text-white font-semibold py-2 px-4 rounded hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-sign-in-alt mr-2"></i> Masuk
            </button>
        </form>
        <div class="mt-6 text-center text-xs text-gray-400">
            &copy; <?php echo date('Y'); ?> Corporate Emerald Edition
        </div>
    </div>

</body>
</html>
