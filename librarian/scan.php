<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotLibrarian();

$student = null;
$studentBooks = [];
$studentFines = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_GET['card'])) {
    $cardId = sanitize($_POST['card_id'] ?? $_GET['card'] ?? '');
    if ($cardId) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE card_id = ? AND role = 'student'");
        $stmt->execute([$cardId]);
        $student = $stmt->fetch();
        if ($student) {
            $studentBooks = getStudentTransactions($pdo, $student['id']);
            $studentFines = getStudentFines($pdo, $student['id']);
        }
    }
}
?>

<div class="container-fluid fade-in">
    <div class="row g-4">
        <div class="col-lg-6" data-aos="fade-up">
            <div class="card">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-qrcode me-2 text-gold"></i>Scan Student Card</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="scan-input-area mb-3">
                            <i class="fas fa-id-card-alt d-block"></i>
                            <p class="text-muted mb-3">Enter or scan the student's library card ID</p>
                            <input type="text" class="form-control form-control-lg text-center" name="card_id" 
                                   placeholder="e.g. LIB-2026-00001" autofocus required
                                   value="<?php echo htmlspecialchars($_POST['card_id'] ?? $_GET['card'] ?? ''); ?>"
                                   style="font-family:monospace;font-size:1.2rem;letter-spacing:2px;max-width:400px;margin:0 auto">
                        </div>
                        <button type="submit" class="btn btn-oxford btn-lg w-100"><i class="fas fa-search me-2"></i>Look Up Student</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <?php if ($student): ?>
            <div class="card scan-result">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="table-avatar" style="width:60px;height:60px;font-size:1.5rem">
                            <?php echo getUserInitial($student['name']); ?>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($student['name']); ?></h4>
                            <span class="text-muted"><?php echo htmlspecialchars($student['role_number']); ?></span>
                            <span class="badge bg-<?php echo ($student['card_status'] ?? 'active') === 'active' ? 'success' : 'danger'; ?> ms-2">
                                <?php echo ucfirst($student['card_status'] ?? 'active'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="student-info-grid">
                        <span class="label">Email</span><span class="value"><?php echo htmlspecialchars($student['email']); ?></span>
                        <span class="label">Department</span><span class="value"><?php echo htmlspecialchars($student['department'] ?? 'N/A'); ?></span>
                        <span class="label">Card ID</span><span class="value"><code><?php echo htmlspecialchars($student['card_id']); ?></code></span>
                        <span class="label">Card Expiry</span><span class="value"><?php echo $student['card_expiry'] ? date('M d, Y', strtotime($student['card_expiry'])) : 'N/A'; ?></span>
                        <span class="label">Active Borrows</span><span class="value"><?php echo count(array_filter($studentBooks, fn($b) => $b['status'] === 'issued')); ?></span>
                        <span class="label">Pending Fines</span><span class="value fine-amount"><?php echo getSetting($pdo, 'currency_symbol', 'Rs.'); ?> <?php echo number_format(array_sum(array_map(fn($f) => $f['status'] === 'pending' ? $f['amount'] : 0, $studentFines)), 2); ?></span>
                    </div>
                </div>
            </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="card scan-result error">
                <div class="card-body text-center py-5">
                    <i class="fas fa-user-slash fa-3x text-danger mb-3"></i>
                    <h5>Student Not Found</h5>
                    <p class="text-muted">No student found with that card ID.</p>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-id-card-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Scan a Card</h5>
                    <p class="text-muted">Enter a student's library card ID to view their information</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($student && !empty($studentBooks)): ?>
    <div class="card mt-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Borrowing History</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead><tr><th>Book</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($studentBooks as $b): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($b['title']); ?></strong></td>
                            <td><?php echo date('M d, Y', strtotime($b['issue_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($b['due_date'])); ?></td>
                            <td><?php echo $b['return_date'] ? date('M d, Y', strtotime($b['return_date'])) : '—'; ?></td>
                            <td><span class="badge bg-<?php echo $b['status'] === 'returned' ? 'success' : ($b['status'] === 'issued' && strtotime($b['due_date']) < time() ? 'danger' : 'warning'); ?>"><?php echo ucfirst($b['status']); ?></span></td>
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
