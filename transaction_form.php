<?php
// transaction_form.php
$type = $_GET['type'] ?? 'IN';
if ($type !== 'IN' && $type !== 'OUT') {
    $type = 'IN';
}

$page_title = $type === 'IN' ? 'Input Barang Masuk (IN)' : 'Input Barang Keluar (OUT)';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/layouts/header.php';

// Ambil daftar barang untuk dropdown
$stmt = $pdo->query("SELECT id, item_code, item_name, current_stock FROM items ORDER BY item_name ASC");
$items = $stmt->fetchAll();
?>

<div class="mb-6">
    <a href="transactions.php" class="text-emerald-700 hover:text-emerald-900 font-medium text-sm">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Riwayat Transaksi
    </a>
</div>

<div class="bg-white rounded-lg shadow-xl border-t-4 <?php echo $type === 'IN' ? 'border-emerald-600' : 'border-orange-500'; ?> p-6 max-w-2xl mx-auto">
    
    <div class="flex items-center mb-6">
        <div class="p-3 rounded-full <?php echo $type === 'IN' ? 'bg-emerald-100 text-emerald-600' : 'bg-orange-100 text-orange-600'; ?> mr-4">
            <i class="fas <?php echo $type === 'IN' ? 'fa-arrow-down' : 'fa-arrow-up'; ?> text-2xl"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $page_title; ?></h2>
            <p class="text-sm text-gray-500">
                <?php echo $type === 'IN' ? 'Menambah jumlah stok barang yang ada.' : 'Mengurangi jumlah stok barang untuk digunakan/didistribusikan.'; ?>
            </p>
        </div>
    </div>

    <form action="transaction_action.php" method="POST" id="txForm">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="transaction_type" value="<?php echo $type; ?>">

        <div class="space-y-6">
            
            <!-- Pilih Barang -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Barang <span class="text-red-500">*</span></label>
                <select name="item_id" id="item_id" class="tom-select w-full" required>
                    <option value="">-- Ketik untuk mencari barang (Kode / Nama) --</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?php echo $item['id']; ?>" data-stock="<?php echo $item['current_stock']; ?>">
                            [<?php echo h($item['item_code']); ?>] <?php echo h($item['item_name']); ?> (Stok: <?php echo $item['current_stock']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($type === 'OUT'): ?>
                    <p class="text-xs text-orange-600 mt-1 font-medium" id="stockInfoDisplay">Silakan pilih barang untuk melihat stok tersedia.</p>
                <?php endif; ?>
            </div>

            <!-- Quantity -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah (Quantity) <span class="text-red-500">*</span></label>
                <input type="number" min="1" name="quantity" id="quantity" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 text-lg font-bold" placeholder="0">
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Keterangan <span class="text-red-500">*</span>
                </label>
                <textarea name="notes" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="<?php echo $type === 'IN' ? 'Contoh: Pembelian dari Supplier A (Nota #123)' : 'Contoh: Diberikan ke Staff B (Divisi IT) / Digunakan untuk PC 01'; ?>"></textarea>
                <p class="text-xs text-gray-500 mt-1">Wajib diisi untuk kejelasan alokasi atau asal usul barang.</p>
            </div>

        </div>

        <div class="mt-8 border-t border-gray-200 pt-5 flex justify-end">
            <button type="submit" class="w-full sm:w-auto <?php echo $type === 'IN' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-orange-500 hover:bg-orange-600'; ?> text-white font-bold py-3 px-8 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors shadow-md">
                <i class="fas fa-check-circle mr-2"></i> Proses Transaksi <?php echo $type; ?>
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Tom Select
        var selectEl = document.getElementById('item_id');
        var tomSelect = new TomSelect(selectEl, {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        <?php if ($type === 'OUT'): ?>
        // Logic khusus OUT: Tampilkan info stok dan validasi max
        var stockInfoDisplay = document.getElementById('stockInfoDisplay');
        var quantityInput = document.getElementById('quantity');
        var currentMaxStock = 0;

        tomSelect.on('change', function(value) {
            if (value) {
                var option = tomSelect.options[value];
                var stockText = option.text.match(/Stok: (\d+)/);
                if (stockText && stockText[1]) {
                    currentMaxStock = parseInt(stockText[1], 10);
                    stockInfoDisplay.textContent = "Stok Tersedia: " + currentMaxStock;
                    
                    if (currentMaxStock === 0) {
                        stockInfoDisplay.className = "text-xs mt-1 font-bold text-red-600";
                        stockInfoDisplay.textContent += " (Stok Kosong! Tidak bisa OUT)";
                        quantityInput.max = 0;
                        quantityInput.value = '';
                    } else {
                        stockInfoDisplay.className = "text-xs mt-1 font-medium text-emerald-600";
                        quantityInput.max = currentMaxStock;
                    }
                }
            } else {
                stockInfoDisplay.textContent = "Silakan pilih barang untuk melihat stok tersedia.";
                stockInfoDisplay.className = "text-xs text-orange-600 mt-1 font-medium";
                currentMaxStock = 0;
                quantityInput.removeAttribute('max');
            }
        });

        // Form Validation on Submit
        document.getElementById('txForm').addEventListener('submit', function(e) {
            var qty = parseInt(quantityInput.value, 10);
            if (currentMaxStock === 0) {
                e.preventDefault();
                Swal.fire('Error', 'Barang ini sedang kosong (Stok 0).', 'error');
                return;
            }
            if (qty > currentMaxStock) {
                e.preventDefault();
                Swal.fire('Error', 'Quantity (' + qty + ') melebihi stok yang tersedia (' + currentMaxStock + ').', 'error');
            }
        });
        <?php endif; ?>
    });
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
