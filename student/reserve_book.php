<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: search.php');
    exit;
}

$bookId = (int)$_GET['id'];
$studentId = $_SESSION['user_id'];

$result = reserveBook($pdo, $bookId, $studentId);

if ($result['success']) {
    header('Location: search.php?message=' . urlencode($result['message']));
} else {
    header('Location: search.php?error=' . urlencode($result['message']));
}
exit;
