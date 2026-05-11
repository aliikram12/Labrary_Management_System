<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotStudent();

$transactions = getStudentTransactions($pdo, $_SESSION['user_id']);
$fines = getStudentFines($pdo, $_SESSION['user_id']);
$currency = getSetting($pdo, 'currency_symbol', 'Rs.');
$activeBooks = array_filter($transactions, fn($t) => $t['status'] === 'issued');
$returnedBooks = array_filter($transactions, fn($t) => $t['status'] === 'returned');
?>

<div class="container-fluid fade-in">
    <div class="stats-grid mb-4" data-aos="fade-up">
        <div class="stat-card">
            <div class="icon primary"><i class="fas fa-book-open"></i></div>
            <div class="info"><h3><?php echo count($activeBooks); ?></h3><p>Currently Borrowed</p></div>
        </div>
        <div class="stat-card">
            <div class="icon success"><i class="fas fa-check-circle"></i></div>
            <div class="info"><h3><?php echo count($returnedBooks); ?></h3><p>Returned</p></div>
        </div>
        <div class="stat-card">
            <div class="icon danger"><i class="fas fa-coins"></i></div>
            <div class="info"><h3><?php echo $currency; ?> <?php echo number_format(array_sum(array_map(fn($f) => $f['status'] === 'pending' ? $f['amount'] : 0, $fines))); ?></h3><p>Pending Fines</p></div>
        </div>
    </div>

    <!-- Active Books -->
    <?php if (!empty($activeBooks)): ?>
    <div class="card mb-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header">
            <h5 class="mb-0 fw-bold"><i class="fas fa-book-open me-2 text-oxford"></i>Currently Borrowed</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Book</th><th>Author</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($activeBooks as $b): 
                            $isOverdue = strtotime($b['due_date']) < time();
                            $daysLeft = floor((strtotime($b['due_date']) - time()) / 86400);
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($b['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($b['author']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($b['issue_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($b['due_date'])); ?></td>
                            <td>
                                <?php if ($isOverdue): ?>
                                    <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Overdue <?php echo abs($daysLeft); ?>d</span>
                                <?php elseif ($daysLeft <= 3): ?>
                                    <span class="badge bg-warning"><?php echo $daysLeft; ?> days left</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?php echo $daysLeft; ?> days left</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Borrowing History -->
    <div class="card mb-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-gold"></i>Borrowing History</h5></div>
        <div class="card-body">
            <?php if (!empty($transactions)): ?>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead><tr><th>Book</th><th>Author</th><th>Issued</th><th>Due</th><th>Returned</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($t['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($t['author']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($t['issue_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($t['due_date'])); ?></td>
                            <td><?php echo $t['return_date'] ? date('M d, Y', strtotime($t['return_date'])) : '—'; ?></td>
                            <td><span class="badge bg-<?php echo $t['status'] === 'returned' ? 'success' : ($t['status'] === 'overdue' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($t['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted"><i class="fas fa-book fa-2x mb-2 d-block"></i>No borrowing history</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Fines -->
    <?php if (!empty($fines)): ?>
    <div class="card" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-coins me-2 text-danger"></i>Fine History</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead><tr><th>Book</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($fines as $f): ?>
                        <tr>
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
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
