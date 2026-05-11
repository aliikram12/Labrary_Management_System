<?php
require_once __DIR__ . '/../config/database.php';

// ============================================================================
// BOOK FUNCTIONS
// ============================================================================
function getAllBooks($pdo, $search = '') {
    if ($search) {
        $stmt = $pdo->prepare("SELECT b.*, c.name as category_name FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE (b.title LIKE ? OR b.author LIKE ? OR b.subject LIKE ? OR b.isbn LIKE ?)");
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    } else {
        $stmt = $pdo->query("SELECT b.*, c.name as category_name FROM books b LEFT JOIN categories c ON b.category_id = c.id ORDER BY b.title");
    }
    return $stmt->fetchAll();
}

function getBookById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT b.*, c.name as category_name FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE b.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function isBookAvailable($pdo, $bookId) {
    $book = getBookById($pdo, $bookId);
    return $book && $book['available_copies'] > 0;
}

function hasActiveReservation($pdo, $bookId, $studentId) {
    $stmt = $pdo->prepare("SELECT id FROM reservations WHERE book_id = ? AND student_id = ? AND status = 'pending'");
    $stmt->execute([$bookId, $studentId]);
    return $stmt->fetch();
}

function addBook($pdo, $title, $author, $isbn, $subject, $copies, $categoryId = null, $publisher = null, $edition = null, $description = null) {
    $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, subject, total_copies, available_copies, status, category_id, publisher, edition, description) VALUES (?, ?, ?, ?, ?, ?, 'available', ?, ?, ?, ?)");
    return $stmt->execute([$title, $author, $isbn, $subject, $copies, $copies, $categoryId, $publisher, $edition, $description]);
}

function updateBook($pdo, $id, $data) {
    $fields = [];
    $values = [];
    $allowed = ['title', 'author', 'isbn', 'subject', 'total_copies', 'available_copies', 'category_id', 'publisher', 'edition', 'description', 'shelf_location', 'book_image'];
    foreach ($allowed as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $values[] = $data[$field];
        }
    }
    if (empty($fields)) return false;
    $values[] = $id;
    $stmt = $pdo->prepare("UPDATE books SET " . implode(', ', $fields) . " WHERE id = ?");
    return $stmt->execute($values);
}

function deleteBook($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    return $stmt->execute([$id]);
}

function getCategories($pdo) {
    return $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name")->fetchAll();
}

function getAllCategories($pdo) {
    return $pdo->query("SELECT c.*, p.name as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY COALESCE(c.parent_id, c.id), c.parent_id IS NOT NULL, c.name")->fetchAll();
}

// ============================================================================
// RESERVATION FUNCTIONS
// ============================================================================
function reserveBook($pdo, $bookId, $studentId) {
    if (!isBookAvailable($pdo, $bookId)) {
        return ['success' => false, 'message' => 'Book is not available'];
    }
    if (hasActiveReservation($pdo, $bookId, $studentId)) {
        return ['success' => false, 'message' => 'You already have a reservation for this book'];
    }
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO reservations (student_id, book_id, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$studentId, $bookId]);
        $reservationId = $pdo->lastInsertId();

        $book = getBookById($pdo, $bookId);
        $studentStmt = $pdo->prepare("SELECT name, role_number FROM users WHERE id = ?");
        $studentStmt->execute([$studentId]);
        $student = $studentStmt->fetch();

        $message = "Student {$student['name']} ({$student['role_number']}) reserved: {$book['title']}";
        createNotification($pdo, $message, 'reservation', 'librarian', null, 'fas fa-bookmark', 'reservations.php');

        $updateBook = $pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
        $updateBook->execute([$bookId]);

        $updatedBook = getBookById($pdo, $bookId);
        if ($updatedBook['available_copies'] == 0) {
            $pdo->prepare("UPDATE books SET status = 'reserved' WHERE id = ?")->execute([$bookId]);
        }

        $pdo->commit();
        return ['success' => true, 'message' => 'Book reserved successfully'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Failed to reserve book'];
    }
}

