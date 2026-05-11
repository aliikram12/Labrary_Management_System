<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotStudent();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author'] ?? '');
    $isbn = sanitize($_POST['isbn'] ?? '');
    $reason = sanitize($_POST['reason'] ?? '');
    createBookRequest($pdo, $_SESSION['user_id'], $title, $author, $isbn, $reason);
    $message = 'Book request submitted successfully';
}

$requests = getStudentBookRequests($pdo, $_SESSION['user_id']);
?>

<div class="container-fluid fade-in">
    <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5" data-aos="fade-up">
            <div class="card card-gold">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-paper-plane me-2 text-gold"></i>Request a Book</h5></div>
                <div class="card-body">
                    <p class="text-muted mb-3">Can't find a book? Request the library to add it.</p>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Book Title *</label>
                            <input type="text" class="form-control" name="title" required placeholder="Enter book title">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Author</label>
                            <input type="text" class="form-control" name="author" placeholder="Author name (optional)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ISBN</label>
                            <input type="text" class="form-control" name="isbn" placeholder="ISBN if known">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" name="reason" rows="3" placeholder="Why do you need this book?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-oxford w-100"><i class="fas fa-paper-plane me-2"></i>Submit Request</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>My Requests</h5></div>
                <div class="card-body">
                    <?php if (!empty($requests)): ?>
                    <div class="table-responsive">
                        <table class="table data-table">
                            <thead><tr><th>Title</th><th>Author</th><th>Date</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($requests as $r): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($r['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($r['author'] ?? '—'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                                    <td><span class="badge bg-<?php echo $r['status'] === 'approved' ? 'success' : ($r['status'] === 'rejected' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($r['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>No requests yet</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
