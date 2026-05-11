<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotLibrarian();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'pay') {
        payFine($pdo, (int)$_POST['fine_id'], $_SESSION['user_id']);
        $message = 'Fine collected successfully';
    }
}
$fines = getAllFines($pdo);
$pendingFines = array_filter($fines, fn($f) => $f['status'] === 'pending');
$currency = getSetting($pdo, 'currency_symbol', 'Rs.');
?>

<div class="container-fluid fade-in">
    <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="stats-grid mb-4" data-aos="fade-up">
        <div class="stat-card">
            <div class="icon danger"><i class="fas fa-exclamation-circle"></i></div>
            <div class="info"><h3><?php echo count($pendingFines); ?></h3><p>Pending Fines</p></div>
        </div>
        <div class="stat-card">
            <div class="icon success"><i class="fas fa-check-circle"></i></div>
            <div class="info"><h3><?php echo count($fines) - count($pendingFines); ?></h3><p>Collected/Waived</p></div>
        </div>
    </div>

    <div class="card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-coins me-2 text-gold"></i>Fine Collection</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead><tr><th>Student</th><th>Book</th><th>Amount</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($fines as $f): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($f['student_name']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($f['role_number']); ?></small></td>
                            <td><?php echo htmlspecialchars($f['title']); ?></td>
                            <td><span class="fine-amount"><?php echo $currency; ?> <?php echo number_format($f['amount'], 2); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($f['fine_date'])); ?></td>
                            <td><span class="badge bg-<?php echo $f['status'] === 'paid' ? 'success' : ($f['status'] === 'waived' ? 'secondary' : 'danger'); ?>"><?php echo ucfirst($f['status']); ?></span></td>
                            <td>
                                <?php if ($f['status'] === 'pending'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="fine_id" value="<?php echo $f['id']; ?>">
                                    <button name="action" value="pay" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Collect</button>
                                </form>
                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
