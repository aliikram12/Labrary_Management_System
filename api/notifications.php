<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'count':
        $count = getUnreadNotificationCount($pdo);
        echo json_encode(['count' => (int)$count]);
        break;
        
    case 'list':
        $limit = (int)($_GET['limit'] ?? 10);
        $notifications = getNotifications($pdo, $limit);
        echo json_encode(['notifications' => $notifications]);
        break;
        
    case 'mark_read':
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id > 0) {
            markNotificationRead($pdo, $id);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Invalid ID']);
        }
        break;
        
    case 'mark_all_read':
        markAllNotificationsRead($pdo);
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
