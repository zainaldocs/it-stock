<?php
// user_action.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $role = $_POST['role'];

        if (!empty($username) && !empty($password) && !empty($role)) {
            // Cek username eksis
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "Username sudah digunakan.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $hashed, $role])) {
                    log_activity($pdo, 'CREATE_USER', "Membuat user baru: $username ($role)");
                    $_SESSION['success'] = "User berhasil ditambahkan.";
                } else {
                    $_SESSION['error'] = "Gagal menambah user.";
                }
            }
        }
    } 
    elseif ($action === 'update') {
        $id = $_POST['id'];
        $password = trim($_POST['password']);
        $role = $_POST['role'];

        if (!empty($id) && !empty($role)) {
            // Ambil username lama
            $stmt_old = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
            $stmt_old->execute([$id]);
            $user = $stmt_old->fetch();

            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, role = ? WHERE id = ?");
                $exec = $stmt->execute([$hashed, $role, $id]);
                $log_msg = "Mengupdate role ({$user['role']} -> $role) & Reset Password user: {$user['username']}";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $exec = $stmt->execute([$role, $id]);
                $log_msg = "Mengupdate role ({$user['role']} -> $role) user: {$user['username']}";
            }

            if ($exec) {
                log_activity($pdo, 'UPDATE_USER', $log_msg);
                $_SESSION['success'] = "User berhasil diupdate.";
            } else {
                $_SESSION['error'] = "Gagal mengupdate user.";
            }
        }
    }
    elseif ($action === 'delete') {
        $id = $_POST['id'];
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "Tidak dapat menghapus akun Anda sendiri yang sedang aktif.";
        } else {
            $stmt_old = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt_old->execute([$id]);
            $user = $stmt_old->fetch();

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$id])) {
                log_activity($pdo, 'DELETE_USER', "Menghapus user: {$user['username']}");
                $_SESSION['success'] = "User berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus user.";
            }
        }
    }
}
header("Location: users.php");
exit;
?>
