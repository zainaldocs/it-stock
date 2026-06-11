<?php
// item_action.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Helpers to convert empty string to NULL for foreign keys
    $cat_barang = !empty($_POST['category_barang_id']) ? $_POST['category_barang_id'] : null;
    $cat_lokasi = !empty($_POST['category_lokasi_id']) ? $_POST['category_lokasi_id'] : null;
    $cat_satuan = !empty($_POST['category_satuan_id']) ? $_POST['category_satuan_id'] : null;

    if ($action === 'create') {
        $item_code = strtoupper(trim($_POST['item_code']));
        $item_name = trim($_POST['item_name']);
        $minimum_stock = (int)$_POST['minimum_stock'];
        $initial_stock = isset($_POST['initial_stock']) ? (int)$_POST['initial_stock'] : 0;

        // Cek unik kode barang
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM items WHERE item_code = ?");
        $stmt_check->execute([$item_code]);
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['error'] = "Kode Barang '$item_code' sudah digunakan.";
            header("Location: item_form.php");
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO items (item_code, item_name, category_barang_id, category_lokasi_id, category_satuan_id, current_stock, minimum_stock)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([$item_code, $item_name, $cat_barang, $cat_lokasi, $cat_satuan, $initial_stock, $minimum_stock])) {
            $item_id = $pdo->lastInsertId();
            log_activity($pdo, 'CREATE_ITEM', "Menambah barang baru: $item_code - $item_name (Stok Awal: $initial_stock)");
            
            // Jika ada stok awal, catat juga di transaksi sebagai IN sistem
            if ($initial_stock > 0) {
                $stmt_tx = $pdo->prepare("INSERT INTO stock_transactions (item_id, transaction_type, quantity, notes, user_id) VALUES (?, 'IN', ?, 'Stok Awal Sistem', ?)");
                $stmt_tx->execute([$item_id, $initial_stock, $_SESSION['user_id']]);
            }

            $_SESSION['success'] = "Barang berhasil ditambahkan.";
            header("Location: items.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menambah barang.";
            header("Location: item_form.php");
            exit;
        }
    } 
    elseif ($action === 'update') {
        $id = $_POST['id'];
        $item_code = strtoupper(trim($_POST['item_code']));
        $item_name = trim($_POST['item_name']);
        $minimum_stock = (int)$_POST['minimum_stock'];

        // Cek unik kode barang tapi exclude diri sendiri
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM items WHERE item_code = ? AND id != ?");
        $stmt_check->execute([$item_code, $id]);
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['error'] = "Kode Barang '$item_code' sudah digunakan oleh barang lain.";
            header("Location: item_form.php?id=$id");
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE items 
            SET item_code = ?, item_name = ?, category_barang_id = ?, category_lokasi_id = ?, category_satuan_id = ?, minimum_stock = ?
            WHERE id = ?
        ");

        if ($stmt->execute([$item_code, $item_name, $cat_barang, $cat_lokasi, $cat_satuan, $minimum_stock, $id])) {
            log_activity($pdo, 'UPDATE_ITEM', "Mengupdate barang ID $id: $item_code - $item_name");
            $_SESSION['success'] = "Barang berhasil diupdate.";
        } else {
            $_SESSION['error'] = "Gagal mengupdate barang.";
        }
        header("Location: items.php");
        exit;
    }
    elseif ($action === 'delete') {
        $id = $_POST['id'];
        
        $stmt_old = $pdo->prepare("SELECT item_code, item_name FROM items WHERE id = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch();

        if ($old_data) {
            $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
            if ($stmt->execute([$id])) {
                log_activity($pdo, 'DELETE_ITEM', "Menghapus barang beserta history transaksi: {$old_data['item_code']} - {$old_data['item_name']}");
                $_SESSION['success'] = "Barang berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus barang.";
            }
        }
        header("Location: items.php");
        exit;
    }
}
header("Location: items.php");
exit;
?>
