<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotLibrarian();

$notifications = getNotifications($pdo);
$pendingReservations = getPendingReservations($pdo);
$issuedBooks = getIssuedBooks($pdo);
$unreadCount = getUnreadNotificationCount($pdo);

$stats = getSystemStats($pdo);

$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="container-fluid px-4 py-4">
    <h2 class="fw-bold mb-4">Librarian Dashboard</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-success fade-in">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger fade-in">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon warning">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="info">
                <h3><?php echo count($pendingReservations); ?></h3>
                <p>Pending Reservations</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon primary">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="info">
                <h3><?php echo count($issuedBooks); ?></h3>
                <p>Books Issued</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="info">
                <h3><?php echo $stats['available_books']; ?></h3>
                <p>Available Books</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon info">
                <i class="fas fa-book"></i>
            </div>
            <div class="info">
                <h3><?php echo $stats['total_books']; ?></h3>
                <p>Total Books</p>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Pending Reservations -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-check me-2"></i>Pending Reservations</h5>
                    <span class="badge bg-warning"><?php echo count($pendingReservations); ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingReservations)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted mb-0">No pending reservations.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Book</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($pendingReservations, 10) as $res): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($res['student_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($res['role_number']); ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($res['title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($res['author']); ?></small>
                                            </td>
                                            <td><?php echo date('M d', strtotime($res['reserved_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-success issue-book" 
                                                        data-id="<?php echo $res['id']; ?>"
                                                        data-student="<?php echo htmlspecialchars($res['student_name']); ?>"
                                                        data-book="<?php echo htmlspecialchars($res['title']); ?>">
                                                    <i class="fas fa-check me-1"></i> Issue
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Overdue Books -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Overdue Books</h5>
                    <?php 
                    $overdueBooks = array_filter($issuedBooks, function($b) {
                        return strtotime($b['due_date']) < time();
                    });
                    ?>
                    <?php if (!empty($overdueBooks)): ?>
                        <span class="badge bg-danger"><?php echo count($overdueBooks); ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($overdueBooks)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted mb-0">No overdue books.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Book</th>
                                        <th>Due Date</th>
                                        <th>Days</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($overdueBooks, 10) as $book): 
                                        $daysOverdue = floor((time() - strtotime($book['due_date'])) / (60 * 60 * 24));
                                    ?>
                                        <tr class="table-danger">
                                            <td>
                                                <strong><?php echo htmlspecialchars($book['student_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($book['role_number']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($book['due_date'])); ?></td>
                                            <td><span class="badge bg-danger"><?php echo $daysOverdue; ?> days</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="issue_book.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-book-open fa-2x mb-2 d-block"></i>
                                Issue Book
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="return_book.php" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-undo fa-2x mb-2 d-block"></i>
                                Return Book
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="books.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-book fa-2x mb-2 d-block"></i>
                                Manage Books
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="reservations.php" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-calendar-check fa-2x mb-2 d-block"></i>
                                Reservations
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Issue Modal -->
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Issue Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Issue book to student?</p>
                <div class="alert alert-info">
                    <p class="mb-1"><strong>Student:</strong> <span id="issueStudent"></span></p>
                    <p class="mb-0"><strong>Book:</strong> <span id="issueBook"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmIssue">
                    <i class="fas fa-check me-1"></i> Issue Book
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let reservationId = null;
const issueModal = new bootstrap.Modal(document.getElementById('issueModal'));

document.querySelectorAll('.issue-book').forEach(btn => {
    btn.addEventListener('click', function() {
        reservationId = this.dataset.id;
        document.getElementById('issueStudent').textContent = this.dataset.student;
        document.getElementById('issueBook').textContent = this.dataset.book;
        issueModal.show();
    });
});

document.getElementById('confirmIssue').addEventListener('click', function() {
    if (reservationId) {
        window.location.href = 'issue_book.php?id=' + reservationId;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
