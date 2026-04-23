<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotAdmin();

$stats = getSystemStats($pdo);
$recentLogs = getSystemLogs($pdo, 20);
$transactions = getAllTransactions($pdo);
$books = getAllBooks($pdo);
?>

<div class="container-fluid px-4 py-4">
    <h2 class="fw-bold mb-4">Admin Dashboard</h2>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon primary">
                <i class="fas fa-book"></i>
            </div>
            <div class="info">
                <h3><?php echo $stats['total_books']; ?></h3>
                <p>Total Books</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon success">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="info">
                <h3><?php echo $stats['total_students']; ?></h3>
                <p>Total Students</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon warning">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="info">
                <h3><?php echo $stats['total_librarians']; ?></h3>
                <p>Librarians</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon info">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="info">
                <h3><?php echo $stats['total_issued']; ?></h3>
                <p>Books Issued</p>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Book Status Overview -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2"></i>Book Status Overview</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Available (<?php echo $stats['available_books']; ?> copies)</span>
                            <span class="text-success"><?php echo $stats['total_books'] ? round($stats['available_books'] / $stats['total_books'] * 100) : 0; ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $stats['total_books'] ? ($stats['available_books'] / $stats['total_books'] * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Issued (<?php echo $stats['total_issued']; ?> books)</span>
                            <span class="text-warning"><?php echo $stats['total_books'] ? round($stats['total_issued'] / $stats['total_books'] * 100) : 0; ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" style="width: <?php echo $stats['total_books'] ? ($stats['total_issued'] / $stats['total_books'] * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Reserved (<?php echo $stats['total_reservations']; ?> books)</span>
                            <span class="text-info"><?php echo $stats['total_books'] ? round($stats['total_reservations'] / $stats['total_books'] * 100) : 0; ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: <?php echo $stats['total_books'] ? ($stats['total_reservations'] / $stats['total_books'] * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                    <a href="logs.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentLogs)): ?>
                        <p class="text-muted text-center py-4">No recent activity.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentLogs as $log): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($log['user_name'] ?? 'System'); ?></td>
                                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                                            <td><small class="text-muted"><?php echo date('M d, H:i', strtotime($log['timestamp'])); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="users.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Manage Users
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="books.php" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-book fa-2x mb-2 d-block"></i>
                                Manage Books
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="reports.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                                View Reports
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="logs.php" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-history fa-2x mb-2 d-block"></i>
                                System Logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Information -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>System Name</strong></td>
                                <td>AliStack Library Management System</td>
                            </tr>
                            <tr>
                                <td><strong>Version</strong></td>
                                <td>1.0.0</td>
                            </tr>
                            <tr>
                                <td><strong>Total Users</strong></td>
                                <td><?php echo $stats['total_students'] + $stats['total_librarians'] + 1; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Database Status</strong></td>
                                <td><span class="badge bg-success">Connected</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-tasks me-2"></i>Pending Tasks</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Pending Reservations</span>
                        <span class="badge bg-warning"><?php echo $stats['total_reservations']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Books Currently Issued</span>
                        <span class="badge bg-primary"><?php echo $stats['total_issued']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Available Copies</span>
                        <span class="badge bg-success"><?php echo $stats['available_books']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
