<?php
// dashboard.php
$page_title = 'Dashboard';
require_once __DIR__ . '/layouts/header.php';

// Ambil Total Jenis Barang
$stmt = $pdo->query("SELECT COUNT(*) FROM items");
$total_items = $stmt->fetchColumn();

// Ambil Total Transaksi Hari Ini
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_transactions WHERE DATE(transaction_date) = ?");
$stmt->execute([$today]);
$total_transactions_today = $stmt->fetchColumn();

// Ambil Total Barang Stok Menipis
$stmt = $pdo->query("SELECT COUNT(*) FROM items WHERE current_stock <= minimum_stock");
$total_critical_items = $stmt->fetchColumn();

// Pagination Transaksi Terbaru
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$stmt_total_tx = $pdo->query("SELECT COUNT(*) FROM stock_transactions");
$total_tx = $stmt_total_tx->fetchColumn();
$total_pages = ceil($total_tx / $limit);

$stmt_tx = $pdo->prepare("
    SELECT t.*, i.item_name, i.item_code, u.username 
    FROM stock_transactions t
    JOIN items i ON t.item_id = i.id
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY t.transaction_date DESC
    LIMIT $limit OFFSET $offset
");
$stmt_tx->execute();
$recent_transactions = $stmt_tx->fetchAll();
?>

<!-- Metrics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- Card 1 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center hover:shadow-md transition duration-200">
        <div class="p-4 rounded-full bg-blue-100 text-blue-600 mr-4">
            <i class="fas fa-boxes text-2xl"></i>
        </div>
        <div>
            <p class="mb-1 text-sm font-medium text-gray-500">Total Jenis Barang</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_items); ?></p>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center hover:shadow-md transition duration-200">
        <div class="p-4 rounded-full bg-emerald-100 text-emerald-600 mr-4">
            <i class="fas fa-exchange-alt text-2xl"></i>
        </div>
        <div>
            <p class="mb-1 text-sm font-medium text-gray-500">Transaksi Hari Ini</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_transactions_today); ?></p>
        </div>
    </div>

    <!-- Card 3 (Link) -->
    <a href="<?php echo base_url('items.php?filter=kritis'); ?>" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center hover:shadow-md hover:border-red-300 transition duration-200 group">
        <div class="p-4 rounded-full bg-red-100 text-red-600 mr-4 group-hover:bg-red-200 transition">
            <i class="fas fa-exclamation-triangle text-2xl"></i>
        </div>
        <div>
            <p class="mb-1 text-sm font-medium text-gray-500 group-hover:text-red-500 transition">Barang Stok Kritis</p>
            <p class="text-3xl font-bold <?php echo $total_critical_items > 0 ? 'text-red-600' : 'text-gray-800'; ?>">
                <?php echo number_format($total_critical_items); ?>
            </p>
        </div>
    </a>

</div>

<!-- Recent Transactions Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-lg">
        <h2 class="text-lg font-semibold text-gray-800">Transaksi Terbaru</h2>
        <a href="<?php echo base_url('transactions.php'); ?>" class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">Lihat Semua <i class="fas fa-arrow-right ml-1"></i></a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Waktu</th>
                    <th scope="col" class="px-6 py-3">Barang</th>
                    <th scope="col" class="px-6 py-3 text-center">Tipe</th>
                    <th scope="col" class="px-6 py-3 text-center">Qty</th>
                    <th scope="col" class="px-6 py-3">Keterangan</th>
                    <th scope="col" class="px-6 py-3">User</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recent_transactions) > 0): ?>
                    <?php foreach ($recent_transactions as $tx): ?>
                        <tr class="bg-white border-b hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo date('d/m/Y H:i', strtotime($tx['transaction_date'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800"><?php echo h($tx['item_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo h($tx['item_code']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($tx['transaction_type'] === 'IN'): ?>
                                    <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-emerald-200">IN</span>
                                <?php else: ?>
                                    <span class="bg-orange-100 text-orange-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-orange-200">OUT</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold <?php echo $tx['transaction_type'] === 'IN' ? 'text-emerald-600' : 'text-orange-600'; ?>">
                                <?php echo $tx['transaction_type'] === 'IN' ? '+' : '-'; ?><?php echo number_format($tx['quantity']); ?>
                            </td>
                            <td class="px-6 py-4 truncate max-w-xs" title="<?php echo h($tx['notes']); ?>">
                                <?php echo h($tx['notes']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo h($tx['username'] ?? 'System'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada transaksi</td>
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
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="py-2 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-emerald-700">
                    Sebelumnya
                </a>
            <?php else: ?>
                <span class="py-2 px-4 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-l-lg cursor-not-allowed">
                    Sebelumnya
                </span>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="py-2 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-lg border-l-0 hover:bg-gray-100 hover:text-emerald-700">
                    Selanjutnya
                </a>
            <?php else: ?>
                <span class="py-2 px-4 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-r-lg border-l-0 cursor-not-allowed">
                    Selanjutnya
                </span>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