function cancelReservation($pdo, $reservationId, $studentId) {
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND student_id = ? AND status = 'pending'");
    $stmt->execute([$reservationId, $studentId]);
    $reservation = $stmt->fetch();
    if (!$reservation) return ['success' => false, 'message' => 'Reservation not found'];

    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?")->execute([$reservationId]);
        $pdo->prepare("UPDATE books SET available_copies = available_copies + 1, status = 'available' WHERE id = ?")->execute([$reservation['book_id']]);
        $pdo->commit();
        return ['success' => true, 'message' => 'Reservation cancelled'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Failed to cancel reservation'];
    }
}

function getStudentReservations($pdo, $studentId) {
    $stmt = $pdo->prepare("SELECT r.*, b.title, b.author FROM reservations r JOIN books b ON r.book_id = b.id WHERE r.student_id = ? ORDER BY r.reserved_at DESC");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

function getStudentTransactions($pdo, $studentId) {
    $stmt = $pdo->prepare("SELECT t.*, b.title, b.author FROM transactions t JOIN books b ON t.book_id = b.id WHERE t.student_id = ? ORDER BY t.issue_date DESC");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

function getAllReservations($pdo) {
    return $pdo->query("SELECT r.*, b.title, b.author, u.name as student_name, u.role_number FROM reservations r JOIN books b ON r.book_id = b.id JOIN users u ON r.student_id = u.id ORDER BY r.reserved_at DESC")->fetchAll();
}

function getPendingReservations($pdo) {
    return $pdo->query("SELECT r.*, b.title, b.author, u.name as student_name, u.role_number FROM reservations r JOIN books b ON r.book_id = b.id JOIN users u ON r.student_id = u.id WHERE r.status = 'pending' ORDER BY r.reserved_at DESC")->fetchAll();
}

// ============================================================================
// TRANSACTION FUNCTIONS (Issue / Return)
// ============================================================================
function issueBook($pdo, $reservationId, $librarianId) {
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND status = 'pending'");
    $stmt->execute([$reservationId]);
    $reservation = $stmt->fetch();
    if (!$reservation) return ['success' => false, 'message' => 'Reservation not found or already processed'];

    $borrowDays = (int)getSetting($pdo, 'default_borrow_days', 14);
    $issueDate = date('Y-m-d');
    $dueDate = date('Y-m-d', strtotime("+{$borrowDays} days"));

    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO transactions (student_id, book_id, librarian_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, ?, 'issued')")
            ->execute([$reservation['student_id'], $reservation['book_id'], $librarianId, $issueDate, $dueDate]);
        $pdo->prepare("UPDATE reservations SET status = 'completed' WHERE id = ?")->execute([$reservationId]);
        $pdo->prepare("UPDATE books SET status = 'issued' WHERE id = ?")->execute([$reservation['book_id']]);

        $book = getBookById($pdo, $reservation['book_id']);
        createNotification($pdo, "Book '{$book['title']}' has been issued to you. Due: " . date('M d, Y', strtotime($dueDate)), 'issue', 'student', $reservation['student_id'], 'fas fa-book-open', 'my_books.php');

        $pdo->commit();
        return ['success' => true, 'message' => 'Book issued successfully'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Failed to issue book'];
    }
}

function returnBook($pdo, $transactionId, $librarianId = null) {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'issued'");
    $stmt->execute([$transactionId]);
    $transaction = $stmt->fetch();
    if (!$transaction) return ['success' => false, 'message' => 'Transaction not found'];

    $returnDate = date('Y-m-d');
    $isOverdue = strtotime($returnDate) > strtotime($transaction['due_date']);
    $status = $isOverdue ? 'overdue' : 'returned';
    $fineAmount = 0;

    if ($isOverdue) {
        $daysOverdue = floor((strtotime($returnDate) - strtotime($transaction['due_date'])) / 86400);
        $finePerDay = (float)getSetting($pdo, 'fine_per_day', 10);
        $fineAmount = $daysOverdue * $finePerDay;
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE transactions SET return_date = ?, status = ?, fine_amount = ?, librarian_id = COALESCE(?, librarian_id) WHERE id = ?")
            ->execute([$returnDate, $status, $fineAmount, $librarianId, $transactionId]);
        $pdo->prepare("UPDATE books SET available_copies = available_copies + 1, status = 'available' WHERE id = ?")->execute([$transaction['book_id']]);

        if ($fineAmount > 0) {
            $pdo->prepare("INSERT INTO fines (transaction_id, student_id, amount, fine_date, status) VALUES (?, ?, ?, ?, 'pending')")
                ->execute([$transactionId, $transaction['student_id'], $fineAmount, $returnDate]);
            $currency = getSetting($pdo, 'currency_symbol', 'Rs.');
            createNotification($pdo, "Fine of {$currency} {$fineAmount} generated for overdue book return.", 'fine', 'student', $transaction['student_id'], 'fas fa-exclamation-triangle', 'my_books.php');
        }

        $book = getBookById($pdo, $transaction['book_id']);
        createNotification($pdo, "Book '{$book['title']}' has been returned.", 'return', 'student', $transaction['student_id'], 'fas fa-undo', 'my_books.php');

        $pdo->commit();
        return ['success' => true, 'message' => 'Book returned successfully' . ($fineAmount > 0 ? ". Fine: $fineAmount" : ''), 'fine' => $fineAmount];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Failed to return book'];
    }
}

function getAllTransactions($pdo) {
    return $pdo->query("SELECT t.*, b.title, b.author, u.name as student_name, u.role_number FROM transactions t JOIN books b ON t.book_id = b.id JOIN users u ON t.student_id = u.id ORDER BY t.issue_date DESC")->fetchAll();
}

function getIssuedBooks($pdo) {
    return $pdo->query("SELECT t.*, b.title, b.author, u.name as student_name, u.role_number FROM transactions t JOIN books b ON t.book_id = b.id JOIN users u ON t.student_id = u.id WHERE t.status = 'issued' ORDER BY t.due_date ASC")->fetchAll();
}

// ============================================================================
// NOTIFICATION FUNCTIONS
// ============================================================================
function createNotification($pdo, $message, $type = 'general', $targetRole = 'all', $userId = null, $icon = 'fas fa-bell', $link = null) {
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, target_role, message, icon, link) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $type, $targetRole, $message, $icon, $link]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function getNotifications($pdo, $limit = 50) {
    if (isAdmin()) {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE target_role IN ('admin', 'all') ORDER BY created_at DESC LIMIT ?");
    } elseif (isLibrarian()) {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE target_role IN ('librarian', 'all') ORDER BY created_at DESC LIMIT ?");
    } elseif (isStudent()) {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE (target_role IN ('student', 'all') AND (user_id = ? OR user_id IS NULL)) ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } else {
        return [];
    }
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getUnreadNotificationCount($pdo) {
    if (!isLoggedIn()) return 0;
    try {
        if (isStudent()) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE is_read = FALSE AND (target_role IN ('student', 'all') AND (user_id = ? OR user_id IS NULL))");
            $stmt->execute([$_SESSION['user_id']]);
        } elseif (isLibrarian()) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = FALSE AND target_role IN ('librarian', 'all')");
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = FALSE AND target_role IN ('admin', 'all')");
        }
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function markNotificationRead($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ?");
    return $stmt->execute([$id]);
}

function markAllNotificationsRead($pdo) {
    if (isStudent()) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE target_role IN ('student', 'all') AND (user_id = ? OR user_id IS NULL)");
        return $stmt->execute([$_SESSION['user_id']]);
    } elseif (isLibrarian()) {
        return $pdo->exec("UPDATE notifications SET is_read = TRUE WHERE target_role IN ('librarian', 'all')");
    } else {
        return $pdo->exec("UPDATE notifications SET is_read = TRUE WHERE target_role IN ('admin', 'all')");
    }
}

// ============================================================================
// USER FUNCTIONS
// ============================================================================
function getAllUsers($pdo, $role = null) {
    if ($role) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
        $stmt->execute([$role]);
    } else {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    }
    return $stmt->fetchAll();
}

