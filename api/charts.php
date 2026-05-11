<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'monthly':
        echo json_encode(getMonthlyChartData($pdo));
        break;
        
    case 'popular':
        echo json_encode(getPopularBooks($pdo, 8));
        break;
        
    case 'categories':
        echo json_encode(getCategoryDistribution($pdo));
        break;
        
    case 'stats':
        echo json_encode(getSystemStats($pdo));
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
