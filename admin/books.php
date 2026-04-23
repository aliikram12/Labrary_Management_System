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

<div class="container-fluid px-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-book me-2"></i>Book Management</h2>
    
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
    
    <div class="row g-4">
        <div class="col-12 col-lg-9">
            <div class="card border-0" style="border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center gap-2" style="background: linear-gradient(135deg, #f8fafc, #fff); border-bottom: 1px solid #e2e8f0; padding: 18px 20px;">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>All Books</h5>
                    <span class="badge" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 8px 16px; border-radius: 8px;"><?php echo count($books); ?> books</span>
                </div>
                <div class="card-body p-3 p-md-20">
                    <?php if (empty($books)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-book fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No books in the library</h5>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" style="margin-bottom: 0;">
                                <thead>
                                    <tr style="background: #f8fafc;">
                                        <th style="font-weight: 600; color: #64748b; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 14px 16px;">Title</th>
                                        <th style="font-weight: 600; color: #64748b; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 14px 16px;">Author</th>
                                        <th style="font-weight: 600; color: #64748b; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 14px 16px;">Category</th>
                                        <th style="font-weight: 600; color: #64748b; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 14px 16px;">ISBN</th>
                                        <th style="font-weight: 600; color: #64748b; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 14px 16px;">Copies</th>
                                        <th style="font-weight: 600; color: #64748b; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 14px 16px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($books as $book): ?>
                                        <tr style="transition: all 0.2s;">
                                            <td style="padding: 16px; vertical-align: middle;">
                                                <strong style="color: #1e293b;"><?php echo htmlspecialchars($book['title']); ?></strong>
                                            </td>
                                            <td style="padding: 16px; vertical-align: middle;"><?php echo htmlspecialchars($book['author']); ?></td>
                                            <td style="padding: 16px; vertical-align: middle;"><?php echo htmlspecialchars($book['subject'] ?? 'N/A'); ?></td>
                                            <td style="padding: 16px; vertical-align: middle;"><code style="background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem;"><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></code></td>
                                            <td style="padding: 16px; vertical-align: middle;">
                                                <span class="text-success fw-bold"><?php echo $book['available_copies']; ?></span> / 
                                                <span class="text-muted"><?php echo $book['total_copies']; ?></span>
                                            </td>
                                            <td style="padding: 16px; vertical-align: middle;">
                                                <span class="badge" style="padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.75rem; background: <?php echo $book['status'] === 'available' ? 'linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a;' : ($book['status'] === 'reserved' ? 'linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706;' : 'linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626;'); ?>">
                                                    <?php echo ucfirst($book['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-lg-3 mt-4 mt-lg-0">
            <div class="card border-0" style="border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; padding: 18px 20px;">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Add New Book</h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 500; color: #374151;">Title</label>
                            <input type="text" class="form-control" name="title" placeholder="Enter book title" style="border-radius: 10px; border: 2px solid #e2e8f0; padding: 12px 16px;" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 500; color: #374151;">Author</label>
                            <input type="text" class="form-control" name="author" placeholder="Enter author name" style="border-radius: 10px; border: 2px solid #e2e8f0; padding: 12px 16px;" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 500; color: #374151;">ISBN</label>
                            <input type="text" class="form-control" name="isbn" placeholder="Enter ISBN (optional)" style="border-radius: 10px; border: 2px solid #e2e8f0; padding: 12px 16px;">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 500; color: #374151;">Category/Subject</label>
                            <input type="text" class="form-control" name="subject" placeholder="Enter category (optional)" style="border-radius: 10px; border: 2px solid #e2e8f0; padding: 12px 16px;">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 500; color: #374151;">Number of Copies</label>
                            <input type="number" class="form-control" name="copies" value="1" min="1" style="border-radius: 10px; border: 2px solid #e2e8f0; padding: 12px 16px;" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none; padding: 14px; border-radius: 10px; font-weight: 600; box-shadow: 0 4px 15px rgba(79,70,229,0.3);">
                            <i class="fas fa-plus me-2"></i> Add Book
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