function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function addUser($pdo, $name, $email, $roleNumber, $department, $password, $role) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR role_number = ?");
    $stmt->execute([$email, $roleNumber]);
    if ($stmt->fetch()) return ['success' => false, 'message' => 'Email or Role Number already exists'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $cardId = ($role === 'student') ? 'LIB-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT) : null;
    $cardExpiry = ($role === 'student') ? date('Y-m-d', strtotime('+1 year')) : null;

    $insertStmt = $pdo->prepare("INSERT INTO users (name, email, role_number, department, password, role, card_id, card_expiry) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($insertStmt->execute([$name, $email, $roleNumber, $department, $hashedPassword, $role, $cardId, $cardExpiry])) {
        return ['success' => true, 'id' => $pdo->lastInsertId()];
    }
    return ['success' => false, 'message' => 'Failed to add user'];
}

function updateUserProfile($pdo, $userId, $data) {
    $fields = [];
    $values = [];
    $allowed = ['name', 'email', 'phone', 'address', 'department', 'profile_image'];
    foreach ($allowed as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $values[] = $data[$field];
        }
    }
    if (empty($fields)) return false;
    $values[] = $userId;
    return $pdo->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?")->execute($values);
}

function changePassword($pdo, $userId, $currentPassword, $newPassword) {
    $user = getUserById($pdo, $userId);
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $userId]);
    return ['success' => true, 'message' => 'Password changed successfully'];
}

