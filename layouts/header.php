<?php
// layouts/header.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_login(); // Pastikan hanya user login yang bisa akses halaman dengan header ini
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Inventory Stock</title>
    <!-- Tailwind CSS CDN -->
    <link rel="stylesheet" href="assets/css/output.css">

    <!-- Font -->
    <link rel="stylesheet" href="assets/fonts/inter/index.css">
    <!-- Icons -->
    <link rel="stylesheet" href="assets/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <!-- Tom Select (untuk live search dropdown) -->
    <link href="assets/css/tom-select.css" rel="stylesheet">
    <script src="assets/js/tom-select.complete.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* gray-100 */
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 h-screen flex overflow-hidden">
    
    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Top Navbar -->
        <header class="flex justify-between items-center py-4 px-6 bg-white border-b-4 border-emerald-800">
            <div class="flex items-center">
                <button id="sidebarToggle" class="text-gray-500 focus:outline-none lg:hidden mr-4">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h1 class="text-2xl font-semibold text-gray-800" id="pageTitle">
                    <?php echo isset($page_title) ? h($page_title) : 'Dashboard'; ?>
                </h1>
            </div>
            <div class="flex items-center">
                <div class="relative">
                    <button id="userDropdownBtn" class="flex items-center text-gray-600 focus:outline-none">
                        <i class="fas fa-user-circle text-2xl mr-2 text-emerald-800"></i>
                        <span class="font-medium mr-1"><?php echo h($_SESSION['username']); ?></span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <!-- Dropdown -->
                    <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden z-50">
                        <div class="px-4 py-2 border-b">
                            <p class="text-sm text-gray-500">Role: <span class="font-bold text-emerald-800"><?php echo h($_SESSION['role']); ?></span></p>
                        </div>
                        <a href="<?php echo base_url('logout.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-red-600">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Viewport -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <!-- Content goes here -->
