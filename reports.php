<?php
// reports.php
$page_title = 'Laporan Matriks Tahunan';
require_once __DIR__ . '/layouts/header.php';

$year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Query untuk laporan matriks bulanan (IN & OUT) per barang
$sql = "
    SELECT 
        i.item_code, 
        i.item_name, 
        cl.name as cat_lokasi,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 1 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS jan_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 1 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS jan_out,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 2 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS feb_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 2 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS feb_out,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 3 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS mar_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 3 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS mar_out,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 4 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS apr_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 4 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS apr_out,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 5 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS mei_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 5 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS mei_out,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 6 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS jun_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 6 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS jun_out,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 7 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS jul_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 7 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS jul_out,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 8 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS agu_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 8 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS agu_out,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 9 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS sep_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 9 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS sep_out,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 10 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS okt_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 10 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS okt_out,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 11 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS nov_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 11 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS nov_out,
        
        SUM(CASE WHEN MONTH(t.transaction_date) = 12 AND t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) AS des_in,
        SUM(CASE WHEN MONTH(t.transaction_date) = 12 AND t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) AS des_out
        
    FROM items i
    LEFT JOIN stock_transactions t ON i.id = t.item_id AND YEAR(t.transaction_date) = ?
    LEFT JOIN categories_type cl ON i.category_lokasi_id = cl.id
    GROUP BY i.id
    ORDER BY i.item_name ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$year]);
$reports = $stmt->fetchAll();

$months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
?>

<!-- Include SheetJS (Excel) & html2pdf (PDF) -->
<script src="assets/js/xlsx.full.min.js"></script>
<script src="assets/js/html2pdf.bundle.min.js"></script>

<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <p class="text-gray-600">Laporan transaksi masuk (IN) dan keluar (OUT) per bulan.</p>
    
    <div class="flex flex-wrap items-center gap-2">
        <!-- Filter Tahun -->
        <form action="" method="GET" class="flex items-center mr-2">
            <select name="year" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold bg-white">
                <?php 
                $start_year = 2020;
                $current_year = date('Y');
                for ($y = $current_year; $y >= $start_year; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>Tahun <?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </form>

        <button onclick="exportExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded shadow-sm flex items-center transition text-sm">
            <i class="fas fa-file-excel mr-2"></i> Ekspor Excel
        </button>
        <button onclick="exportPDF()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow-sm flex items-center transition text-sm">
            <i class="fas fa-file-pdf mr-2"></i> Ekspor PDF
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" id="reportContainer">
    <div class="p-4 bg-white border-b hidden" id="reportHeaderPdf">
        <h2 class="text-xl font-bold text-center text-gray-800">LAPORAN INVENTARIS BARANG</h2>
        <p class="text-center text-gray-600">Tahun: <?php echo $year; ?></p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left text-gray-600 table-auto border-collapse" id="reportTable">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th rowspan="2" class="px-3 py-2 border border-gray-300 text-center">No</th>
                    <th rowspan="2" class="px-3 py-2 border border-gray-300">Kode</th>
                    <th rowspan="2" class="px-3 py-2 border border-gray-300 min-w-[150px]">Nama Barang</th>
                    <th rowspan="2" class="px-3 py-2 border border-gray-300">Lokasi</th>
                    <?php foreach ($months as $m): ?>
                        <th colspan="2" class="px-2 py-2 border border-gray-300 text-center bg-gray-200"><?php echo $m; ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php for ($i=0; $i<12; $i++): ?>
                        <th class="px-1 py-1 border border-gray-300 text-center text-[10px] bg-emerald-50 text-emerald-700">IN</th>
                        <th class="px-1 py-1 border border-gray-300 text-center text-[10px] bg-orange-50 text-orange-700">OUT</th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($reports) > 0): ?>
                    <?php $no = 1; foreach ($reports as $row): ?>
                        <tr class="bg-white hover:bg-gray-50 transition">
                            <td class="px-3 py-2 border border-gray-300 text-center"><?php echo $no++; ?></td>
                            <td class="px-3 py-2 border border-gray-300 font-mono"><?php echo h($row['item_code']); ?></td>
                            <td class="px-3 py-2 border border-gray-300 font-medium text-gray-800"><?php echo h($row['item_name']); ?></td>
                            <td class="px-3 py-2 border border-gray-300"><?php echo h($row['cat_lokasi'] ?? '-'); ?></td>
                            
                            <?php 
                            $month_keys = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
                            foreach ($month_keys as $mk): 
                                $in_val = $row[$mk.'_in'];
                                $out_val = $row[$mk.'_out'];
                            ?>
                                <td class="px-1 py-2 border border-gray-300 text-center font-bold <?php echo $in_val > 0 ? 'text-emerald-600' : 'text-gray-300 font-normal'; ?>">
                                    <?php echo $in_val > 0 ? $in_val : '-'; ?>
                                </td>
                                <td class="px-1 py-2 border border-gray-300 text-center font-bold <?php echo $out_val > 0 ? 'text-orange-600' : 'text-gray-300 font-normal'; ?>">
                                    <?php echo $out_val > 0 ? $out_val : '-'; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="28" class="px-6 py-8 text-center text-gray-500">Belum ada data barang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function exportExcel() {
    var wb = XLSX.utils.table_to_book(document.getElementById('reportTable'), {sheet: "Laporan <?php echo $year; ?>"});
    XLSX.writeFile(wb, "Laporan_Inventaris_<?php echo $year; ?>.xlsx");
}

function exportPDF() {
    // Tampilkan header khusus PDF sebelum di-render
    document.getElementById('reportHeaderPdf').classList.remove('hidden');
    
    var element = document.getElementById('reportContainer');
    var opt = {
        margin:       0.2,
        filename:     'Laporan_Inventaris_<?php echo $year; ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
    };
    
    // Gunakan html2pdf
    html2pdf().set(opt).from(element).save().then(() => {
        // Sembunyikan kembali header setelah selesai
        document.getElementById('reportHeaderPdf').classList.add('hidden');
    });
}
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
