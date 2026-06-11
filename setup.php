<?php
// setup.php
$host = '127.0.0.1';
$username = 'root'; // default laragon username
$password = ''; // default laragon password

try {
    // Koneksi awal tanpa nama database
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Buat Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS it_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database 'it_inventory' berhasil dibuat atau sudah ada.<br>";

    // Gunakan database tersebut
    $pdo->exec("USE it_inventory");

    // 2. Buat Tabel users
    $sql_users = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('Admin', 'Staff') NOT NULL DEFAULT 'Staff',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_users);
    echo "Tabel 'users' siap.<br>";

    // 3. Buat Tabel categories_type
    $sql_categories = "
    CREATE TABLE IF NOT EXISTS categories_type (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        type ENUM('Barang', 'Lokasi', 'Satuan') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_categories);
    echo "Tabel 'categories_type' siap.<br>";

    // 4. Buat Tabel items
    $sql_items = "
    CREATE TABLE IF NOT EXISTS items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_code VARCHAR(50) NOT NULL UNIQUE,
        item_name VARCHAR(150) NOT NULL,
        category_barang_id INT NULL,
        category_lokasi_id INT NULL,
        category_satuan_id INT NULL,
        current_stock INT NOT NULL DEFAULT 0,
        minimum_stock INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_barang_id) REFERENCES categories_type(id) ON DELETE SET NULL,
        FOREIGN KEY (category_lokasi_id) REFERENCES categories_type(id) ON DELETE SET NULL,
        FOREIGN KEY (category_satuan_id) REFERENCES categories_type(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql_items);
    echo "Tabel 'items' siap.<br>";

    // 5. Buat Tabel stock_transactions
    $sql_transactions = "
    CREATE TABLE IF NOT EXISTS stock_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        transaction_type ENUM('IN', 'OUT') NOT NULL,
        quantity INT NOT NULL,
        notes TEXT NOT NULL,
        transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user_id INT NULL,
        FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql_transactions);
    echo "Tabel 'stock_transactions' siap.<br>";

    // 6. Buat Tabel activity_logs
    $sql_logs = "
    CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT NULL,
        log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql_logs);
    echo "Tabel 'activity_logs' siap.<br>";

    // 7. Insert Default Admin jika belum ada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (username, password, role) VALUES ('admin', '$hashed_password', 'Admin')");
        echo "Default Admin user dibuat. (Username: admin, Password: admin123)<br>";
    }

    // Insert Default Staff jika belum ada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'staff'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hashed_password = password_hash('staff123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (username, password, role) VALUES ('staff', '$hashed_password', 'Staff')");
        echo "Default Staff user dibuat. (Username: staff, Password: staff123)<br>";
    }

    echo "<br><strong>Setup Selesai!</strong> Silakan hapus atau amankan file setup.php ini.<br>";
    echo "<a href='login.php'>Lanjut ke Halaman Login</a>";

} catch (PDOException $e) {
    die("Setup Error: " . $e->getMessage());
}
?>
