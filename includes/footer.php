<?php 
$isDashboardPage = false;
if (isset($_SESSION['user_id'])) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $dashboardPages = ['dashboard.php','search.php','my_books.php','users.php','reports.php',
        'books.php','issue_book.php','return_book.php','reservations.php','logs.php',
        'settings.php','profile.php','cards.php','fines.php','card.php','requests.php',
        'notifications.php','scan.php'];
    $isDashboardPage = in_array($currentPage, $dashboardPages);
}
?>

<?php if ($isDashboardPage): ?>
            </div><!-- /.app-content -->
        </main><!-- /.app-main -->
    </div><!-- /.app-wrapper -->
<?php else: ?>
    </main>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <i class="fas fa-university"></i>
                        <span>AliStack Digital Library</span>
                    </div>
                    <p class="footer-tagline">Oxford-Style Academic Excellence in Library Management</p>
                </div>
                <div class="footer-links">
                    <a href="index.php">Home</a>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                </div>
                <div class="footer-copy">
                    <p>&copy; <?php echo date('Y'); ?> AliStack Digital Library. All rights reserved.</p>
                    <p class="footer-dev">Developed with <i class="fas fa-heart text-danger"></i> by Ali Stack</p>
                </div>
            </div>
        </div>
    </footer>
<?php endif; ?>

<!-- Core Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Enhanced Libraries -->
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
// Initialize AOS
AOS.init({ duration: 600, once: true, offset: 50 });

// Toastr config
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 4000,
    showEasing: "swing",
    hideEasing: "linear"
};

// Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) {
        sidebar.classList.toggle('active');
        if (overlay) overlay.classList.toggle('active');
        document.body.classList.toggle('sidebar-open');
    }
}

// Close sidebar on mobile when clicking outside
document.addEventListener('click', function(e) {
    if (window.innerWidth <= 992) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar && toggle && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    }
});

// Initialize DataTables
$(document).ready(function() {
    $('.data-table').DataTable({
        responsive: true,
        pageLength: 15,
        language: {
            search: '<i class="fas fa-search"></i>',
            searchPlaceholder: 'Search...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_',
            paginate: { previous: '<i class="fas fa-chevron-left"></i>', next: '<i class="fas fa-chevron-right"></i>' }
        },
        dom: '<"dt-header"<"dt-search"f><"dt-length"l>>rt<"dt-footer"<"dt-info"i><"dt-pagination"p>>'
    });
});

// SweetAlert2 Delete Confirmation
function confirmDelete(formId, itemName) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Delete ' + (itemName || 'this item') + '? This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-trash me-1"></i> Delete',
        cancelButtonText: 'Cancel',
        customClass: { popup: 'swal-oxford' }
    }).then((result) => {
        if (result.isConfirmed) document.getElementById(formId).submit();
    });
    return false;
}

// Notification Polling (every 30 seconds)
<?php if ($isDashboardPage && isLoggedIn()): ?>
let lastNotifCount = <?php echo $unreadCount; ?>;
setInterval(function() {
    fetch('../api/notifications.php?action=count')
        .then(r => r.json())
        .then(data => {
            const badge = document.querySelector('.notif-badge');
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count > 9 ? '9+' : data.count;
                    badge.style.display = 'flex';
                } else {
                    const trigger = document.querySelector('.notification-trigger');
                    if (trigger) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'notif-badge';
                        newBadge.textContent = data.count > 9 ? '9+' : data.count;
                        trigger.appendChild(newBadge);
                    }
                }
                if (data.count > lastNotifCount) {
                    toastr.info('You have new notifications', 'Notification');
                }
                lastNotifCount = data.count;
            } else if (badge) {
                badge.style.display = 'none';
            }
        })
        .catch(() => {});
}, 30000);

function markAllRead() {
    fetch('../api/notifications.php?action=mark_all_read')
        .then(r => r.json())
        .then(data => {
            document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
            const badge = document.querySelector('.notif-badge');
            if (badge) badge.style.display = 'none';
            toastr.success('All notifications marked as read');
        });
}
<?php endif; ?>

// Scroll-based navbar styling for public pages
<?php if (!$isDashboardPage): ?>
window.addEventListener('scroll', function() {
    const nav = document.getElementById('mainNav');
    if (nav) {
        if (window.scrollY > 50) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    }
});
<?php endif; ?>
</script>

<script src="../assets/js/main.js"></script>
</body>
</html>
