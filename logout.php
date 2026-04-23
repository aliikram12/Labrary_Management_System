<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    try {
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action) VALUES (?, ?)");
        $stmt->execute([$userId, 'Logout']);
    } catch (Exception $e) {
        error_log("Failed to log action: " . $e->getMessage());
    }
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header('Location: index.php');
exit;
