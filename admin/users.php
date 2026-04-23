<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotAdmin();

$message = '';
$error = '';

$role = $_GET['role'] ?? '';
$users = getAllUsers($pdo, $role);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = sanitize($_POST['name']);
            $email = sanitize($_POST['email']);
            $roleNumber = sanitize($_POST['role_number']);
            $department = sanitize($_POST['department']);
            $password = $_POST['password'];
            $userRole = $_POST['role'];
            
            $result = addUser($pdo, $name, $email, $roleNumber, $department, $password, $userRole);
            
            if ($result['success']) {
                $message = 'User added successfully';
            } else {
                $error = $result['message'];
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            if (deleteUser($pdo, $id)) {
                $message = 'User deleted successfully';
            } else {
                $error = 'Failed to delete user';
            }
        }
    }
}

$users = getAllUsers($pdo, $role);
?>

<div class="container-fluid px-4 py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-users me-2"></i>User Management</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff;">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Role Number / Username</label>
                            <input type="text" class="form-control" name="role_number" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="student">Student</option>
                                <option value="librarian">Librarian</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Add User</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8fafc, #fff);">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Users</h5>
                    <div class="btn-group" role="group">
                        <a href="?role=" class="btn btn-sm btn-outline-primary">All</a>
                        <a href="?role=student" class="btn btn-sm btn-outline-success">Students</a>
                        <a href="?role=librarian" class="btn btn-sm btn-outline-warning">Librarians</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role Number</th>
                                    <th>Department</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role_number'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($user['department'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $user['role'] === 'admin' ? 'dark' : ($user['role'] === 'librarian' ? 'warning' : 'success'); 
                                            ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?></td>
                                        <td>
                                            <?php if ($user['role'] !== 'admin'): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
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

<?php require_once '../includes/footer.php'; ?>
