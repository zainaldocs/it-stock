<?php
// item_form.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_admin();

$id = $_GET['id'] ?? null;
$item = null;
$page_title = 'Tambah Barang Baru';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        $_SESSION['error'] = "Barang tidak ditemukan.";
        header("Location: items.php");
        exit;
    }
    $page_title = 'Edit Barang: ' . h($item['item_code']);
}

// Ambil data dropdown kategori
$categories_raw = $pdo->query("SELECT * FROM categories_type ORDER BY name")->fetchAll();
$cat_barang = array_filter($categories_raw, fn($c) => $c['type'] === 'Barang');
$cat_lokasi = array_filter($categories_raw, fn($c) => $c['type'] === 'Lokasi');
$cat_satuan = array_filter($categories_raw, fn($c) => $c['type'] === 'Satuan');

require_once __DIR__ . '/layouts/header.php';
?>

<div class="mb-6">
    <a href="items.php" class="text-emerald-700 hover:text-emerald-900 font-medium text-sm">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Barang
    </a>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-3xl">
    <form action="item_action.php" method="POST">
        <input type="hidden" name="action" value="<?php echo $id ? 'update' : 'create'; ?>">
        <?php if ($id): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            <!-- Kode Barang -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Barang <span class="text-red-500">*</span></label>
                <input type="text" name="item_code" value="<?php echo $item ? h($item['item_code']) : ''; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50 uppercase" placeholder="Contoh: LAP-001">
                <p class="text-xs text-gray-500 mt-1">Harus unik, tidak boleh sama dengan barang lain.</p>
            </div>

            <!-- Nama Barang -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang <span class="text-red-500">*</span></label>
                <input type="text" name="item_name" value="<?php echo $item ? h($item['item_name']) : ''; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Contoh: Laptop Thinkpad T14">
            </div>

            <!-- Kategori Barang -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Barang</label>
                <select name="category_barang_id" class="tom-select w-full">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($cat_barang as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($item && $item['category_barang_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo h($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Lokasi -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Penyimpanan</label>
                <select name="category_lokasi_id" class="tom-select w-full">
                    <option value="">-- Pilih Lokasi --</option>
                    <?php foreach ($cat_lokasi as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($item && $item['category_lokasi_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo h($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Satuan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                <select name="category_satuan_id" class="tom-select w-full">
                    <option value="">-- Pilih Satuan --</option>
                    <?php foreach ($cat_satuan as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($item && $item['category_satuan_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo h($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Stok Minimum -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Batas Minimum Stok <span class="text-red-500">*</span></label>
                <input type="number" min="0" name="minimum_stock" value="<?php echo $item ? $item['minimum_stock'] : '0'; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p class="text-xs text-gray-500 mt-1">Jika stok menyentuh angka ini, sistem akan memberi peringatan Stok Kritis.</p>
            </div>

            <!-- Stok Awal (Hanya untuk tambah baru) -->
            <?php if (!$item): ?>
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal (Optional)</label>
                <input type="number" min="0" name="initial_stock" value="0" class="w-full md:w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <?php endif; ?>

        </div>

        <div class="mt-6 border-t border-gray-200 pt-5 flex justify-end">
            <button type="submit" class="bg-emerald-800 text-white font-semibold py-2 px-6 rounded hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
                <i class="fas fa-save mr-2"></i> Simpan Barang
            </button>
        </div>
    </form>
</div>

<script>
    // Initialize Tom Select for searchable dropdowns
    document.querySelectorAll('.tom-select').forEach((el) => {
        new TomSelect(el, {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    });
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
