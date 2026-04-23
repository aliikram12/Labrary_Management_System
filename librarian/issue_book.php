<?php
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotLibrarian();

$message = '';
$error = '';

if (isset($_GET['id'])) {
    $reservationId = (int)$_GET['id'];
    $result = issueBook($pdo, $reservationId, $_SESSION['user_id']);
    
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
}

$pendingReservations = getPendingReservations($pdo);
?>

<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-0"><i class="fas fa-book-open me-2"></i>Issue Book</h2>
        </div>
    </div>
    
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
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 fw-bold">Pending Reservations</h5>
        </div>
        <div class="card-body">
            <?php if (empty($pendingReservations)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-muted">No pending reservations</h5>
                    <p class="text-muted">All reservations have been processed.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Role Number</th>
                                <th>Book</th>
                                <th>Author</th>
                                <th>Reserved Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingReservations as $res): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($res['student_name']); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($res['role_number']); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($res['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($res['author']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($res['reserved_at'])); ?></td>
                                    <td>
                                        <a href="issue_book.php?id=<?php echo $res['id']; ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('Issue this book to <?php echo htmlspecialchars($res['student_name']); ?>?')">
                                            <i class="fas fa-check me-1"></i> Issue Book
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Issue by Role Number -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0 fw-bold"><i class="fas fa-search me-2"></i>Quick Issue by Role Number</h5>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="role_number" placeholder="Enter Student Role Number">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Find Reservations
                    </button>
                </div>
            </form>
            
            <?php if (isset($_GET['role_number'])): 
                $roleNumber = sanitize($_GET['role_number']);
                $stmt = $pdo->prepare("
                    SELECT r.*, b.title, b.author, u.name as student_name, u.role_number 
                    FROM reservations r 
                    JOIN books b ON r.book_id = b.id 
                    JOIN users u ON r.student_id = u.id 
                    WHERE u.role_number = ? AND r.status = 'pending'
                ");
                $stmt->execute([$roleNumber]);
                $reservations = $stmt->fetchAll();
            ?>
                <div class="mt-4">
                    <h6>Reservations for Role Number: <strong><?php echo htmlspecialchars($roleNumber); ?></strong></h6>
                    <?php if (empty($reservations)): ?>
                        <p class="text-muted">No pending reservations found for this role number.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>Reserved Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $res): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($res['title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($res['author']); ?></small>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($res['reserved_at'])); ?></td>
                                            <td>
                                                <a href="issue_book.php?id=<?php echo $res['id']; ?>" class="btn btn-sm btn-success">Issue</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
