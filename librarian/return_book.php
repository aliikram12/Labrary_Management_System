<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotLibrarian();

$message = '';
$error = '';

if (isset($_GET['id'])) {
    $transactionId = (int)$_GET['id'];
    $result = returnBook($pdo, $transactionId);
    
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
}

$issuedBooks = getIssuedBooks($pdo);
?>

<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-0"><i class="fas fa-undo me-2"></i>Return Book</h2>
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
    
    <!-- Currently Issued Books -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Currently Issued Books</h5>
            <span class="badge bg-primary"><?php echo count($issuedBooks); ?> books</span>
        </div>
        <div class="card-body">
            <?php if (empty($issuedBooks)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-muted">No books currently issued</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Role Number</th>
                                <th>Book</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($issuedBooks as $book): 
                                $isOverdue = strtotime($book['due_date']) < time();
                            ?>
                                <tr class="<?php echo $isOverdue ? 'table-danger' : ''; ?>">
                                    <td><strong><?php echo htmlspecialchars($book['student_name']); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($book['role_number']); ?></code></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($book['author']); ?></small>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($book['issue_date'])); ?></td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($book['due_date'])); ?>
                                        <?php if ($isOverdue): ?>
                                            <br><small class="text-danger">Overdue!</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $isOverdue ? 'danger' : 'warning'; ?>">
                                            <?php echo $isOverdue ? 'Overdue' : 'Issued'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="return_book.php?id=<?php echo $book['id']; ?>" 
                                           class="btn btn-sm btn-primary"
                                           onclick="return confirm('Mark this book as returned?')">
                                            <i class="fas fa-undo me-1"></i> Mark Returned
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
    
    <!-- Quick Return by Role Number -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 fw-bold"><i class="fas fa-search me-2"></i>Quick Return by Role Number</h5>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="role_number" placeholder="Enter Student Role Number">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </div>
            </form>
            
            <?php if (isset($_GET['role_number'])): 
                $roleNumber = sanitize($_GET['role_number']);
                $stmt = $pdo->prepare("
                    SELECT t.*, b.title, b.author, u.name as student_name, u.role_number 
                    FROM transactions t 
                    JOIN books b ON t.book_id = b.id 
                    JOIN users u ON t.student_id = u.id 
                    WHERE u.role_number = ? AND t.status = 'issued'
                ");
                $stmt->execute([$roleNumber]);
                $books = $stmt->fetchAll();
            ?>
                <div class="mt-4">
                    <h6>Issued Books for Role Number: <strong><?php echo htmlspecialchars($roleNumber); ?></strong></h6>
                    <?php if (empty($books)): ?>
                        <p class="text-muted">No books found for this role number.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>Due Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($book['author']); ?></small>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($book['due_date'])); ?></td>
                                            <td>
                                                <a href="return_book.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-undo me-1"></i> Return
                                                </a>
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
