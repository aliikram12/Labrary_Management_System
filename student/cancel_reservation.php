<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$reservationId = (int)$_GET['id'];
$studentId = $_SESSION['user_id'];

$result = cancelReservation($pdo, $reservationId, $studentId);

if ($result['success']) {
    header('Location: dashboard.php?message=' . urlencode($result['message']));
} else {
    header('Location: dashboard.php?error=' . urlencode($result['message']));
}
exit;
