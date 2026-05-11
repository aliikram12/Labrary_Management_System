<?php 
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotStudent();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $result = cancelReservation($pdo, (int)$_POST['reservation_id'], $_SESSION['user_id']);
    $_SESSION['flash_message'] = $result['message'];
    $_SESSION['flash_type'] = $result['success'] ? 'success' : 'danger';
}

header('Location: dashboard.php');
exit;
