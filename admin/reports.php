<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

$stats = getSystemStats($pdo);
$transactions = getAllTransactions($pdo);
$books = getAllBooks($pdo);
$users = getAllUsers($pdo, 'student');
$fines = getAllFines($pdo);
$monthlyData = getMonthlyChartData($pdo);
$currency = getSetting($pdo, 'currency_symbol', 'Rs.');
?>

<div class="container-fluid fade-in">
    <ul class="nav nav-tabs mb-4" role="tablist" data-aos="fade-up">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview">System Overview</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#borrowing">Borrowing History</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#inventory">Inventory</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#finesTab">Fines</button></li>
    </ul>
    
    <div class="tab-content">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview">
            <div class="stats-grid mb-4" data-aos="fade-up">
                <div class="stat-card"><div class="icon primary"><i class="fas fa-book"></i></div><div class="info"><h3><?php echo $stats['total_books']; ?></h3><p>Books</p></div></div>
                <div class="stat-card"><div class="icon success"><i class="fas fa-users"></i></div><div class="info"><h3><?php echo $stats['total_students']; ?></h3><p>Students</p></div></div>
                <div class="stat-card"><div class="icon warning"><i class="fas fa-book-reader"></i></div><div class="info"><h3><?php echo $stats['total_issued']; ?></h3><p>Issued</p></div></div>
                <div class="stat-card"><div class="icon danger"><i class="fas fa-coins"></i></div><div class="info"><h3><?php echo $currency; ?> <?php echo number_format($stats['pending_fines']); ?></h3><p>Pending Fines</p></div></div>
            </div>
            <div class="card" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-oxford"></i>Monthly Trends</h5></div>
                <div class="card-body"><div class="chart-container"><canvas id="reportChart"></canvas></div></div>
            </div>
        </div>

        <!-- Borrowing History -->
        <div class="tab-pane fade" id="borrowing">
            <div class="card" data-aos="fade-up">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Borrowing History</h5>
                    <div class="export-group">
                        <button class="btn-export btn-export-csv" onclick="exportTable('borrowTable','borrowing_history')"><i class="fas fa-file-csv"></i>CSV</button>
                        <button class="btn-export btn-export-print" onclick="window.print()"><i class="fas fa-print"></i>Print</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table" id="borrowTable">
                            <thead><tr><th>Student</th><th>Roll No</th><th>Book</th><th>Issue</th><th>Due</th><th>Return</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($t['student_name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($t['role_number']); ?></code></td>
                                    <td><?php echo htmlspecialchars($t['title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($t['issue_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($t['due_date'])); ?></td>
                                    <td><?php echo $t['return_date'] ? date('M d, Y', strtotime($t['return_date'])) : '—'; ?></td>
                                    <td><span class="badge bg-<?php echo $t['status'] === 'returned' ? 'success' : ($t['status'] === 'overdue' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($t['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory -->
        <div class="tab-pane fade" id="inventory">
            <div class="card" data-aos="fade-up">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-warehouse me-2"></i>Inventory Status</h5>
                    <div class="export-group">
                        <button class="btn-export btn-export-csv" onclick="exportTable('inventoryTable','inventory_report')"><i class="fas fa-file-csv"></i>CSV</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table" id="inventoryTable">
                            <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Total</th><th>Available</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><?php echo htmlspecialchars($book['subject'] ?? 'N/A'); ?></td>
                                    <td><?php echo $book['total_copies']; ?></td>
                                    <td><span class="fw-bold text-<?php echo $book['available_copies'] > 0 ? 'success' : 'danger'; ?>"><?php echo $book['available_copies']; ?></span></td>
                                    <td><span class="badge bg-<?php echo $book['status'] === 'available' ? 'success' : ($book['status'] === 'reserved' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($book['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fines Tab -->
        <div class="tab-pane fade" id="finesTab">
            <div class="card" data-aos="fade-up">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-coins me-2 text-gold"></i>Fine Report</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table">
                            <thead><tr><th>Student</th><th>Book</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($fines as $f): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($f['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($f['title']); ?></td>
                                    <td><span class="fine-amount"><?php echo $currency; ?> <?php echo number_format($f['amount'], 2); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($f['fine_date'])); ?></td>
                                    <td><span class="badge bg-<?php echo $f['status'] === 'paid' ? 'success' : ($f['status'] === 'waived' ? 'secondary' : 'danger'); ?>"><?php echo ucfirst($f['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('reportChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($monthlyData['labels']); ?>,
                datasets: [{
                    label: 'Issues', data: <?php echo json_encode($monthlyData['issues']); ?>,
                    backgroundColor: 'rgba(30,58,95,.8)', borderRadius: 6
                }, {
                    label: 'Returns', data: <?php echo json_encode($monthlyData['returns']); ?>,
                    backgroundColor: 'rgba(196,163,90,.7)', borderRadius: 6
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
        });
    }
});

function exportTable(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    let csv = [];
    const rows = table.querySelectorAll('tr');
    rows.forEach(row => {
        let cols = [];
        row.querySelectorAll('td, th').forEach(col => cols.push('"' + col.innerText.replace(/"/g, '""') + '"'));
        csv.push(cols.join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename + '_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}
</script>

<?php require_once '../includes/footer.php'; ?>
