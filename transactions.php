<?php
// transactions.php
$page_title = 'Riwayat Transaksi';
require_once __DIR__ . '/layouts/header.php';

$role = $_SESSION['role'];

// Pagination
$limit = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter
$type_filter = $_GET['type'] ?? '';

$sql_count = "SELECT COUNT(*) FROM stock_transactions";
$sql_data = "
    SELECT t.*, i.item_name, i.item_code, u.username 
    FROM stock_transactions t
    JOIN items i ON t.item_id = i.id
    LEFT JOIN users u ON t.user_id = u.id
";

if ($type_filter === 'IN' || $type_filter === 'OUT') {
    $sql_count .= " WHERE transaction_type = '$type_filter'";
    $sql_data .= " WHERE t.transaction_type = '$type_filter'";
}

$sql_data .= " ORDER BY t.transaction_date DESC LIMIT $limit OFFSET $offset";

$total_tx = $pdo->query($sql_count)->fetchColumn();
$total_pages = ceil($total_tx / $limit);

$transactions = $pdo->query($sql_data)->fetchAll();
?>

<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <p class="text-gray-600">Catatan riwayat barang masuk dan keluar.</p>
    
    <div class="flex flex-wrap items-center gap-2">
        <!-- Filter Dropdown -->
        <form action="" method="GET" class="flex items-center">
            <select name="type" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm bg-white">
                <option value="">Semua Tipe</option>
                <option value="IN" <?php echo $type_filter === 'IN' ? 'selected' : ''; ?>>Barang Masuk (IN)</option>
                <option value="OUT" <?php echo $type_filter === 'OUT' ? 'selected' : ''; ?>>Barang Keluar (OUT)</option>
            </select>
        </form>

        <a href="transaction_form.php?type=IN" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded shadow-sm flex items-center transition text-sm">
            <i class="fas fa-arrow-down mr-2"></i> Barang Masuk
        </a>
        <a href="transaction_form.php?type=OUT" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded shadow-sm flex items-center transition text-sm">
            <i class="fas fa-arrow-up mr-2"></i> Barang Keluar
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Tanggal</th>
                    <th scope="col" class="px-6 py-3">Barang</th>
                    <th scope="col" class="px-6 py-3 text-center">Tipe</th>
                    <th scope="col" class="px-6 py-3 text-center">Qty</th>
                    <th scope="col" class="px-6 py-3">Keterangan</th>
                    <th scope="col" class="px-6 py-3">User</th>
                    <?php if ($role === 'Admin'): ?>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $tx): ?>
                        <tr class="bg-white border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <?php echo date('d/m/Y H:i', strtotime($tx['transaction_date'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800"><?php echo h($tx['item_name']); ?></div>
                                <div class="text-xs text-gray-500 font-mono"><?php echo h($tx['item_code']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($tx['transaction_type'] === 'IN'): ?>
                                    <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2 py-1 rounded border border-emerald-200">IN</span>
                                <?php else: ?>
                                    <span class="bg-orange-100 text-orange-800 text-xs font-semibold px-2 py-1 rounded border border-orange-200">OUT</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-lg <?php echo $tx['transaction_type'] === 'IN' ? 'text-emerald-600' : 'text-orange-600'; ?>">
                                <?php echo $tx['transaction_type'] === 'IN' ? '+' : '-'; ?><?php echo number_format($tx['quantity']); ?>
                            </td>
                            <td class="px-6 py-4 min-w-[200px]">
                                <?php echo nl2br(h($tx['notes'])); ?>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium">
                                <?php echo h($tx['username'] ?? 'Sistem'); ?>
                            </td>
                            
                            <?php if ($role === 'Admin'): ?>
                            <td class="px-6 py-4 text-center">
                                <button onclick="openEditModal(<?php echo $tx['id']; ?>, <?php echo $tx['quantity']; ?>, '<?php echo h(addslashes($tx['notes'])); ?>', '<?php echo $tx['transaction_type']; ?>', <?php echo $tx['item_id']; ?>)" class="text-blue-600 hover:text-blue-800" title="Koreksi Transaksi">
                                    <i class="fas fa-edit"></i> Koreksi
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $role === 'Admin' ? '7' : '6'; ?>" class="px-6 py-8 text-center text-gray-500">Belum ada transaksi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center bg-gray-50 rounded-b-lg">
        <span class="text-sm text-gray-600">
            Halaman <strong><?php echo $page; ?></strong> dari <strong><?php echo $total_pages; ?></strong>
        </span>
        <nav class="inline-flex rounded-md shadow-sm">
            <?php 
                $base_url_page = "?type=$type_filter&page=";
            ?>
            <?php if ($page > 1): ?>
                <a href="<?php echo $base_url_page . ($page - 1); ?>" class="py-2 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100">Prev</a>
            <?php endif; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="<?php echo $base_url_page . ($page + 1); ?>" class="py-2 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 <?php echo $page == 1 ? 'rounded-l-lg' : ''; ?> rounded-r-lg border-l-0 hover:bg-gray-100">Next</a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php if ($role === 'Admin'): ?>
<!-- Modal Edit Transaksi (Koreksi) -->
<div id="editTxModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-orange-50 rounded-t-lg">
            <h3 class="text-lg font-semibold text-orange-800"><i class="fas fa-exclamation-triangle mr-2"></i> Koreksi Transaksi</h3>
            <button onclick="closeModal('editTxModal')" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="transaction_action.php" method="POST">
            <input type="hidden" name="action" value="koreksi">
            <input type="hidden" name="tx_id" id="edit_tx_id">
            <input type="hidden" name="item_id" id="edit_item_id">
            <input type="hidden" name="tx_type" id="edit_tx_type">
            <input type="hidden" name="old_qty" id="edit_old_qty">
            
            <div class="p-6 space-y-4">
                <div class="bg-yellow-50 p-3 rounded text-xs text-yellow-800 border border-yellow-200">
                    <strong>Peringatan:</strong> Mengubah quantity akan mengkalkulasi ulang stok barang saat ini. Gunakan fitur ini hanya jika terjadi salah input oleh staf.
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Baru <span class="text-red-500">*</span></label>
                    <input type="number" min="1" name="new_qty" id="edit_new_qty" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Alasan Koreksi <span class="text-red-500">*</span></label>
                    <textarea name="notes" id="edit_notes" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editTxModal')" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-orange-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-orange-700">Simpan Koreksi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, qty, notes, type, item_id) {
    document.getElementById('edit_tx_id').value = id;
    document.getElementById('edit_item_id').value = item_id;
    document.getElementById('edit_tx_type').value = type;
    document.getElementById('edit_old_qty').value = qty;
    document.getElementById('edit_new_qty').value = qty;
    document.getElementById('edit_notes').value = notes;
    document.getElementById('editTxModal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
