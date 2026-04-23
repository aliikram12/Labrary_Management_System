<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotLibrarian();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $title = sanitize($_POST['title']);
            $author = sanitize($_POST['author']);
            $isbn = sanitize($_POST['isbn']);
            $subject = sanitize($_POST['subject']);
            $copies = (int)$_POST['copies'];
            
            if (addBook($pdo, $title, $author, $isbn, $subject, $copies)) {
                $message = 'Book added successfully';
            } else {
                $error = 'Failed to add book';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            if (deleteBook($pdo, $id)) {
                $message = 'Book deleted successfully';
            } else {
                $error = 'Failed to delete book';
            }
        }
    }
}

$books = getAllBooks($pdo);
?>

<div class="container-fluid px-4 py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-books me-2"></i>Book Inventory</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Subject</th>
                                    <th>Copies</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                            <small class="text-muted">ISBN: <?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><?php echo htmlspecialchars($book['subject']); ?></td>
                                        <td><?php echo $book['available_copies']; ?> / <?php echo $book['total_copies']; ?></td>
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
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #0891b2, #0e7490); color: #fff;">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Book</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Author</label>
                            <input type="text" class="form-control" name="author" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ISBN</label>
                            <input type="text" class="form-control" name="isbn">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" name="subject">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Number of Copies</label>
                            <input type="number" class="form-control" name="copies" value="1" min="1" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Add Book</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
