<?php
require_once __DIR__ . '/../config/database.php';

function getAllBooks($pdo, $search = '') {
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE (title LIKE ? OR author LIKE ? OR subject LIKE ? OR isbn LIKE ?)");
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    } else {
        $stmt = $pdo->query("SELECT * FROM books ORDER BY title");
    }
    return $stmt->fetchAll();
}

function getBookById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function isBookAvailable($pdo, $bookId) {
    $book = getBookById($pdo, $bookId);
    return $book && $book['available_copies'] > 0 && $book['status'] === 'available';
}

function hasActiveReservation($pdo, $bookId, $studentId) {
    $stmt = $pdo->prepare("SELECT id FROM reservations WHERE book_id = ? AND student_id = ? AND status = 'pending'");
    $stmt->execute([$bookId, $studentId]);
    return $stmt->fetch();
}

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
        
        $notificationStmt = $pdo->prepare("INSERT INTO notifications (type, message, reservation_id) VALUES (?, ?, ?)");
        $message = "Student {$student['name']} (Role No: {$student['role_number']}) reserved book: {$book['title']}";
        $notificationStmt->execute(['reservation', $message, $reservationId]);
        
        // Update book availability - decrement copies and update status if needed
        $updateBook = $pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
        $updateBook->execute([$bookId]);
        
        // Update status based on available copies
        $updatedBook = getBookById($pdo, $bookId);
        if ($updatedBook['available_copies'] == 0) {
            $statusUpdate = $pdo->prepare("UPDATE books SET status = 'reserved' WHERE id = ?");
            $statusUpdate->execute([$bookId]);
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
    
    if (!$reservation) {
        return ['success' => false, 'message' => 'Reservation not found'];
    }
    
    $pdo->beginTransaction();
    
    try {
        $updateRes = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
        $updateRes->execute([$reservationId]);
        
        $updateBook = $pdo->prepare("UPDATE books SET available_copies = available_copies + 1, status = 'available' WHERE id = ?");
        $updateBook->execute([$reservation['book_id']]);
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Reservation cancelled'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Failed to cancel reservation'];
    }
}

