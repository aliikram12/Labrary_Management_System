<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotLibrarian();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $result = issueBook($pdo, (int)$_POST['reservation_id'], $_SESSION['user_id']);
    if ($result['success']) { $message = $result['message']; } else { $error = $result['message']; }
}

$pendingReservations = getPendingReservations($pdo);
?>

<div class="container-fluid fade-in">
    <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card" data-aos="fade-up">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-book-open me-2 text-oxford"></i>Issue Books from Reservations</h5>
            <span class="badge bg-warning"><?php echo count($pendingReservations); ?> pending</span>
        </div>
        <div class="card-body">
            <?php if (!empty($pendingReservations)): ?>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead><tr><th>Student</th><th>Roll No</th><th>Book</th><th>Reserved</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($pendingReservations as $r): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="table-avatar"><?php echo getUserInitial($r['student_name']); ?></div>
                                    <strong><?php echo htmlspecialchars($r['student_name']); ?></strong>
                                </div>
                            </td>
                            <td><code><?php echo htmlspecialchars($r['role_number']); ?></code></td>
                            <td><?php echo htmlspecialchars($r['title']); ?><br><small class="text-muted"><?php echo htmlspecialchars($r['author']); ?></small></td>
                            <td><?php echo timeAgo($r['reserved_at']); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="reservation_id" value="<?php echo $r['id']; ?>">
                                    <button class="btn btn-sm btn-success" onclick="return confirm('Issue this book?')">
                                        <i class="fas fa-check me-1"></i>Issue
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-check-circle fa-3x mb-3 text-success d-block"></i><h5>No pending reservations</h5></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
