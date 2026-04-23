<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotLibrarian();

$reservations = getAllReservations($pdo);
$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-0"><i class="fas fa-calendar-check me-2"></i>Reservations</h2>
        </div>
    </div>
    
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
    
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pending">
                        Pending <span class="badge bg-warning ms-1"><?php echo count(array_filter($reservations, function($r) { return $r['status'] === 'pending'; })); ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#completed">
                        Completed
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#cancelled">
                        Cancelled
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#all">
                        All
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- Pending -->
                <div class="tab-pane fade show active" id="pending">
                    <?php 
                    $pending = array_filter($reservations, function($r) { return $r['status'] === 'pending'; });
                    if (empty($pending)): 
                    ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-muted">No pending reservations</h5>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Role Number</th>
                                        <th>Book</th>
                                        <th>Reserved Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending as $res): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($res['student_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($res['role_number']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($res['title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($res['author']); ?></small>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($res['reserved_at'])); ?></td>
                                            <td><span class="badge bg-warning">Pending</span></td>
                                            <td>
                                                <a href="issue_book.php?id=<?php echo $res['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check me-1"></i> Issue
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Completed -->
                <div class="tab-pane fade" id="completed">
                    <?php 
                    $completed = array_filter($reservations, function($r) { return $r['status'] === 'completed'; });
                    if (empty($completed)): 
                    ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No completed reservations</h5>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Book</th>
                                        <th>Reserved Date</th>
                                        <th>Completed Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($completed as $res): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($res['student_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($res['title']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($res['reserved_at'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($res['reserved_at'])); // This would need a completed_at field in DB ?></td>
                                            <td><span class="badge bg-success">Completed</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Cancelled -->
                <div class="tab-pane fade" id="cancelled">
                    <?php 
                    $cancelled = array_filter($reservations, function($r) { return $r['status'] === 'cancelled'; });
                    if (empty($cancelled)): 
                    ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No cancelled reservations</h5>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Book</th>
                                        <th>Reserved Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cancelled as $res): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($res['student_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($res['title']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($res['reserved_at'])); ?></td>
                                            <td><span class="badge bg-secondary">Cancelled</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- All -->
                <div class="tab-pane fade" id="all">
                    <?php if (empty($reservations)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No reservations</h5>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Role Number</th>
                                        <th>Book</th>
                                        <th>Reserved Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $res): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($res['student_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($res['role_number']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($res['title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($res['author']); ?></small>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($res['reserved_at'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $res['status'] === 'pending' ? 'warning' : 
                                                        ($res['status'] === 'completed' ? 'success' : 'secondary'); 
                                                ?>">
                                                    <?php echo ucfirst($res['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($res['status'] === 'pending'): ?>
                                                    <a href="issue_book.php?id=<?php echo $res['id']; ?>" class="btn btn-sm btn-success">Issue</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
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
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
