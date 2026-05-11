<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotStudent();

$query = sanitize($_GET['q'] ?? '');
$category = sanitize($_GET['category'] ?? '');
$books = getAllBooks($pdo, $query);
$categories = getCategories($pdo);

// Handle reservation
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $result = reserveBook($pdo, (int)$_POST['book_id'], $_SESSION['user_id']);
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
    $books = getAllBooks($pdo, $query); // Refresh
}
?>

<div class="container-fluid fade-in">
    <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="card mb-4" data-aos="fade-up">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="fas fa-search me-1"></i>Search Books</label>
                    <input type="text" class="form-control form-control-lg" name="q" id="searchInput"
                           placeholder="Search by title, author, or ISBN..." 
                           value="<?php echo htmlspecialchars($query); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold"><i class="fas fa-filter me-1"></i>Category</label>
                    <select class="form-select form-select-lg" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $category === $cat['name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-oxford btn-lg w-100"><i class="fas fa-search me-1"></i>Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-book me-2 text-oxford"></i>
                <?php echo $query ? "Results for \"$query\"" : 'Book Catalog'; ?>
            </h5>
            <span class="badge bg-light text-dark"><?php echo count($books); ?> books</span>
        </div>
        <div class="card-body">
            <?php if (!empty($books)): ?>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr><th>Title</th><th>Author</th><th>Category</th><th>Available</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($book['subject'] ?? 'N/A'); ?></span></td>
                            <td>
                                <span class="text-<?php echo $book['available_copies'] > 0 ? 'success' : 'danger'; ?> fw-bold">
                                    <?php echo $book['available_copies']; ?>
                                </span> / <?php echo $book['total_copies']; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $book['available_copies'] > 0 ? 'success' : 'danger'; ?>">
                                    <?php echo $book['available_copies'] > 0 ? 'Available' : 'Unavailable'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($book['available_copies'] > 0): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-gold"
                                            onclick="return confirm('Reserve this book?')">
                                        <i class="fas fa-bookmark me-1"></i>Reserve
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-search fa-3x mb-3 d-block"></i>
                    <h5>No books found</h5>
                    <p>Try a different search term or category</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
