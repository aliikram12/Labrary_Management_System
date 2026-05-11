<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

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
            $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $publisher = sanitize($_POST['publisher'] ?? '');
            $edition = sanitize($_POST['edition'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            
            if (addBook($pdo, $title, $author, $isbn, $subject, $copies, $categoryId, $publisher, $edition, $description)) {
                $message = 'Book added successfully';
                logAction($_SESSION['user_id'], "Added book: $title", $pdo);
            } else { $error = 'Failed to add book'; }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $book = getBookById($pdo, $id);
            if (deleteBook($pdo, $id)) {
                $message = 'Book deleted successfully';
                logAction($_SESSION['user_id'], "Deleted book: " . ($book['title'] ?? ''), $pdo);
            } else { $error = 'Failed to delete book'; }
        }
    }
}

$books = getAllBooks($pdo);
$categories = getCategories($pdo);
?>

<div class="container-fluid fade-in">
    <?php if ($message): ?><div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>
    
    <div class="row g-4">
        <div class="col-12 col-lg-9" data-aos="fade-up">
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-book me-2 text-oxford"></i>All Books</h5>
                    <span class="badge" style="background:linear-gradient(135deg,var(--oxford-blue),var(--oxford-navy));padding:8px 16px;border-radius:8px;color:#fff"><?php echo count($books); ?> books</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table">
                            <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>ISBN</th><th>Copies</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($book['subject'] ?? 'N/A'); ?></span></td>
                                    <td><code><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></code></td>
                                    <td><span class="text-success fw-bold"><?php echo $book['available_copies']; ?></span> / <?php echo $book['total_copies']; ?></td>
                                    <td><span class="badge bg-<?php echo $book['status'] === 'available' ? 'success' : ($book['status'] === 'reserved' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($book['status']); ?></span></td>
                                    <td>
                                        <form method="POST" class="d-inline" id="del-book-<?php echo $book['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('del-book-<?php echo $book['id']; ?>','<?php echo htmlspecialchars($book['title']); ?>')"><i class="fas fa-trash"></i></button>
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
        
        <div class="col-12 col-lg-3" data-aos="fade-up" data-aos-delay="100">
            <div class="card card-gold">
                <div class="card-header" style="background:linear-gradient(135deg,var(--oxford-blue),var(--oxford-navy));color:#fff;"><h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Add New Book</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3"><label class="form-label">Title *</label><input type="text" class="form-control" name="title" required></div>
                        <div class="mb-3"><label class="form-label">Author *</label><input type="text" class="form-control" name="author" required></div>
                        <div class="mb-3"><label class="form-label">ISBN</label><input type="text" class="form-control" name="isbn"></div>
                        <div class="mb-3"><label class="form-label">Category</label>
                            <select class="form-select" name="subject">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Publisher</label><input type="text" class="form-control" name="publisher"></div>
                        <div class="mb-3"><label class="form-label">Edition</label><input type="text" class="form-control" name="edition"></div>
                        <div class="mb-3"><label class="form-label">Copies *</label><input type="number" class="form-control" name="copies" value="1" min="1" required></div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
                        <button type="submit" class="btn btn-oxford w-100"><i class="fas fa-plus me-2"></i>Add Book</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
