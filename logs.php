<?php
// logs.php
$page_title = 'Audit Trail (Log Aktivitas)';
require_once __DIR__ . '/layouts/header.php';
require_admin();

// Pagination
$limit = 50;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) FROM activity_logs";
$total_logs = $pdo->query($sql_count)->fetchColumn();
$total_pages = ceil($total_logs / $limit);

$sql_data = "
    SELECT l.*, u.username 
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.log_date DESC
    LIMIT $limit OFFSET $offset
";
$logs = $pdo->query($sql_data)->fetchAll();
?>

<div class="mb-6">
    <p class="text-gray-600">Catatan aktivitas penting dalam sistem untuk keperluan audit.</p>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3 w-48">Waktu</th>
                    <th scope="col" class="px-6 py-3 w-32">User</th>
                    <th scope="col" class="px-6 py-3 w-48">Aksi</th>
                    <th scope="col" class="px-6 py-3">Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($logs) > 0): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="bg-white border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500 font-mono">
                                <?php echo date('d/m/Y H:i:s', strtotime($log['log_date'])); ?>
                            </td>
                            <td class="px-6 py-3 font-medium text-gray-800">
                                <?php echo h($log['username'] ?? 'System'); ?>
                            </td>
                            <td class="px-6 py-3">
                                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded border border-gray-200 font-bold tracking-wide">
                                    <?php echo h($log['action']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-xs text-gray-700">
                                <?php echo nl2br(h($log['details'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada log aktivitas</td>
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
                <a href="?page=<?php echo ($page - 1); ?>" class="py-2 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100">Prev</a>
            <?php endif; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo ($page + 1); ?>" class="py-2 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 <?php echo $page == 1 ? 'rounded-l-lg' : ''; ?> rounded-r-lg border-l-0 hover:bg-gray-100">Next</a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
