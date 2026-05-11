<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotAdmin();

$stats = getSystemStats($pdo);
$monthlyData = getMonthlyChartData($pdo);
$popularBooks = getPopularBooks($pdo, 5);
$categoryDist = getCategoryDistribution($pdo);
$recentLogs = getSystemLogs($pdo, 8);
$overdueBooks = getIssuedBooks($pdo);
$overdueBooks = array_filter($overdueBooks, function($t) { return strtotime($t['due_date']) < time(); });
$overdueBooks = array_slice($overdueBooks, 0, 5);
?>

<div class="container-fluid fade-in">
    <!-- Stats Cards -->
    <div class="stats-grid" data-aos="fade-up">
        <div class="stat-card">
            <div class="icon primary"><i class="fas fa-book"></i></div>
            <div class="info">
                <h3><?php echo number_format($stats['total_books']); ?></h3>
                <p>Total Books</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon success"><i class="fas fa-user-graduate"></i></div>
            <div class="info">
                <h3><?php echo number_format($stats['total_students']); ?></h3>
                <p>Students</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon warning"><i class="fas fa-book-reader"></i></div>
            <div class="info">
                <h3><?php echo number_format($stats['total_issued']); ?></h3>
                <p>Issued Books</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon danger"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="info">
                <h3><?php echo number_format($stats['total_overdue']); ?></h3>
                <p>Overdue</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon info"><i class="fas fa-calendar-check"></i></div>
            <div class="info">
                <h3><?php echo number_format($stats['total_reservations']); ?></h3>
                <p>Pending Reservations</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon gold"><i class="fas fa-coins"></i></div>
            <div class="info">
                <h3><?php echo getSetting($pdo, 'currency_symbol', 'Rs.'); ?> <?php echo number_format($stats['pending_fines']); ?></h3>
                <p>Pending Fines</p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
            <div class="card card-blue">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-oxford"></i>Monthly Activity</h5>
                    <span class="badge bg-light text-dark">Last 6 Months</span>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card card-gold">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2 text-gold"></i>Category Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container-sm">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions + Popular Books -->
    <div class="row g-4 mb-4">
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="users.php" class="quick-action">
                                <i class="fas fa-user-plus"></i>
                                <span>Add User</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="books.php" class="quick-action">
                                <i class="fas fa-plus-circle"></i>
                                <span>Add Book</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="cards.php" class="quick-action">
                                <i class="fas fa-id-card"></i>
                                <span>Cards</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="reports.php" class="quick-action">
                                <i class="fas fa-file-pdf"></i>
                                <span>Reports</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-fire me-2 text-danger"></i>Popular Books</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="info-list px-3">
                        <?php if (!empty($popularBooks)): ?>
                            <?php foreach ($popularBooks as $i => $book): ?>
                            <li>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark">#<?php echo $i + 1; ?></span>
                                    <span class="label"><?php echo htmlspecialchars($book['title']); ?></span>
                                </div>
                                <span class="value"><?php echo $book['borrow_count']; ?> borrows</span>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-center text-muted py-3">No borrow data yet</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Overdue Books</h5>
                    <span class="badge bg-danger"><?php echo count($overdueBooks); ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($overdueBooks)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <?php foreach ($overdueBooks as $ob): 
                                    $daysOver = floor((time() - strtotime($ob['due_date'])) / 86400);
                                ?>
                                <tr>
                                    <td>
                                        <strong style="font-size:.82rem"><?php echo htmlspecialchars($ob['student_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($ob['title']); ?></small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-danger"><?php echo $daysOver; ?> days</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted"><i class="fas fa-check-circle fa-2x mb-2 text-success d-block"></i>No overdue books!</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row g-4">
        <div class="col-lg-8" data-aos="fade-up">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Recent Activity</h5>
                    <a href="logs.php" class="btn btn-sm btn-oxford-outline">View All</a>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        <?php foreach ($recentLogs as $log): 
                            $cls = '';
                            if (strpos(strtolower($log['action']), 'login') !== false) $cls = 'success';
                            elseif (strpos(strtolower($log['action']), 'fail') !== false) $cls = 'danger';
                            elseif (strpos(strtolower($log['action']), 'reserv') !== false) $cls = 'warning';
                        ?>
                        <div class="timeline-item <?php echo $cls; ?>">
                            <div class="timeline-content">
                                <strong><?php echo htmlspecialchars($log['user_name'] ?? 'System'); ?></strong>
                                — <?php echo htmlspecialchars($log['action']); ?>
                            </div>
                            <div class="timeline-time"><?php echo timeAgo($log['timestamp']); ?></div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($recentLogs)): ?>
                            <p class="text-muted text-center py-3">No activity logs yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-info"></i>System Overview</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="info-list px-3">
                        <li><span class="label">Total Book Copies</span><span class="value"><?php echo number_format($stats['total_copies']); ?></span></li>
                        <li><span class="label">Available Copies</span><span class="value text-success"><?php echo number_format($stats['available_books']); ?></span></li>
                        <li><span class="label">Librarians</span><span class="value"><?php echo $stats['total_librarians']; ?></span></li>
                        <li><span class="label">Books Returned</span><span class="value"><?php echo number_format($stats['total_returned']); ?></span></li>
                        <li><span class="label">Fines Collected</span><span class="value text-success"><?php echo getSetting($pdo, 'currency_symbol', 'Rs.'); ?> <?php echo number_format($stats['collected_fines']); ?></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Activity Chart
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($monthlyData['labels']); ?>,
                datasets: [{
                    label: 'Books Issued',
                    data: <?php echo json_encode($monthlyData['issues']); ?>,
                    backgroundColor: 'rgba(30,58,95,.8)',
                    borderRadius: 6, barPercentage: 0.6
                }, {
                    label: 'Books Returned',
                    data: <?php echo json_encode($monthlyData['returns']); ?>,
                    backgroundColor: 'rgba(196,163,90,.7)',
                    borderRadius: 6, barPercentage: 0.6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { family: 'Inter' } } } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,.04)' } }, x: { grid: { display: false } } }
            }
        });
    }

    // Category Distribution Chart
    const catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        const catData = <?php echo json_encode($categoryDist); ?>;
        const colors = ['#1e3a5f','#c4a35a','#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: catData.map(c => c.category),
                datasets: [{ data: catData.map(c => c.count), backgroundColor: colors.slice(0, catData.length), borderWidth: 0 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 10, font: { size: 11, family: 'Inter' } } } }
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
