<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    markNotificationRead($pdo, (int)$_POST['notif_id']);
}
if (isset($_GET['mark_all'])) {
    markAllNotificationsRead($pdo);
    header('Location: notifications.php');
    exit;
}

$notifications = getNotifications($pdo, 100);
?>

<div class="container-fluid fade-in">
    <div class="card" data-aos="fade-up">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-bell me-2 text-gold"></i>All Notifications</h5>
            <a href="?mark_all=1" class="btn btn-sm btn-oxford-outline"><i class="fas fa-check-double me-1"></i>Mark All Read</a>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $n): ?>
                <div class="notif-item <?php echo $n['is_read'] ? '' : 'unread'; ?>" style="padding:16px 20px;border-bottom:1px solid var(--gray-100);">
                    <div class="notif-icon"><i class="<?php echo $n['icon'] ?? 'fas fa-bell'; ?>"></i></div>
                    <div class="notif-content" style="flex:1">
                        <p style="margin:0;font-size:.85rem"><?php echo htmlspecialchars($n['message']); ?></p>
                        <small class="text-muted"><?php echo timeAgo($n['created_at']); ?></small>
                    </div>
                    <?php if (!$n['is_read']): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
                        <button name="mark_read" value="1" class="btn btn-sm btn-outline-primary"><i class="fas fa-check"></i></button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-bell-slash fa-3x mb-3 d-block"></i>
                    <h5>No notifications yet</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
