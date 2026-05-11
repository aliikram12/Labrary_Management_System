<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotStudent();
$user = getCurrentUser($pdo);
$initial = getUserInitial($user['name']);
?>

<div class="container-fluid fade-in">
    <div class="row g-4 justify-content-center">
        <div class="col-lg-6" data-aos="fade-up">
            <div class="text-center mb-4">
                <h3 class="fw-bold font-serif">Your Digital Library Card</h3>
                <p class="text-muted">Present this card when borrowing books</p>
            </div>
            
            <!-- Library Card -->
            <div class="library-card-preview mx-auto" style="width:420px;max-width:100%;">
                <div class="card-lib-header">
                    <div class="card-lib-logo"><i class="fas fa-university"></i><span>AliStack Digital Library</span></div>
                    <span style="font-size:.6rem;color:var(--oxford-gold);text-transform:uppercase;letter-spacing:1px">Student ID</span>
                </div>
                <div class="card-lib-body">
                    <div class="card-lib-photo">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="../uploads/profiles/<?php echo $user['profile_image']; ?>" alt="">
                        <?php else: ?>
                            <span class="initial"><?php echo $initial; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-lib-info">
                        <span class="card-name"><?php echo htmlspecialchars($user['name']); ?></span>
                        <span class="card-dept"><?php echo htmlspecialchars($user['department'] ?? 'Student'); ?></span>
                        <span class="card-number"><?php echo htmlspecialchars($user['role_number']); ?></span>
                    </div>
                </div>
                <div class="card-lib-footer">
                    <span class="card-id"><?php echo htmlspecialchars($user['card_id'] ?? 'N/A'); ?></span>
                    <span class="card-expiry">VALID THRU: <?php echo $user['card_expiry'] ? date('m/Y', strtotime($user['card_expiry'])) : 'N/A'; ?></span>
                </div>
            </div>

            <!-- Card Details -->
            <div class="card mt-4">
                <div class="card-body">
                    <ul class="info-list">
                        <li><span class="label"><i class="fas fa-barcode me-2"></i>Card ID</span><span class="value"><code><?php echo htmlspecialchars($user['card_id'] ?? 'N/A'); ?></code></span></li>
                        <li>
                            <span class="label"><i class="fas fa-shield-alt me-2"></i>Status</span>
                            <span class="badge bg-<?php echo ($user['card_status'] ?? 'active') === 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($user['card_status'] ?? 'active'); ?>
                            </span>
                        </li>
                        <li><span class="label"><i class="fas fa-calendar me-2"></i>Expiry</span><span class="value"><?php echo $user['card_expiry'] ? date('F d, Y', strtotime($user['card_expiry'])) : 'N/A'; ?></span></li>
                        <li><span class="label"><i class="fas fa-user me-2"></i>Member Since</span><span class="value"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></span></li>
                    </ul>
                </div>
            </div>

            <div class="text-center mt-3">
                <button onclick="window.print()" class="btn btn-oxford"><i class="fas fa-print me-2"></i>Print Card</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
