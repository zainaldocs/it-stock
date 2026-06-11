<?php
// layouts/footer.php
?>
        </main> <!-- End Main Viewport -->
        
        <footer class="bg-white border-t py-4 px-6 text-center text-sm text-gray-500">
            &copy; <?php echo date('Y'); ?> IT Inventory Stock - Corporate Emerald Edition
        </footer>
    </div> <!-- End Main Content -->

    <script>
        // Toggle Sidebar Mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });

        // Toggle User Dropdown
        const userDropdownBtn = document.getElementById('userDropdownBtn');
        const userDropdown = document.getElementById('userDropdown');

        userDropdownBtn.addEventListener('click', () => {
            userDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            if (!userDropdownBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });

        // Display SweetAlert message if session contains 'success' or 'error'
        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?php echo addslashes($_SESSION['success']); ?>',
                confirmButtonColor: '#064e3b'
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '<?php echo addslashes($_SESSION['error']); ?>',
                confirmButtonColor: '#064e3b'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
