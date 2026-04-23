<?php
require_once __DIR__ . '/../config/database.php';

function loginUser($email, $password, $pdo) {
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email and password are required'];
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR role_number = ?) AND password IS NOT NULL");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email/role number or password'];
    }
    
    if (!password_verify($password, $user['password'])) {
        logFailedLogin($email, $pdo);
        return ['success' => false, 'message' => 'Invalid email/role number or password'];
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['role_number'] = $user['role_number'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    logAction($user['id'], 'Login', $pdo);
    
    return ['success' => true, 'role' => $user['role'], 'name' => $user['name']];
}

function registerUser($name, $email, $roleNumber, $department, $password, $pdo) {
    if (empty($name) || empty($email) || empty($roleNumber) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
    }
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR role_number = ?");
    $stmt->execute([$email, $roleNumber]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email or Role Number already exists'];
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insertStmt = $pdo->prepare("INSERT INTO users (name, email, role_number, department, password, role) VALUES (?, ?, ?, ?, ?, 'student')");
    
    if ($insertStmt->execute([$name, $email, $roleNumber, $department, $hashedPassword])) {
        logAction($pdo->lastInsertId(), 'Registration', $pdo);
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Registration failed. Please try again'];
}

function logoutUser($pdo) {
    $userId = $_SESSION['user_id'] ?? null;
    
    if ($userId) {
        logAction($userId, 'Logout', $pdo);
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
    
    header('Location: ../index.php');
    exit;
}

function logAction($userId, $action, $pdo) {
    try {
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action) VALUES (?, ?)");
        $stmt->execute([$userId, $action]);
    } catch (Exception $e) {
        error_log("Failed to log action: " . $e->getMessage());
    }
}

function logFailedLogin($email, $pdo) {
    try {
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action) VALUES (NULL, ?)");
        $stmt->execute(['Failed login attempt: ' . $email]);
    } catch (Exception $e) {
        error_log("Failed to log failed login: " . $e->getMessage());
    }
}
