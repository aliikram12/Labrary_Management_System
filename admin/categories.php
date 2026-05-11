<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

$error = '';
$success = '';

// Handle category operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            if (empty($name)) {
                $error = "Category name is required.";
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                    $stmt->execute([$name, $description]);
                    logAction($pdo, $_SESSION['user_id'], 'admin', "Added new category: $name");
                    $success = "Category added successfully.";
                } catch(PDOException $e) {
                    $error = "Error adding category: " . $e->getMessage();
                }
            }
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            if (empty($name)) {
                $error = "Category name is required.";
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $id]);
                    logAction($pdo, $_SESSION['user_id'], 'admin', "Updated category ID: $id");
                    $success = "Category updated successfully.";
                } catch(PDOException $e) {
                    $error = "Error updating category: " . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            try {
                // Check if books are using this category
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE category_id = ?");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Cannot delete category: There are books assigned to this category.";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$id]);
                    logAction($pdo, $_SESSION['user_id'], 'admin', "Deleted category ID: $id");
                    $success = "Category deleted successfully.";
                }
            } catch(PDOException $e) {
                $error = "Error deleting category: " . $e->getMessage();
            }
        }
    }
}

// Fetch categories with book count
$stmt = $pdo->query("
    SELECT c.*, COUNT(b.id) as book_count 
    FROM categories c 
    LEFT JOIN books b ON c.id = b.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();
?>

<div class="container-fluid fade-in">
    <?php if ($error): ?>
        <script>document.addEventListener('DOMContentLoaded', function() { toastr.error("<?php echo addslashes($error); ?>"); });</script>
    <?php endif; ?>
    <?php if ($success): ?>
        <script>document.addEventListener('DOMContentLoaded', function() { toastr.success("<?php echo addslashes($success); ?>"); });</script>
    <?php endif; ?>

    <div class="row">
        <!-- Add Category Form -->
        <div class="col-lg-4 mb-4">
            <div class="card" data-aos="fade-right">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2 text-oxford"></i>Add Category</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control" name="name" required placeholder="e.g., Computer Science">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Brief description of this category"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-oxford w-100">
                            <i class="fas fa-save me-2"></i>Save Category
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Categories List -->
        <div class="col-lg-8">
            <div class="card" data-aos="fade-left">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-tags me-2 text-gold"></i>Manage Categories</h5>
                    <span class="badge bg-light text-dark"><?php echo count($categories); ?> Categories</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Books</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                    <td><span class="text-muted text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo htmlspecialchars($cat['description'] ?? ''); ?>"><?php echo htmlspecialchars($cat['description'] ?? 'No description'); ?></span></td>
                                    <td><span class="badge bg-info"><?php echo $cat['book_count']; ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($cat['book_count'] == 0): ?>
                                            <form method="POST" action="" class="d-inline" id="delForm_<?php echo $cat['id']; ?>" onsubmit="event.preventDefault(); confirmDelete('delForm_<?php echo $cat['id']; ?>', '<?php echo addslashes($cat['name']); ?>');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled title="Cannot delete: books attached" onclick="toastr.warning('Remove associated books before deleting this category.');">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
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

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header bg-oxford text-white" style="border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                <h5 class="modal-title font-serif"><i class="fas fa-edit me-2 text-gold"></i>Edit Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editCatId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category Name</label>
                        <input type="text" class="form-control" name="name" id="editCatName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" name="description" id="editCatDesc" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold text-dark"><i class="fas fa-save me-2"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(cat) {
    document.getElementById('editCatId').value = cat.id;
    document.getElementById('editCatName').value = cat.name;
    document.getElementById('editCatDesc').value = cat.description || '';
    
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
</script>

<?php require_once '../includes/footer.php'; ?>