function getStudentReservations($pdo, $studentId) {
    $stmt = $pdo->prepare("
        SELECT r.*, b.title, b.author 
        FROM reservations r 
        JOIN books b ON r.book_id = b.id 
        WHERE r.student_id = ? 
        ORDER BY r.reserved_at DESC
    ");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

function getStudentTransactions($pdo, $studentId) {
    $stmt = $pdo->prepare("
        SELECT t.*, b.title, b.author 
        FROM transactions t 
        JOIN books b ON t.book_id = b.id 
        WHERE t.student_id = ? 
        ORDER BY t.issue_date DESC
    ");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

function getAllReservations($pdo) {
    $stmt = $pdo->query("
        SELECT r.*, b.title, b.author, u.name as student_name, u.role_number 
        FROM reservations r 
        JOIN books b ON r.book_id = b.id 
        JOIN users u ON r.student_id = u.id 
        ORDER BY r.reserved_at DESC
    ");
    return $stmt->fetchAll();
}

function getPendingReservations($pdo) {
    $stmt = $pdo->query("
        SELECT r.*, b.title, b.author, u.name as student_name, u.role_number 
        FROM reservations r 
        JOIN books b ON r.book_id = b.id 
        JOIN users u ON r.student_id = u.id 
        WHERE r.status = 'pending'
        ORDER BY r.reserved_at DESC
    ");
    return $stmt->fetchAll();
}

function issueBook($pdo, $reservationId, $librarianId) {
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND status = 'pending'");
    $stmt->execute([$reservationId]);
    $reservation = $stmt->fetch();
    
    if (!$reservation) {
        return ['success' => false, 'message' => 'Reservation not found or already processed'];
    }
    
    $issueDate = date('Y-m-d');
    $dueDate = date('Y-m-d', strtotime('+14 days'));
    
    $pdo->beginTransaction();
    
    try {
        $transStmt = $pdo->prepare("INSERT INTO transactions (student_id, book_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, 'issued')");
        $transStmt->execute([$reservation['student_id'], $reservation['book_id'], $issueDate, $dueDate]);
        
        $updateRes = $pdo->prepare("UPDATE reservations SET status = 'completed' WHERE id = ?");
        $updateRes->execute([$reservationId]);
        
        // Note: available_copies was already decremented in reserveBook(), just update status
        $updateBook = $pdo->prepare("UPDATE books SET status = 'issued' WHERE id = ?");
        $updateBook->execute([$reservation['book_id']]);
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Book issued successfully'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Failed to issue book'];
    }
}

function returnBook($pdo, $transactionId) {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'issued'");
    $stmt->execute([$transactionId]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        return ['success' => false, 'message' => 'Transaction not found'];
    }
    
    $returnDate = date('Y-m-d');
    $status = strtotime($returnDate) > strtotime($transaction['due_date']) ? 'overdue' : 'returned';
    
    $pdo->beginTransaction();
    
    try {
        $updateTrans = $pdo->prepare("UPDATE transactions SET return_date = ?, status = ? WHERE id = ?");
        $updateTrans->execute([$returnDate, $status, $transactionId]);
        
        $updateBook = $pdo->prepare("UPDATE books SET available_copies = available_copies + 1, status = 'available' WHERE id = ?");
        $updateBook->execute([$transaction['book_id']]);
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Book returned successfully'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Failed to return book'];
    }
}

function getAllTransactions($pdo) {
    $stmt = $pdo->query("
        SELECT t.*, b.title, b.author, u.name as student_name, u.role_number 
        FROM transactions t 
        JOIN books b ON t.book_id = b.id 
        JOIN users u ON t.student_id = u.id 
        ORDER BY t.issue_date DESC
    ");
    return $stmt->fetchAll();
}

function getIssuedBooks($pdo) {
    $stmt = $pdo->query("
        SELECT t.*, b.title, b.author, u.name as student_name, u.role_number 
        FROM transactions t 
        JOIN books b ON t.book_id = b.id 
        JOIN users u ON t.student_id = u.id 
        WHERE t.status = 'issued'
        ORDER BY t.due_date ASC
    ");
    return $stmt->fetchAll();
}

function getNotifications($pdo) {
    $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function markNotificationRead($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ?");
    return $stmt->execute([$id]);
}

function getUnreadNotificationCount($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = FALSE");
    return $stmt->fetchColumn();
}

function getAllUsers($pdo, $role = null) {
    if ($role) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
        $stmt->execute([$role]);
    } else {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    }
    return $stmt->fetchAll();
}

function getSystemStats($pdo) {
    $stats = [];
    
    $stats['total_books'] = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $stats['total_students'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $stats['total_librarians'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'librarian'")->fetchColumn();
    $stats['total_issued'] = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status = 'issued'")->fetchColumn();
    $stats['total_reservations'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
    $stats['available_books'] = $pdo->query("SELECT SUM(available_copies) FROM books")->fetchColumn();
    
    return $stats;
}

function addUser($pdo, $name, $email, $roleNumber, $department, $password, $role) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR role_number = ?");
    $stmt->execute([$email, $roleNumber]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email or Role Number already exists'];
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insertStmt = $pdo->prepare("INSERT INTO users (name, email, role_number, department, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($insertStmt->execute([$name, $email, $roleNumber, $department, $hashedPassword, $role])) {
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Failed to add user'];
}

function deleteUser($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    return $stmt->execute([$id]);
}

function addBook($pdo, $title, $author, $isbn, $subject, $copies) {
    $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, subject, total_copies, available_copies, status) VALUES (?, ?, ?, ?, ?, ?, 'available')");
    return $stmt->execute([$title, $author, $isbn, $subject, $copies, $copies]);
}

function updateBook($pdo, $id, $title, $author, $isbn, $subject, $copies) {
    $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, isbn = ?, subject = ?, total_copies = ?, available_copies = ? WHERE id = ?");
    return $stmt->execute([$title, $author, $isbn, $subject, $copies, $copies, $id]);
}

function deleteBook($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    return $stmt->execute([$id]);
}

function getSystemLogs($pdo, $limit = 100) {
    $stmt = $pdo->prepare("
        SELECT l.*, u.name as user_name, u.role 
        FROM system_logs l 
        LEFT JOIN users u ON l.user_id = u.id 
        ORDER BY l.timestamp DESC 
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