function deleteUser($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    return $stmt->execute([$id]);
}

// ============================================================================
// STATS & ANALYTICS
// ============================================================================
function getSystemStats($pdo) {
    $stats = [];
    $stats['total_books'] = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $stats['total_copies'] = $pdo->query("SELECT COALESCE(SUM(total_copies), 0) FROM books")->fetchColumn();
    $stats['total_students'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $stats['total_librarians'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'librarian'")->fetchColumn();
    $stats['total_issued'] = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status = 'issued'")->fetchColumn();
    $stats['total_returned'] = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status = 'returned'")->fetchColumn();
    $stats['total_overdue'] = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status = 'issued' AND due_date < CURDATE()")->fetchColumn();
    $stats['total_reservations'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
    $stats['available_books'] = $pdo->query("SELECT COALESCE(SUM(available_copies), 0) FROM books")->fetchColumn();
    try {
        $stats['pending_fines'] = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM fines WHERE status = 'pending'")->fetchColumn();
        $stats['collected_fines'] = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM fines WHERE status = 'paid'")->fetchColumn();
    } catch (Exception $e) {
        $stats['pending_fines'] = 0;
        $stats['collected_fines'] = 0;
    }
    return $stats;
}

function getMonthlyChartData($pdo) {
    $data = ['labels' => [], 'issues' => [], 'returns' => []];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $label = date('M Y', strtotime("-$i months"));
        $data['labels'][] = $label;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE DATE_FORMAT(issue_date, '%Y-%m') = ?");
        $stmt->execute([$month]);
        $data['issues'][] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE DATE_FORMAT(return_date, '%Y-%m') = ?");
        $stmt->execute([$month]);
        $data['returns'][] = (int)$stmt->fetchColumn();
    }
    return $data;
}

function getPopularBooks($pdo, $limit = 5) {
    $stmt = $pdo->prepare("SELECT b.title, COUNT(t.id) as borrow_count FROM transactions t JOIN books b ON t.book_id = b.id GROUP BY t.book_id ORDER BY borrow_count DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getCategoryDistribution($pdo) {
    return $pdo->query("SELECT COALESCE(b.subject, 'Uncategorized') as category, COUNT(*) as count FROM books b GROUP BY b.subject ORDER BY count DESC LIMIT 8")->fetchAll();
}

// ============================================================================
// FINE FUNCTIONS
// ============================================================================
function getStudentFines($pdo, $studentId) {
    $stmt = $pdo->prepare("SELECT f.*, t.issue_date, t.due_date, t.return_date, b.title FROM fines f JOIN transactions t ON f.transaction_id = t.id JOIN books b ON t.book_id = b.id WHERE f.student_id = ? ORDER BY f.fine_date DESC");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

function getAllFines($pdo) {
    return $pdo->query("SELECT f.*, u.name as student_name, u.role_number, b.title, t.issue_date, t.due_date, t.return_date FROM fines f JOIN users u ON f.student_id = u.id JOIN transactions t ON f.transaction_id = t.id JOIN books b ON t.book_id = b.id ORDER BY f.fine_date DESC")->fetchAll();
}

function payFine($pdo, $fineId, $collectedBy) {
    $stmt = $pdo->prepare("UPDATE fines SET status = 'paid', paid_date = CURDATE(), collected_by = ? WHERE id = ?");
    return $stmt->execute([$collectedBy, $fineId]);
}

// ============================================================================
// SYSTEM LOGS
// ============================================================================
function logAction($userId, $action, $pdo, $details = null) {
    try {
        $ip = getClientIP();
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, ip_address, details) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $ip, $details]);
    } catch (Exception $e) {
        error_log("Failed to log action: " . $e->getMessage());
    }
}

function getSystemLogs($pdo, $limit = 100) {
    $stmt = $pdo->prepare("SELECT l.*, u.name as user_name, u.role FROM system_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.timestamp DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// ============================================================================
// LIBRARY CARD FUNCTIONS
// ============================================================================
function generateCardId() {
    return 'LIB-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function renewCard($pdo, $userId, $years = 1) {
    $newExpiry = date('Y-m-d', strtotime("+{$years} years"));
    $pdo->prepare("UPDATE users SET card_expiry = ?, card_status = 'active' WHERE id = ?")->execute([$newExpiry, $userId]);
    return true;
}

function disableCard($pdo, $userId) {
    $pdo->prepare("UPDATE users SET card_status = 'disabled' WHERE id = ?")->execute([$userId]);
    return true;
}

// ============================================================================
// SETTINGS
// ============================================================================
function updateSetting($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

function getAllSettings($pdo) {
    try {
        $rows = $pdo->query("SELECT * FROM settings ORDER BY setting_group, setting_key")->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row;
        }
        return $settings;
    } catch (Exception $e) {
        return [];
    }
}

// ============================================================================
// BOOK REQUESTS
// ============================================================================
function createBookRequest($pdo, $studentId, $title, $author, $isbn, $reason) {
    $stmt = $pdo->prepare("INSERT INTO book_requests (student_id, title, author, isbn, reason) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$studentId, $title, $author, $isbn, $reason]);
}

function getStudentBookRequests($pdo, $studentId) {
    $stmt = $pdo->prepare("SELECT * FROM book_requests WHERE student_id = ? ORDER BY created_at DESC");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

function getAllBookRequests($pdo) {
    return $pdo->query("SELECT br.*, u.name as student_name, u.role_number FROM book_requests br JOIN users u ON br.student_id = u.id ORDER BY br.created_at DESC")->fetchAll();
}

// ============================================================================
// FILE UPLOAD HELPER
// ============================================================================
function uploadImage($file, $directory, $prefix = 'img') {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) return null;
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) return null;
    if ($file['size'] > MAX_FILE_SIZE) return null;
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . uniqid() . '.' . $ext;
    $destination = $directory . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    return null;
}
