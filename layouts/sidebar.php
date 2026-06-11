<?php
// layouts/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'];
?>
<div id="sidebar" class="bg-emerald-900 text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full lg:relative lg:translate-x-0 transition duration-200 ease-in-out z-20 flex flex-col">
    
    <!-- Logo -->
    <a href="<?php echo base_url('dashboard.php'); ?>" class="text-white flex items-center space-x-2 px-4 mb-6">
        <i class="fas fa-boxes text-3xl"></i>
        <span class="text-2xl font-extrabold tracking-tight">IT <span class="text-emerald-400">Stock</span></span>
    </a>

    <!-- Nav Links -->
    <nav class="flex-1 space-y-2">
        <a href="<?php echo base_url('dashboard.php'); ?>" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page == 'dashboard.php' ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-800 hover:text-white'; ?>">
            <i class="fas fa-home mr-2 w-5 text-center"></i> Dashboard
        </a>
        
        <div class="px-4 py-2 mt-4 text-xs font-semibold text-emerald-400 uppercase tracking-wider">
            Manajemen Inventaris
        </div>
        <a href="<?php echo base_url('items.php'); ?>" class="block py-2.5 px-4 rounded transition duration-200 <?php echo strpos($current_page, 'item') !== false ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-800 hover:text-white'; ?>">
            <i class="fas fa-box-open mr-2 w-5 text-center"></i> Data Barang
        </a>
        <a href="<?php echo base_url('transactions.php'); ?>" class="block py-2.5 px-4 rounded transition duration-200 <?php echo strpos($current_page, 'transaction') !== false ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-800 hover:text-white'; ?>">
            <i class="fas fa-exchange-alt mr-2 w-5 text-center"></i> Transaksi IN/OUT
        </a>
        <a href="<?php echo base_url('reports.php'); ?>" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page == 'reports.php' ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-800 hover:text-white'; ?>">
            <i class="fas fa-chart-bar mr-2 w-5 text-center"></i> Laporan Bulanan
        </a>

        <?php if ($role === 'Admin'): ?>
        <div class="px-4 py-2 mt-6 text-xs font-semibold text-emerald-400 uppercase tracking-wider">
            Administrator
        </div>
        <a href="<?php echo base_url('categories.php'); ?>" class="block py-2.5 px-4 rounded transition duration-200 <?php echo strpos($current_page, 'categor') !== false ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-800 hover:text-white'; ?>">
            <i class="fas fa-tags mr-2 w-5 text-center"></i> Kategori & Master
        </a>
        <a href="<?php echo base_url('users.php'); ?>" class="block py-2.5 px-4 rounded transition duration-200 <?php echo strpos($current_page, 'user') !== false ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-800 hover:text-white'; ?>">
            <i class="fas fa-users mr-2 w-5 text-center"></i> Manajemen User
        </a>
        <a href="<?php echo base_url('logs.php'); ?>" class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page == 'logs.php' ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-800 hover:text-white'; ?>">
            <i class="fas fa-history mr-2 w-5 text-center"></i> Audit Trail
        </a>
        <?php endif; ?>
    </nav>
</div>

<!-- Overlay untuk mobile -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black opacity-50 z-10 hidden lg:hidden"></div>
