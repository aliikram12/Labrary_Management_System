<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$query = sanitize($_GET['q'] ?? '');
$category = sanitize($_GET['category'] ?? '');

if (strlen($query) < 2 && empty($category)) {
    echo json_encode(['books' => []]);
    exit;
}

$sql = "SELECT b.*, c.name as category_name FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE 1=1";
$params = [];

if ($query) {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.subject LIKE ?)";
    $term = "%$query%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}

if ($category) {
    $sql .= " AND (b.subject = ? OR c.name = ?)";
    $params[] = $category;
    $params[] = $category;
}

$sql .= " ORDER BY b.title LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

echo json_encode(['books' => $books, 'count' => count($books)]);