<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();
$logs = getSystemLogs($pdo, 200);
?>

<div class="container-fluid fade-in">
    <div class="card" data-aos="fade-up">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-oxford"></i>System Activity Log</h5>
            <span class="badge bg-light text-dark"><?php echo count($logs); ?> entries</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead><tr><th>ID</th><th>User</th><th>Role</th><th>Action</th><th>IP</th><th>Timestamp</th></tr></thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($log['user_name'] ?? 'System'); ?></strong></td>
                            <td>
                                <?php if ($log['role']): ?>
                                    <span class="badge bg-<?php echo $log['role'] === 'admin' ? 'dark' : ($log['role'] === 'librarian' ? 'warning' : 'success'); ?>"><?php echo ucfirst($log['role']); ?></span>
                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><code><?php echo htmlspecialchars($log['ip_address'] ?? '—'); ?></code></td>
                            <td><?php echo date('M d, Y H:i:s', strtotime($log['timestamp'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
