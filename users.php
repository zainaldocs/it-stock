<?php
// users.php
$page_title = 'Manajemen User';
require_once __DIR__ . '/layouts/header.php';
require_admin();

$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY role, username");
$users = $stmt->fetchAll();
?>

<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Kelola akun pengguna (Admin & Staff).</p>
    <button onclick="openModal('addModal')" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded shadow-sm flex items-center transition text-sm">
        <i class="fas fa-user-plus mr-2"></i> Tambah User
    </button>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16 text-center">No</th>
                    <th scope="col" class="px-6 py-3">Username</th>
                    <th scope="col" class="px-6 py-3 text-center">Role</th>
                    <th scope="col" class="px-6 py-3">Tgl Dibuat</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php $no = 1; foreach ($users as $u): ?>
                        <tr class="bg-white border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-center"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 font-bold text-gray-800">
                                <i class="fas fa-user-circle text-gray-400 mr-2 text-lg align-middle"></i>
                                <?php echo h($u['username']); ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($u['role'] === 'Admin'): ?>
                                    <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full font-bold border border-indigo-200">Admin</span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full font-bold border border-gray-200">Staff</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo date('d/m/Y', strtotime($u['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <button onclick="openEditModal(<?php echo $u['id']; ?>, '<?php echo h(addslashes($u['username'])); ?>', '<?php echo $u['role']; ?>')" class="text-blue-600 hover:text-blue-800" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <button onclick="confirmDelete(<?php echo $u['id']; ?>, '<?php echo h(addslashes($u['username'])); ?>')" class="text-red-600 hover:text-red-800" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php else: ?>
                                    <span class="text-gray-300" title="Tidak bisa menghapus akun sendiri"><i class="fas fa-trash"></i></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-lg">
            <h3 class="text-lg font-semibold text-gray-800">Tambah User Baru</h3>
            <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="user_action.php" method="POST">
            <input type="hidden" name="action" value="create">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="Staff">Staff</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-700 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-emerald-800">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-lg">
            <h3 class="text-lg font-semibold text-gray-800">Edit User</h3>
            <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="user_action.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" id="edit_username" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50" readonly>
                    <p class="text-xs text-gray-500 mt-1">Username tidak bisa diubah.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select name="role" id="edit_role" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="Staff">Staff</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Form Hapus Hidden -->
<form id="deleteForm" action="user_action.php" method="POST" class="hidden">
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

function openEditModal(id, username, role) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_role').value = role;
    openModal('editModal');
}

function confirmDelete(id, username) {
    Swal.fire({
        title: 'Hapus User?',
        text: "Anda akan menghapus user: " + username,
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
