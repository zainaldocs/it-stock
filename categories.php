<?php
// categories.php
$page_title = 'Manajemen Kategori';
require_once __DIR__ . '/layouts/header.php';
require_admin();

// Ambil data kategori
$stmt = $pdo->query("SELECT * FROM categories_type ORDER BY type, name");
$categories = $stmt->fetchAll();
?>

<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Kelola master data Kategori Barang, Lokasi, dan Satuan.</p>
    <button onclick="openModal('addModal')" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded shadow-sm flex items-center transition">
        <i class="fas fa-plus mr-2"></i> Tambah Kategori
    </button>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16 text-center">No</th>
                    <th scope="col" class="px-6 py-3">Nama Kategori</th>
                    <th scope="col" class="px-6 py-3 w-48 text-center">Tipe</th>
                    <th scope="col" class="px-6 py-3 w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($categories) > 0): ?>
                    <?php $no = 1; foreach ($categories as $cat): ?>
                        <tr class="bg-white border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-center"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 font-medium text-gray-800"><?php echo h($cat['name']); ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($cat['type'] === 'Barang'): ?>
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded border border-blue-200">Barang</span>
                                <?php elseif ($cat['type'] === 'Lokasi'): ?>
                                    <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded border border-purple-200">Lokasi</span>
                                <?php else: ?>
                                    <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded border border-yellow-200">Satuan</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <button onclick="openEditModal(<?php echo $cat['id']; ?>, '<?php echo h(addslashes($cat['name'])); ?>', '<?php echo $cat['type']; ?>')" class="text-blue-600 hover:text-blue-800" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="confirmDelete(<?php echo $cat['id']; ?>)" class="text-red-600 hover:text-red-800" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data kategori</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-lg">
            <h3 class="text-lg font-semibold text-gray-800">Tambah Kategori</h3>
            <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="category_action.php" method="POST">
            <input type="hidden" name="action" value="create">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Pilih Tipe --</option>
                        <option value="Barang">Kategori Barang</option>
                        <option value="Lokasi">Lokasi Penyimpanan</option>
                        <option value="Satuan">Satuan (Pcs, Unit, dll)</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-700 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-emerald-800 focus:outline-none">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-lg">
            <h3 class="text-lg font-semibold text-gray-800">Edit Kategori</h3>
            <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="category_action.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe <span class="text-red-500">*</span></label>
                    <select name="type" id="edit_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="Barang">Kategori Barang</option>
                        <option value="Lokasi">Lokasi Penyimpanan</option>
                        <option value="Satuan">Satuan (Pcs, Unit, dll)</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Form Hapus Hidden -->
<form id="deleteForm" action="category_action.php" method="POST" class="hidden">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function openEditModal(id, name, type) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_type').value = type;
    openModal('editModal');
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data kategori ini akan dihapus permanen. Jika data ini sedang digunakan oleh barang, mungkin akan dikosongkan (SET NULL).",
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

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
