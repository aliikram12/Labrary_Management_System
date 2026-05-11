<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotLibrarian();

$user = getCurrentUser($pdo);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $data = [
            'name' => sanitize($_POST['name']),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'address' => sanitize($_POST['address'] ?? ''),
            'department' => sanitize($_POST['department'] ?? '')
        ];
        
        // Handle profile image upload
        if (!empty($_FILES['profile_image']['tmp_name'])) {
            $filename = uploadImage($_FILES['profile_image'], PROFILE_IMG_DIR, 'profile');
            if ($filename) {
                $data['profile_image'] = $filename;
            } else {
                $error = 'Failed to upload image. Max 5MB, JPG/PNG/GIF only.';
            }
        }
        
        if (!$error) {
            updateUserProfile($pdo, $_SESSION['user_id'], $data);
            $_SESSION['name'] = $data['name'];
            $message = 'Profile updated successfully';
            $user = getCurrentUser($pdo); // refresh
        }
    } elseif ($action === 'change_password') {
        $result = changePassword($pdo, $_SESSION['user_id'], $_POST['current_password'], $_POST['new_password']);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

$initial = getUserInitial($user['name']);
?>

<div class="container-fluid fade-in">
    <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-lg-4" data-aos="fade-up">
            <div class="card card-gold text-center">
                <div class="card-body py-5">
                    <div style="width:100px;height:100px;border-radius:50%;margin:0 auto 16px;background:linear-gradient(135deg,var(--oxford-blue),var(--oxford-navy));display:flex;align-items:center;justify-content:center;overflow:hidden;border:3px solid var(--oxford-gold);box-shadow:0 8px 25px rgba(0,0,0,.15);">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="../uploads/profiles/<?php echo $user['profile_image']; ?>" style="width:100%;height:100%;object-fit:cover" alt="Profile">
                        <?php else: ?>
                            <span style="color:#fff;font-size:2.5rem;font-weight:700"><?php echo $initial; ?></span>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'dark' : ($user['role'] === 'librarian' ? 'warning' : 'success'); ?> mb-3"><?php echo ucfirst($user['role']); ?></span>
                    <ul class="info-list text-start mt-3">
                        <li><span class="label"><i class="fas fa-envelope me-2"></i>Email</span><span class="value"><?php echo htmlspecialchars($user['email']); ?></span></li>
                        <li><span class="label"><i class="fas fa-id-badge me-2"></i>ID</span><span class="value"><?php echo htmlspecialchars($user['role_number']); ?></span></li>
                        <li><span class="label"><i class="fas fa-building me-2"></i>Dept</span><span class="value"><?php echo htmlspecialchars($user['department'] ?? 'N/A'); ?></span></li>
                        <li><span class="label"><i class="fas fa-clock me-2"></i>Joined</span><span class="value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Edit Forms -->
        <div class="col-lg-8">
            <div class="card mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-user-edit me-2 text-oxford"></i>Edit Profile</h5></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" name="department" value="<?php echo htmlspecialchars($user['department'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile Image</label>
                                <input type="file" class="form-control" name="profile_image" accept="image/*">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-oxford"><i class="fas fa-save me-2"></i>Update Profile</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-lock me-2 text-danger"></i>Change Password</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" required minlength="6">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-danger w-100"><i class="fas fa-key me-2"></i>Change Password</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
