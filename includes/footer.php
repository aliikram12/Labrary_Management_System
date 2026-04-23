<?php 
$isDashboardPage = false;
if (isset($_SESSION['user_id'])) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $dashboardPages = ['dashboard.php', 'search.php', 'my_books.php', 'users.php', 'reports.php', 
        'books.php', 'issue_book.php', 'return_book.php', 'reservations.php', 'logs.php'];
    $isDashboardPage = in_array($currentPage, $dashboardPages);
}
?>

<?php if ($isDashboardPage): ?>
            </div>
        </main>
    </div>
<?php else: ?>
    </main>
<?php endif; ?>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; 2026 AliStack Library Management System. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0">Developed by Ali Stack</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="../assets/js/main.js"></script>
<script>
// Toggle Sidebar Function
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

// Initialize toggle when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.getElementById('sidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        var sidebar = document.getElementById('sidebar');
        var toggle = document.getElementById('sidebarToggle');
        if (window.innerWidth <= 768 && sidebar && toggle) {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
});
</script>
</body>
</html>
