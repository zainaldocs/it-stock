<?php
// category_action.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $name = trim($_POST['name']);
        $type = $_POST['type'];
        
        if (!empty($name) && !empty($type)) {
            $stmt = $pdo->prepare("INSERT INTO categories_type (name, type) VALUES (?, ?)");
            if ($stmt->execute([$name, $type])) {
                log_activity($pdo, 'CREATE_CATEGORY', "Menambah kategori baru: $name ($type)");
                $_SESSION['success'] = "Kategori berhasil ditambahkan.";
            } else {
                $_SESSION['error'] = "Gagal menambah kategori.";
            }
        }
    } 
    elseif ($action === 'update') {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $type = $_POST['type'];
        
        if (!empty($id) && !empty($name) && !empty($type)) {
            // Ambil data lama untuk log
            $stmt_old = $pdo->prepare("SELECT name, type FROM categories_type WHERE id = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();

            $stmt = $pdo->prepare("UPDATE categories_type SET name = ?, type = ? WHERE id = ?");
            if ($stmt->execute([$name, $type, $id])) {
                log_activity($pdo, 'UPDATE_CATEGORY', "Mengubah kategori ID $id. Lama: {$old_data['name']} ({$old_data['type']}) -> Baru: $name ($type)");
                $_SESSION['success'] = "Kategori berhasil diupdate.";
            } else {
                $_SESSION['error'] = "Gagal mengupdate kategori.";
            }
        }
    }
    elseif ($action === 'delete') {
        $id = $_POST['id'];
        if (!empty($id)) {
            // Ambil nama untuk log
            $stmt_old = $pdo->prepare("SELECT name, type FROM categories_type WHERE id = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();

            $stmt = $pdo->prepare("DELETE FROM categories_type WHERE id = ?");
            if ($stmt->execute([$id])) {
                log_activity($pdo, 'DELETE_CATEGORY', "Menghapus kategori: {$old_data['name']} ({$old_data['type']})");
                $_SESSION['success'] = "Kategori berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus kategori.";
            }
        }
    }

    header("Location: categories.php");
    exit;
} else {
    header("Location: categories.php");
    exit;
}
?>
