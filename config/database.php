<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} elseif (session_status() === PHP_SESSION_ACTIVE) {
    // Session is already active, no action needed
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'alistack_lms');

// System Constants
define('SITE_NAME', 'AliStack Digital Library');
define('SITE_VERSION', '2.0.0');
define('BASE_URL', '/Labrary_Management_System');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('PROFILE_IMG_DIR', UPLOAD_DIR . 'profiles/');
define('BOOK_IMG_DIR', UPLOAD_DIR . 'books/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Ensure upload directories exist
if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
if (!is_dir(PROFILE_IMG_DIR)) @mkdir(PROFILE_IMG_DIR, 0755, true);
if (!is_dir(BOOK_IMG_DIR)) @mkdir(BOOK_IMG_DIR, 0755, true);

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function isAdmin() {
    return getUserRole() === 'admin';
}

function isLibrarian() {
    return getUserRole() === 'librarian';
}

function isStudent() {
    return getUserRole() === 'student';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}

function redirectIfNotAdmin() {
    redirectIfNotLoggedIn();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit;
    }
}

function redirectIfNotLibrarian() {
    redirectIfNotLoggedIn();
    if (!isLibrarian()) {
        header('Location: ../index.php');
        exit;
    }
}

function redirectIfNotStudent() {
    redirectIfNotLoggedIn();
    if (!isStudent()) {
        header('Location: ../index.php');
        exit;
    }
}

function checkCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get a system setting value
 */
function getSetting($pdo, $key, $default = '') {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Get user's profile image URL or default avatar
 */
function getProfileImage($user) {
    if (!empty($user['profile_image']) && file_exists(PROFILE_IMG_DIR . $user['profile_image'])) {
        return BASE_URL . '/uploads/profiles/' . $user['profile_image'];
    }
    // Generate initial-based avatar
    $name = $user['name'] ?? 'U';
    $initial = strtoupper(substr($name, 0, 1));
    return '';  // Empty = use CSS initial avatar
}

/**
 * Get user initial for avatar
 */
function getUserInitial($name) {
    return strtoupper(substr(trim($name), 0, 1));
}

/**
 * Get current user data from database
 */
function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Format date in a human-friendly way
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Get client IP address
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}
