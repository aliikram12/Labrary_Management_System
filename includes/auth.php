<?php
require_once __DIR__ . '/../config/database.php';

function loginUser($pdo, $email, $password, $role) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Check if user is suspended
        if (isset($user['status']) && $user['status'] === 'suspended') {
            logFailedLogin($pdo, $email, $role, 'Account suspended');
            return ['success' => false, 'message' => 'Your account has been suspended. Contact admin.'];
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['role_number'] = $user['role_number'];
        $_SESSION['csrf_token'] = generateToken();
        
        // Update last login
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        
        // Log action
        logAction($user['id'], 'User logged in', $pdo, "Role: $role");
        
        return ['success' => true, 'role' => $user['role']];
    }
    
    logFailedLogin($pdo, $email, $role);
    return ['success' => false, 'message' => 'Invalid email or password'];
}

function registerUser($pdo, $name, $email, $roleNumber, $department, $password) {
    // Check if email or role number exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR role_number = ?");
    $stmt->execute([$email, $roleNumber]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email or Roll Number already exists'];
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $cardId = 'LIB-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $cardExpiry = date('Y-m-d', strtotime('+1 year'));
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, role_number, department, password, role, card_id, card_expiry) VALUES (?, ?, ?, ?, ?, 'student', ?, ?)");
        $stmt->execute([$name, $email, $roleNumber, $department, $hashedPassword, $cardId, $cardExpiry]);
        
        $userId = $pdo->lastInsertId();
        logAction($userId, 'New student registered', $pdo, "Name: $name, Email: $email");
        
        // Welcome notification
        createNotification($pdo, "Welcome to AliStack Digital Library, $name! Your library card has been activated.", 'welcome', 'student', $userId, 'fas fa-gift', 'card.php');
        
        return ['success' => true, 'id' => $userId];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

function logoutUser() {
    session_destroy();
    header('Location: login.php');
    exit;
}

function logFailedLogin($pdo, $email, $role, $reason = 'Invalid credentials') {
    try {
        $ip = getClientIP();
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, ip_address, details) VALUES (NULL, ?, ?, ?)");
        $stmt->execute(["Failed login attempt for: $email ($role)", $ip, $reason]);
    } catch (Exception $e) {
        error_log("Failed login log error: " . $e->getMessage());
    }
}
