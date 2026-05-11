<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotLibrarian();
$reservations = getAllReservations($pdo);
?>

<div class="container-fluid fade-in">
    <div class="card" data-aos="fade-up">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-check me-2 text-gold"></i>All Reservations</h5>
            <span class="badge bg-light text-dark"><?php echo count($reservations); ?> total</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead><tr><th>Student</th><th>Book</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="table-avatar"><?php echo getUserInitial($r['student_name']); ?></div>
                                    <div><strong><?php echo htmlspecialchars($r['student_name']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($r['role_number']); ?></small></div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($r['title']); ?><br><small class="text-muted"><?php echo htmlspecialchars($r['author']); ?></small></td>
                            <td><?php echo date('M d, Y', strtotime($r['reserved_at'])); ?><br><small class="text-muted"><?php echo timeAgo($r['reserved_at']); ?></small></td>
                            <td><span class="badge bg-<?php echo $r['status'] === 'pending' ? 'warning' : ($r['status'] === 'completed' ? 'success' : 'secondary'); ?>"><?php echo ucfirst($r['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
