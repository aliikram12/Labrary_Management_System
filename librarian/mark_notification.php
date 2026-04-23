<?php
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

redirectIfNotLibrarian();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    markNotificationRead($pdo, $id);
}

header('Location: dashboard.php');
exit;
