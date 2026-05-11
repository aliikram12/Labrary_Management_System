<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotStudent();

$user = getCurrentUser($pdo);
$transactions = getStudentTransactions($pdo, $_SESSION['user_id']);
$reservations = getStudentReservations($pdo, $_SESSION['user_id']);
$fines = getStudentFines($pdo, $_SESSION['user_id']);

$activeBooks = array_filter($transactions, fn($t) => $t['status'] === 'issued');
$overdueBooks = array_filter($activeBooks, fn($t) => strtotime($t['due_date']) < time());
$pendingReservations = array_filter($reservations, fn($r) => $r['status'] === 'pending');
$pendingFines = array_filter($fines, fn($f) => $f['status'] === 'pending');
$totalPendingFine = array_sum(array_map(fn($f) => $f['amount'], $pendingFines));
$currency = getSetting($pdo, 'currency_symbol', 'Rs.');
$maxBooks = (int)getSetting($pdo, 'max_books_per_student', 5);
?>

<div class="container-fluid fade-in">
    <!-- Stats -->
    <div class="stats-grid" data-aos="fade-up">
        <div class="stat-card">
            <div class="icon primary"><i class="fas fa-book-open"></i></div>
            <div class="info"><h3><?php echo count($activeBooks); ?> / <?php echo $maxBooks; ?></h3><p>Active Borrows</p></div>
        </div>
        <div class="stat-card">
            <div class="icon warning"><i class="fas fa-calendar-check"></i></div>
            <div class="info"><h3><?php echo count($pendingReservations); ?></h3><p>Pending Reservations</p></div>
        </div>
        <div class="stat-card">
            <div class="icon danger"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="info"><h3><?php echo count($overdueBooks); ?></h3><p>Overdue</p></div>
        </div>
        <div class="stat-card">
            <div class="icon gold"><i class="fas fa-coins"></i></div>
            <div class="info"><h3><?php echo $currency; ?> <?php echo number_format($totalPendingFine); ?></h3><p>Pending Fines</p></div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Current Books -->
        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
            <div class="card card-blue">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-book-open me-2 text-oxford"></i>Currently Borrowed Books</h5>
                    <a href="my_books.php" class="btn btn-sm btn-oxford-outline">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($activeBooks)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Book</th><th>Author</th><th>Due Date</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($activeBooks as $book): 
                                    $isOverdue = strtotime($book['due_date']) < time();
                                    $daysLeft = floor((strtotime($book['due_date']) - time()) / 86400);
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($book['due_date'])); ?></td>
                                    <td>
                                        <?php if ($isOverdue): ?>
                                            <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Overdue</span>
                                        <?php elseif ($daysLeft <= 3): ?>
                                            <span class="badge bg-warning"><i class="fas fa-clock me-1"></i><?php echo $daysLeft; ?> days left</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i><?php echo $daysLeft; ?> days left</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-book fa-3x mb-3 d-block"></i>
                            <h6>No books currently borrowed</h6>
                            <a href="search.php" class="btn btn-oxford btn-sm mt-2"><i class="fas fa-search me-1"></i>Search Books</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions + Card Preview -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6"><a href="search.php" class="quick-action"><i class="fas fa-search"></i><span>Search</span></a></div>
                        <div class="col-6"><a href="my_books.php" class="quick-action"><i class="fas fa-book"></i><span>My Books</span></a></div>
                        <div class="col-6"><a href="card.php" class="quick-action"><i class="fas fa-id-card"></i><span>My Card</span></a></div>
                        <div class="col-6"><a href="requests.php" class="quick-action"><i class="fas fa-paper-plane"></i><span>Request</span></a></div>
                    </div>
                </div>
            </div>

            <!-- Mini Card Preview -->
            <?php if (!empty($user['card_id'])): ?>
            <div class="library-card-preview" style="width:100%;">
                <div class="card-lib-header">
                    <div class="card-lib-logo"><i class="fas fa-university"></i><span>AliStack</span></div>
                </div>
                <div class="card-lib-body">
                    <div class="card-lib-photo"><span class="initial"><?php echo getUserInitial($user['name']); ?></span></div>
                    <div class="card-lib-info">
                        <span class="card-name"><?php echo htmlspecialchars($user['name']); ?></span>
                        <span class="card-dept"><?php echo htmlspecialchars($user['department'] ?? 'Student'); ?></span>
                        <span class="card-number"><?php echo htmlspecialchars($user['role_number']); ?></span>
                    </div>
                </div>
                <div class="card-lib-footer">
                    <span class="card-id"><?php echo htmlspecialchars($user['card_id']); ?></span>
                    <span class="card-expiry">EXP: <?php echo $user['card_expiry'] ? date('m/Y', strtotime($user['card_expiry'])) : 'N/A'; ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reservations -->
    <?php if (!empty($pendingReservations)): ?>
    <div class="card" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-check me-2 text-warning"></i>Pending Reservations</h5>
            <span class="badge bg-warning"><?php echo count($pendingReservations); ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Book</th><th>Author</th><th>Reserved</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($pendingReservations as $r): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($r['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($r['author']); ?></td>
                            <td><?php echo timeAgo($r['reserved_at']); ?></td>
                            <td>
                                <form method="POST" action="cancel_reservation.php" class="d-inline">
                                    <input type="hidden" name="reservation_id" value="<?php echo $r['id']; ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times me-1"></i>Cancel</button>
                                </form>
                            </td>
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
