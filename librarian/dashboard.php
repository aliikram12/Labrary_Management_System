<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotLibrarian();

$stats = getSystemStats($pdo);
$pendingReservations = getPendingReservations($pdo);
$issuedBooks = getIssuedBooks($pdo);
$overdueBooks = array_filter($issuedBooks, function($t) { return strtotime($t['due_date']) < time(); });
$recentLogs = getSystemLogs($pdo, 6);
$monthlyData = getMonthlyChartData($pdo);
?>

<div class="container-fluid fade-in">
    <div class="stats-grid" data-aos="fade-up">
        <div class="stat-card">
            <div class="icon primary"><i class="fas fa-book"></i></div>
            <div class="info"><h3><?php echo number_format($stats['total_books']); ?></h3><p>Total Books</p></div>
        </div>
        <div class="stat-card">
            <div class="icon warning"><i class="fas fa-book-reader"></i></div>
            <div class="info"><h3><?php echo number_format($stats['total_issued']); ?></h3><p>Currently Issued</p></div>
        </div>
        <div class="stat-card">
            <div class="icon info"><i class="fas fa-calendar-check"></i></div>
            <div class="info"><h3><?php echo count($pendingReservations); ?></h3><p>Pending Reservations</p></div>
        </div>
        <div class="stat-card">
            <div class="icon danger"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="info"><h3><?php echo count($overdueBooks); ?></h3><p>Overdue Books</p></div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Activity Chart -->
        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
            <div class="card card-blue">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-oxford"></i>Monthly Activity</h5></div>
                <div class="card-body"><div class="chart-container"><canvas id="libMonthlyChart"></canvas></div></div>
            </div>
        </div>
        <!-- Quick Actions -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6"><a href="issue_book.php" class="quick-action"><i class="fas fa-book-open"></i><span>Issue Book</span></a></div>
                        <div class="col-6"><a href="return_book.php" class="quick-action"><i class="fas fa-undo-alt"></i><span>Return Book</span></a></div>
                        <div class="col-6"><a href="reservations.php" class="quick-action"><i class="fas fa-calendar-check"></i><span>Reservations</span></a></div>
                        <div class="col-6"><a href="scan.php" class="quick-action"><i class="fas fa-qrcode"></i><span>Scan Card</span></a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Pending Reservations -->
        <div class="col-lg-6" data-aos="fade-up">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-clock me-2 text-warning"></i>Pending Reservations</h5>
                    <a href="reservations.php" class="btn btn-sm btn-oxford-outline">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($pendingReservations)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Student</th><th>Book</th><th>Date</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach (array_slice($pendingReservations, 0, 5) as $r): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($r['student_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($r['title']); ?></td>
                                    <td><small><?php echo timeAgo($r['reserved_at']); ?></small></td>
                                    <td><a href="issue_book.php" class="btn btn-sm btn-success"><i class="fas fa-check"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted"><i class="fas fa-check-circle fa-2x mb-2 text-success d-block"></i>No pending reservations</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Overdue Books -->
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Overdue Books</h5>
                    <span class="badge bg-danger"><?php echo count($overdueBooks); ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($overdueBooks)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Student</th><th>Book</th><th>Days Overdue</th></tr></thead>
                            <tbody>
                                <?php foreach (array_slice(array_values($overdueBooks), 0, 5) as $ob): 
                                    $daysOver = floor((time() - strtotime($ob['due_date'])) / 86400);
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($ob['student_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($ob['title']); ?></td>
                                    <td><span class="badge bg-danger"><?php echo $daysOver; ?> days</span></td>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('libMonthlyChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthlyData['labels']); ?>,
                datasets: [{
                    label: 'Issues', data: <?php echo json_encode($monthlyData['issues']); ?>,
                    borderColor: '#1e3a5f', backgroundColor: 'rgba(30,58,95,.1)',
                    fill: true, tension: .4, pointRadius: 5, pointBackgroundColor: '#1e3a5f'
                }, {
                    label: 'Returns', data: <?php echo json_encode($monthlyData['returns']); ?>,
                    borderColor: '#c4a35a', backgroundColor: 'rgba(196,163,90,.1)',
                    fill: true, tension: .4, pointRadius: 5, pointBackgroundColor: '#c4a35a'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { family: 'Inter' } } } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } }, x: { grid: { display: false } } }
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
