<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotAdmin();

$logs = getSystemLogs($pdo, 100);
?>

<div class="container-fluid px-4 py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-history me-2"></i>System Logs</h2>
    
    <div class="card" style="border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8fafc, #fff); padding: 18px 22px; border-bottom: 1px solid #e2e8f0;">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list-alt me-2 text-primary"></i>Recent Activity Log</h5>
            <span class="badge" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 8px 14px; border-radius: 8px;"><?php echo count($logs); ?> entries</span>
        </div>
        <div class="card-body" style="padding: 20px;">
            <?php if (empty($logs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No system logs yet</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['id']; ?></td>
                                    <td><?php echo htmlspecialchars($log['user_name'] ?? 'System'); ?></td>
                                    <td>
                                        <?php if ($log['role']): ?>
                                            <span class="badge bg-<?php 
                                                echo $log['role'] === 'admin' ? 'dark' : 
                                                    ($log['role'] === 'librarian' ? 'warning' : 'success'); 
                                            ?>">
                                                <?php echo ucfirst($log['role']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo date('M d, Y H:i:s', strtotime($log['timestamp'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
