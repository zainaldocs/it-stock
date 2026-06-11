<?php
// config/functions.php
if (session_status() === PHP_SESSION_NONE) {
    session_name('IT_STOCK_SESSION');
    session_start();
}

/**
 * Cek apakah user sudah login. Jika belum, arahkan ke login.php
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Cek apakah user adalah Admin. Jika bukan, arahkan kembali ke dashboard atau tampilkan pesan error.
 */
function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'Admin') {
        header("Location: dashboard.php");
        exit;
    }
}

/**
 * Fungsi untuk mencatat aktivitas ke tabel activity_logs
 * @param PDO $pdo Koneksi database
 * @param string $action Nama aksi (misal: 'LOGIN', 'CREATE_ITEM')
 * @param string $details Detail aksi
 */
function log_activity($pdo, $action, $details) {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $action, $details]);
    } catch (PDOException $e) {
        // Jika foreign key gagal (user_id tidak ada di database, misal karena session bentrok / dihapus)
        // Maka rekam aktivitas dengan user_id = NULL
        if ($e->getCode() == 23000 || $e->getCode() == '23000') {
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (NULL, ?, ?)");
            $stmt->execute([$action, $details]);
        }
    }
}

/**
 * Sanitasi string input dasar untuk menghindari XSS saat ditampilkan
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Mendapatkan base URL proyek
 */
function base_url($path = '') {
    // Sesuaikan ini jika struktur folder Anda berbeda (misalnya it-stock)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    // Ambil path folder proyek
    $project_folder = '/it-stock';
    return $protocol . "://" . $host . $project_folder . '/' . ltrim($path, '/');
}
?>
