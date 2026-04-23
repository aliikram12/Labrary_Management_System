<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$term = $_GET['q'] ?? '';

if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

$books = getAllBooks($pdo, $term);

$result = [];
foreach ($books as $book) {
    $result[] = [
        'id' => $book['id'],
        'title' => $book['title'],
        'author' => $book['author'],
        'subject' => $book['subject'],
        'isbn' => $book['isbn'],
        'status' => $book['status'],
        'available_copies' => $book['available_copies'],
        'total_copies' => $book['total_copies']
    ];
}

echo json_encode($result);
?>