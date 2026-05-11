<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = $_POST['settings'] ?? [];
    foreach ($settings as $key => $value) {
        updateSetting($pdo, sanitize($key), sanitize($value));
    }
    $message = 'Settings updated successfully';
    logAction($_SESSION['user_id'], 'Updated system settings', $pdo);
}

$allSettings = getAllSettings($pdo);
$groups = [];
foreach ($allSettings as $s) {
    $groups[$s['setting_group'] ?? 'general'][] = $s;
}
$groupLabels = ['general' => 'General Settings', 'borrowing' => 'Borrowing Rules', 'fines' => 'Fine Configuration', 'contact' => 'Contact Information', 'cards' => 'Library Cards', 'notifications' => 'Notifications'];
?>

<div class="container-fluid fade-in">
    <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST">
                <?php foreach ($groups as $group => $settings): ?>
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-cog me-2 text-oxford"></i><?php echo $groupLabels[$group] ?? ucfirst($group); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($settings as $s): ?>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo htmlspecialchars($s['setting_label'] ?? $s['setting_key']); ?></label>
                                <input type="text" class="form-control" name="settings[<?php echo htmlspecialchars($s['setting_key']); ?>]" value="<?php echo htmlspecialchars($s['setting_value']); ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-oxford btn-lg"><i class="fas fa-save me-2"></i>Save All Settings</button>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="card card-gold" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-gold"></i>Help</h5></div>
                <div class="card-body">
                    <ul class="info-list">
                        <li><span class="label">Fine Per Day</span><span class="value">Amount charged daily for overdue</span></li>
                        <li><span class="label">Max Books</span><span class="value">Limit per student at one time</span></li>
                        <li><span class="label">Borrow Period</span><span class="value">Default days before due</span></li>
                        <li><span class="label">Card Validity</span><span class="value">Auto-expire student cards</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
