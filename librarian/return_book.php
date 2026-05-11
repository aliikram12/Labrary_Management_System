<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotLibrarian();

$message = '';
$error = '';
$fineInfo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_id'])) {
    $result = returnBook($pdo, (int)$_POST['transaction_id'], $_SESSION['user_id']);
    if ($result['success']) { 
        $message = $result['message'];
        if ($result['fine'] > 0) $fineInfo = $result['fine'];
    } else { 
        $error = $result['message']; 
    }
}

$issuedBooks = getIssuedBooks($pdo);
$currency = getSetting($pdo, 'currency_symbol', 'Rs.');
?>

<div class="container-fluid fade-in">
    <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
            <?php if ($fineInfo): ?><br><strong>Fine generated: <?php echo $currency; ?> <?php echo $fineInfo; ?></strong><?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card" data-aos="fade-up">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-undo-alt me-2 text-oxford"></i>Return Books</h5>
            <span class="badge bg-warning"><?php echo count($issuedBooks); ?> issued</span>
        </div>
        <div class="card-body">
            <?php if (!empty($issuedBooks)): ?>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead><tr><th>Student</th><th>Book</th><th>Issue Date</th><th>Due Date</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($issuedBooks as $t): 
                            $isOverdue = strtotime($t['due_date']) < time();
                            $daysOver = $isOverdue ? floor((time() - strtotime($t['due_date'])) / 86400) : 0;
                            $finePerDay = (float)getSetting($pdo, 'fine_per_day', 10);
                            $estFine = $daysOver * $finePerDay;
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="table-avatar"><?php echo getUserInitial($t['student_name']); ?></div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($t['student_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($t['role_number']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($t['title']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($t['issue_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($t['due_date'])); ?></td>
                            <td>
                                <?php if ($isOverdue): ?>
                                    <span class="badge bg-danger"><?php echo $daysOver; ?>d overdue</span>
                                    <br><small class="fine-amount"><?php echo $currency; ?> <?php echo number_format($estFine); ?> fine</small>
                                <?php else: ?>
                                    <span class="badge bg-success">On time</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="transaction_id" value="<?php echo $t['id']; ?>">
                                    <button class="btn btn-sm btn-oxford" onclick="return confirm('Process return?<?php echo $isOverdue ? ' Fine will be applied.' : ''; ?>')">
                                        <i class="fas fa-undo me-1"></i>Return
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-check-circle fa-3x mb-3 text-success d-block"></i><h5>No books currently issued</h5></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
