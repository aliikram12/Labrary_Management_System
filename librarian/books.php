<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotLibrarian();
$books = getAllBooks($pdo);
?>

<div class="container-fluid fade-in">
    <div class="card" data-aos="fade-up">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-book me-2 text-oxford"></i>Book Inventory</h5>
            <span class="badge bg-light text-dark"><?php echo count($books); ?> books</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>ISBN</th><th>Copies</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($book['subject'] ?? 'N/A'); ?></span></td>
                            <td><code><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></code></td>
                            <td><span class="text-success fw-bold"><?php echo $book['available_copies']; ?></span> / <?php echo $book['total_copies']; ?></td>
                            <td><span class="badge bg-<?php echo $book['status'] === 'available' ? 'success' : ($book['status'] === 'reserved' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($book['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
