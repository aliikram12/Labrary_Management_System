<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

$message = '';
$error = '';
$role = $_GET['role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $result = addUser($pdo, sanitize($_POST['name']), sanitize($_POST['email']), sanitize($_POST['role_number']), sanitize($_POST['department']), $_POST['password'], $_POST['role']);
            if ($result['success']) {
                $message = 'User added successfully';
                logAction($_SESSION['user_id'], "Added user: " . $_POST['name'], $pdo);
            } else { $error = $result['message']; }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $user = getUserById($pdo, $id);
            if (deleteUser($pdo, $id)) {
                $message = 'User deleted successfully';
                logAction($_SESSION['user_id'], "Deleted user: " . ($user['name'] ?? ''), $pdo);
            } else { $error = 'Failed to delete user'; }
        }
    }
}

$users = getAllUsers($pdo, $role);
?>

<div class="container-fluid fade-in">
    <?php if ($message): ?><div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>
    
    <div class="row g-4">
        <div class="col-lg-3" data-aos="fade-up">
            <div class="card card-gold">
                <div class="card-header" style="background:linear-gradient(135deg,var(--oxford-blue),var(--oxford-navy));color:#fff;"><h5 class="mb-0 fw-bold"><i class="fas fa-user-plus me-2"></i>Add New User</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3"><label class="form-label">Name *</label><input type="text" class="form-control" name="name" required></div>
                        <div class="mb-3"><label class="form-label">Email *</label><input type="email" class="form-control" name="email" required></div>
                        <div class="mb-3"><label class="form-label">Roll No / Username *</label><input type="text" class="form-control" name="role_number" required></div>
                        <div class="mb-3"><label class="form-label">Department</label><input type="text" class="form-control" name="department"></div>
                        <div class="mb-3"><label class="form-label">Role *</label>
                            <select class="form-select" name="role" required>
                                <option value="student">Student</option>
                                <option value="librarian">Librarian</option>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Password *</label><input type="password" class="form-control" name="password" required minlength="6"></div>
                        <button type="submit" class="btn btn-oxford w-100"><i class="fas fa-user-plus me-2"></i>Add User</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-oxford"></i>All Users</h5>
                    <div class="btn-group">
                        <a href="?role=" class="btn btn-sm <?php echo !$role ? 'btn-oxford' : 'btn-oxford-outline'; ?>">All</a>
                        <a href="?role=student" class="btn btn-sm <?php echo $role === 'student' ? 'btn-oxford' : 'btn-oxford-outline'; ?>">Students</a>
                        <a href="?role=librarian" class="btn btn-sm <?php echo $role === 'librarian' ? 'btn-oxford' : 'btn-oxford-outline'; ?>">Librarians</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table">
                            <thead><tr><th>User</th><th>Email</th><th>Roll No</th><th>Dept</th><th>Role</th><th>Last Login</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="table-avatar"><?php echo getUserInitial($user['name']); ?></div>
                                            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><code><?php echo htmlspecialchars($user['role_number'] ?? 'N/A'); ?></code></td>
                                    <td><?php echo htmlspecialchars($user['department'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-<?php echo $user['role'] === 'admin' ? 'dark' : ($user['role'] === 'librarian' ? 'warning' : 'success'); ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><?php echo $user['last_login'] ? timeAgo($user['last_login']) : '<span class="text-muted">Never</span>'; ?></td>
                                    <td>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="POST" class="d-inline" id="del-user-<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('del-user-<?php echo $user['id']; ?>','<?php echo htmlspecialchars($user['name']); ?>')"><i class="fas fa-trash"></i></button>
                                        </form>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
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

<?php require_once '../includes/footer.php'; ?>
