<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotAdmin();

$stats = getSystemStats($pdo);
$transactions = getAllTransactions($pdo);
$books = getAllBooks($pdo);
$users = getAllUsers($pdo, 'student');

$filterStudent = $_GET['student_id'] ?? '';
$filterBook = $_GET['book_id'] ?? '';

$reportTransactions = $transactions;
if ($filterStudent) {
    $reportTransactions = array_filter($transactions, function($t) use ($filterStudent) {
        return $t['student_id'] == $filterStudent;
    });
}
if ($filterBook) {
    $reportTransactions = array_filter($reportTransactions, function($t) use ($filterBook) {
        return $t['book_id'] == $filterBook;
    });
}
?>

<div class="container-fluid px-4 py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h2>
    
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview">
                System Overview
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#borrowing">
                Borrowing History
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#inventory">
                Inventory Status
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#activity">
                User Activity
            </button>
        </li>
    </ul>
    
    <div class="tab-content">
        <div class="tab-pane fade show active" id="overview">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-book fa-3x text-primary mb-3"></i>
                            <h3><?php echo $stats['total_books']; ?></h3>
                            <p class="mb-0">Total Books</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-user-graduate fa-3x text-success mb-3"></i>
                            <h3><?php echo $stats['total_students']; ?></h3>
                            <p class="mb-0">Total Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-book-reader fa-3x text-warning mb-3"></i>
                            <h3><?php echo $stats['total_issued']; ?></h3>
                            <p class="mb-0">Currently Issued</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Book Availability</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <td>Available Copies</td>
                                        <td><strong><?php echo $stats['available_books']; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Issued Books</td>
                                        <td><strong><?php echo $stats['total_issued']; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Pending Reservations</td>
                                        <td><strong><?php echo $stats['total_reservations']; ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">User Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <td>Students</td>
                                        <td><strong><?php echo $stats['total_students']; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Librarians</td>
                                        <td><strong><?php echo $stats['total_librarians']; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Admins</td>
                                        <td><strong>1</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="borrowing">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Borrowing History Report</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Student</label>
                            <select class="form-select" name="student_id">
                                <option value="">All Students</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo $filterStudent == $user['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Filter by Book</label>
                            <select class="form-select" name="book_id">
                                <option value="">All Books</option>
                                <?php foreach ($books as $book): ?>
                                    <option value="<?php echo $book['id']; ?>" <?php echo $filterBook == $book['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($book['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Role Number</th>
                                    <th>Book</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportTransactions as $trans): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($trans['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($trans['role_number']); ?></td>
                                        <td><?php echo htmlspecialchars($trans['title']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($trans['issue_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($trans['due_date'])); ?></td>
                                        <td><?php echo $trans['return_date'] ? date('M d, Y', strtotime($trans['return_date'])) : '-'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $trans['status'] === 'returned' ? 'success' : ($trans['status'] === 'overdue' ? 'danger' : 'warning'); 
                                            ?>">
                                                <?php echo ucfirst($trans['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="inventory">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Inventory Status Report</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Subject</th>
                                    <th>Total Copies</th>
                                    <th>Available</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><?php echo htmlspecialchars($book['subject']); ?></td>
                                        <td><?php echo $book['total_copies']; ?></td>
                                        <td><?php echo $book['available_copies']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $book['status'] === 'available' ? 'success' : ($book['status'] === 'reserved' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($book['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="activity">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">User Activity Report</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Last Login</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): 
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE student_id = ?");
                                    $stmt->execute([$user['id']]);
                                    $borrowCount = $stmt->fetchColumn();
                                    
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE student_id = ?");
                                    $stmt->execute([$user['id']]);
                                    $reserveCount = $stmt->fetchColumn();
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $user['role'] === 'admin' ? 'dark' : ($user['role'] === 'librarian' ? 'warning' : 'success'); 
                                            ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
