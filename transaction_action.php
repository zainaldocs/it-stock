<?php
// transaction_action.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

// Semua user (Admin & Staff) bisa create transaksi
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];

    if ($action === 'create') {
        $item_id = $_POST['item_id'];
        $transaction_type = $_POST['transaction_type'];
        $quantity = (int)$_POST['quantity'];
        $notes = trim($_POST['notes']);

        if (empty($item_id) || $quantity <= 0 || empty($notes)) {
            $_SESSION['error'] = "Data tidak lengkap atau tidak valid.";
            header("Location: transaction_form.php?type=$transaction_type");
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Ambil current_stock dengan FOR UPDATE (Lock)
            $stmt = $pdo->prepare("SELECT item_code, item_name, current_stock FROM items WHERE id = ? FOR UPDATE");
            $stmt->execute([$item_id]);
            $item = $stmt->fetch();

            if (!$item) {
                throw new Exception("Barang tidak ditemukan.");
            }

            $current_stock = (int)$item['current_stock'];
            $new_stock = $current_stock;

            if ($transaction_type === 'IN') {
                $new_stock = $current_stock + $quantity;
            } elseif ($transaction_type === 'OUT') {
                if ($quantity > $current_stock) {
                    throw new Exception("Quantity OUT ($quantity) melebihi stok yang tersedia ($current_stock). Transaksi Ditolak.");
                }
                $new_stock = $current_stock - $quantity;
            } else {
                throw new Exception("Tipe transaksi tidak valid.");
            }

            // Update stok di items
            $stmt_update = $pdo->prepare("UPDATE items SET current_stock = ? WHERE id = ?");
            $stmt_update->execute([$new_stock, $item_id]);

            // Insert log transaksi
            $stmt_insert = $pdo->prepare("
                INSERT INTO stock_transactions (item_id, transaction_type, quantity, notes, user_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt_insert->execute([$item_id, $transaction_type, $quantity, $notes, $user_id]);

            // Log aktivitas umum
            log_activity($pdo, "TRANSACTION_$transaction_type", "Melakukan transaksi $transaction_type sebanyak $quantity untuk barang: {$item['item_code']}");

            $pdo->commit();
            $_SESSION['success'] = "Transaksi $transaction_type berhasil diproses.";
            header("Location: transactions.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
            header("Location: transaction_form.php?type=$transaction_type");
            exit;
        }
    } 
    elseif ($action === 'koreksi') {
        // Hanya Admin yang bisa koreksi
        if ($_SESSION['role'] !== 'Admin') {
            $_SESSION['error'] = "Hanya Admin yang dapat mengkoreksi transaksi.";
            header("Location: transactions.php");
            exit;
        }

        $tx_id = $_POST['tx_id'];
        $item_id = $_POST['item_id'];
        $tx_type = $_POST['tx_type'];
        $old_qty = (int)$_POST['old_qty'];
        $new_qty = (int)$_POST['new_qty'];
        $notes = trim($_POST['notes']);

        if (empty($tx_id) || empty($item_id) || $new_qty <= 0 || empty($notes)) {
            $_SESSION['error'] = "Data koreksi tidak valid.";
            header("Location: transactions.php");
            exit;
        }

        if ($old_qty === $new_qty) {
            // Hanya update notes
            $stmt = $pdo->prepare("UPDATE stock_transactions SET notes = ? WHERE id = ?");
            $stmt->execute([$notes, $tx_id]);
            $_SESSION['success'] = "Keterangan transaksi berhasil diupdate.";
            header("Location: transactions.php");
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Lock item
            $stmt = $pdo->prepare("SELECT item_code, current_stock FROM items WHERE id = ? FOR UPDATE");
            $stmt->execute([$item_id]);
            $item = $stmt->fetch();

            $current_stock = (int)$item['current_stock'];
            
            // Hitung selisih
            $diff = $new_qty - $old_qty; // Jika IN: 10 jadi 15 -> diff = +5. Jika IN: 10 jadi 5 -> diff = -5
            $new_stock = $current_stock;

            if ($tx_type === 'IN') {
                $new_stock = $current_stock + $diff;
            } elseif ($tx_type === 'OUT') {
                // OUT: Jika 10 jadi 15 -> diff = +5, berarti stok harus berkurang 5.
                // OUT: Jika 10 jadi 5 -> diff = -5, berarti stok bertambah 5.
                $new_stock = $current_stock - $diff;
            }

            if ($new_stock < 0) {
                throw new Exception("Koreksi ditolak. Jika dikoreksi, stok akhir barang akan menjadi minus ($new_stock).");
            }

            // Update Stok
            $stmt_update = $pdo->prepare("UPDATE items SET current_stock = ? WHERE id = ?");
            $stmt_update->execute([$new_stock, $item_id]);

            // Update Transaksi (Append Note " [Dikoreksi Admin: alasan]")
            $new_notes = $notes . " [Koreksi dari Qty Lama: $old_qty]";
            $stmt_tx = $pdo->prepare("UPDATE stock_transactions SET quantity = ?, notes = ? WHERE id = ?");
            $stmt_tx->execute([$new_qty, $new_notes, $tx_id]);

            // Log Aktivitas
            log_activity($pdo, "KOREKSI_TRANSAKSI", "Mengkoreksi TX #$tx_id (Barang: {$item['item_code']}). Qty: $old_qty -> $new_qty. Alasan: $notes");

            $pdo->commit();
            $_SESSION['success'] = "Koreksi transaksi berhasil disimpan dan stok telah disesuaikan.";
            header("Location: transactions.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
            header("Location: transactions.php");
            exit;
        }
    }
}
header("Location: transactions.php");
exit;
?>
