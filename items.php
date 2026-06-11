<?php
// items.php
$page_title = 'Data Barang';
require_once __DIR__ . '/layouts/header.php';

$role = $_SESSION['role'];
$filter = $_GET['filter'] ?? '';

$sql = "
    SELECT i.*, 
           cb.name as cat_barang, 
           cl.name as cat_lokasi, 
           cs.name as cat_satuan
    FROM items i
    LEFT JOIN categories_type cb ON i.category_barang_id = cb.id
    LEFT JOIN categories_type cl ON i.category_lokasi_id = cl.id
    LEFT JOIN categories_type cs ON i.category_satuan_id = cs.id
";

if ($filter === 'kritis') {
    $sql .= " WHERE i.current_stock <= i.minimum_stock";
}

$sql .= " ORDER BY i.item_name ASC";

$stmt = $pdo->query($sql);
$items = $stmt->fetchAll();
?>

<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <p class="text-gray-600">Daftar semua inventaris barang. <?php if ($role === 'Admin') echo "Anda dapat menambah, mengedit, dan menghapus barang."; ?></p>
    
    <div class="flex items-center space-x-2">
        <?php if ($filter === 'kritis'): ?>
            <a href="items.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow-sm flex items-center transition text-sm">
                <i class="fas fa-times mr-2"></i> Hapus Filter
            </a>
        <?php else: ?>
            <a href="items.php?filter=kritis" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded shadow-sm flex items-center transition text-sm">
                <i class="fas fa-filter mr-2"></i> Filter Kritis
            </a>
        <?php endif; ?>

        <?php if ($role === 'Admin'): ?>
            <a href="item_form.php" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded shadow-sm flex items-center transition text-sm">
                <i class="fas fa-plus mr-2"></i> Tambah Barang
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
        <div class="relative w-full max-w-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm" placeholder="Cari Kode atau Nama Barang...">
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600" id="itemsTable">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Kode Barang</th>
                    <th scope="col" class="px-6 py-3">Nama Barang</th>
                    <th scope="col" class="px-6 py-3">Kategori</th>
                    <th scope="col" class="px-6 py-3">Lokasi</th>
                    <th scope="col" class="px-6 py-3 text-center">Stok</th>
                    <?php if ($role === 'Admin'): ?>
                    <th scope="col" class="px-6 py-3 text-center w-24">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($items) > 0): ?>
                    <?php foreach ($items as $item): ?>
                        <?php $is_critical = $item['current_stock'] <= $item['minimum_stock']; ?>
                        <tr class="bg-white border-b hover:bg-gray-50 transition <?php echo $is_critical ? 'bg-red-50/30' : ''; ?> item-row">
                            <td class="px-6 py-4 font-mono font-medium text-emerald-800 search-col-code">
                                <?php echo h($item['item_code']); ?>
                            </td>
                            <td class="px-6 py-4 search-col-name">
                                <div class="font-semibold text-gray-800"><?php echo h($item['item_name']); ?></div>
                                <?php if ($is_critical): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800 mt-1">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Stok Kritis
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo h($item['cat_barang'] ?? '-'); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo h($item['cat_lokasi'] ?? '-'); ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-lg font-bold <?php echo $is_critical ? 'text-red-600' : 'text-gray-800'; ?>">
                                    <?php echo number_format($item['current_stock']); ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Min: <?php echo number_format($item['minimum_stock']); ?> <?php echo h($item['cat_satuan'] ?? ''); ?>
                                </div>
                            </td>
                            
                            <?php if ($role === 'Admin'): ?>
                            <td class="px-6 py-4 text-center space-x-2">
                                <a href="item_form.php?id=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-800" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmDelete(<?php echo $item['id']; ?>)" class="text-red-600 hover:text-red-800" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $role === 'Admin' ? '6' : '5'; ?>" class="px-6 py-8 text-center text-gray-500">Tidak ada data barang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($role === 'Admin'): ?>
<!-- Form Hapus Hidden -->
<form id="deleteForm" action="item_action.php" method="POST" class="hidden">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Barang ini?',
        text: "Data barang akan dihapus permanen beserta seluruh riwayat transaksinya (Cascade).",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    })
}
</script>
<?php endif; ?>

<script>
// Simple Client-side Search
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('.item-row');
    
    rows.forEach(row => {
        let code = row.querySelector('.search-col-code').textContent.toLowerCase();
        let name = row.querySelector('.search-col-name').textContent.toLowerCase();
        
        if (code.includes(filter) || name.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
