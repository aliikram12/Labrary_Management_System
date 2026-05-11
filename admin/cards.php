<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);
    if ($action === 'renew' && $userId) {
        renewCard($pdo, $userId);
        $message = 'Card renewed successfully';
    } elseif ($action === 'disable' && $userId) {
        disableCard($pdo, $userId);
        $message = 'Card disabled';
    } elseif ($action === 'regenerate' && $userId) {
        $newCard = generateCardId();
        $pdo->prepare("UPDATE users SET card_id = ?, card_status = 'active', card_expiry = DATE_ADD(CURDATE(), INTERVAL 1 YEAR) WHERE id = ?")->execute([$newCard, $userId]);
        $message = "New card generated: $newCard";
    }
}

$students = getAllUsers($pdo, 'student');
$activeCards = count(array_filter($students, fn($s) => ($s['card_status'] ?? '') === 'active'));
$expiredCards = count(array_filter($students, fn($s) => ($s['card_status'] ?? '') === 'expired' || (isset($s['card_expiry']) && strtotime($s['card_expiry']) < time())));
?>

<div class="container-fluid fade-in">
    <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="stats-grid mb-4" data-aos="fade-up">
        <div class="stat-card">
            <div class="icon success"><i class="fas fa-id-card"></i></div>
            <div class="info"><h3><?php echo $activeCards; ?></h3><p>Active Cards</p></div>
        </div>
        <div class="stat-card">
            <div class="icon danger"><i class="fas fa-id-card"></i></div>
            <div class="info"><h3><?php echo $expiredCards; ?></h3><p>Expired Cards</p></div>
        </div>
        <div class="stat-card">
            <div class="icon primary"><i class="fas fa-users"></i></div>
            <div class="info"><h3><?php echo count($students); ?></h3><p>Total Students</p></div>
        </div>
    </div>

    <div class="card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header">
            <h5 class="mb-0 fw-bold"><i class="fas fa-id-card me-2 text-oxford"></i>Student Library Cards</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr><th>Student</th><th>Card ID</th><th>Expiry</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): 
                            $expired = isset($s['card_expiry']) && strtotime($s['card_expiry']) < time();
                            $status = $s['card_status'] ?? 'active';
                            if ($expired) $status = 'expired';
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="table-avatar"><?php echo getUserInitial($s['name']); ?></div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($s['name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($s['role_number']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><code><?php echo htmlspecialchars($s['card_id'] ?? 'N/A'); ?></code></td>
                            <td><?php echo isset($s['card_expiry']) ? date('M d, Y', strtotime($s['card_expiry'])) : 'N/A'; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $status === 'active' ? 'success' : ($status === 'disabled' ? 'secondary' : 'danger'); ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $s['id']; ?>">
                                    <button name="action" value="renew" class="btn btn-sm btn-success" title="Renew"><i class="fas fa-redo"></i></button>
                                    <button name="action" value="disable" class="btn btn-sm btn-warning" title="Disable"><i class="fas fa-ban"></i></button>
                                    <button name="action" value="regenerate" class="btn btn-sm btn-primary" title="New Card"><i class="fas fa-plus"></i></button>
                                </form>
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
