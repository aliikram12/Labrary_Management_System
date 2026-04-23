<?php
require_once __DIR__ . '/../config/database.php';

function getPageTitle() {
    $page = basename($_SERVER['PHP_SELF'], '.php');
    $titles = [
        'index' => 'Home',
        'login' => 'Login',
        'register' => 'Register',
        'dashboard' => 'Dashboard',
        'search' => 'Search Books',
        'my_books' => 'My Books',
        'users' => 'User Management',
        'reports' => 'Reports',
        'books' => 'Book Inventory',
        'issue_book' => 'Issue Book',
        'return_book' => 'Return Book',
        'reservations' => 'Reservations',
        'logs' => 'System Logs'
    ];
    return $titles[$page] ?? 'AliStack LMS';
}

function getNotificationCount($pdo) {
    if (!isLibrarian() && !isAdmin()) return 0;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE is_read = FALSE");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function getUserNotifications($pdo, $limit = 10) {
    if (!isLoggedIn()) return [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM notifications ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

$isDashboardPage = false;
if (isLoggedIn()) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $dashboardPages = ['dashboard.php', 'search.php', 'my_books.php', 'users.php', 'reports.php', 
        'books.php', 'issue_book.php', 'return_book.php', 'reservations.php', 'logs.php'];
    $isDashboardPage = in_array($currentPage, $dashboardPages);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getPageTitle(); ?> - AliStack Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php if (strpos($_SERVER['PHP_SELF'], 'admin/') !== false): ?>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <?php endif; ?>
    <?php if (strpos($_SERVER['PHP_SELF'], 'librarian/') !== false): ?>
    <link rel="stylesheet" href="../assets/css/librarian.css">
    <?php endif; ?>
</head>
<body>

<?php if ($isDashboardPage): ?>

<div class="wrapper">
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <a href="../index.php" class="brand">
                <i class="fas fa-book-reader"></i>
                <span>AliStack LMS</span>
            </a>
        </div>
        <ul class="nav flex-column">
            <?php if (isStudent()): ?>
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="search.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>">
                        <i class="fas fa-search"></i> Search Books
                    </a>
                </li>
                <li class="nav-item">
                    <a href="my_books.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_books.php' ? 'active' : ''; ?>">
                        <i class="fas fa-book"></i> My Books
                    </a>
                </li>
            <?php elseif (isLibrarian()): ?>
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="issue_book.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'issue_book.php' ? 'active' : ''; ?>">
                        <i class="fas fa-book-open"></i> Issue Book
                    </a>
                </li>
                <li class="nav-item">
                    <a href="return_book.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'return_book.php' ? 'active' : ''; ?>">
                        <i class="fas fa-undo"></i> Return Book
                    </a>
                </li>
                <li class="nav-item">
                    <a href="books.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>">
                        <i class="fas fa-book"></i> Books
                    </a>
                </li>
                <li class="nav-item">
                    <a href="reservations.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-check"></i> Reservations
                    </a>
                </li>
            <?php elseif (isAdmin()): ?>
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a href="books.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>">
                        <i class="fas fa-book"></i> Books
                    </a>
                </li>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logs.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : ''; ?>">
                        <i class="fas fa-history"></i> System Logs
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar">
                    <i class="fas fa-user-circle fa-2x"></i>
                </div>
                <div class="info">
                    <span class="name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <span class="role"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
            <a href="../logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
    
    <main class="main-content">
        <header class="top-header">
            <button class="btn-toggle" id="sidebarToggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-right">
                <?php if (isLibrarian() || isAdmin()): ?>
                    <?php $notifCount = getNotificationCount($pdo); ?>
                    <div class="notification-bell">
                        <a href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <?php if ($notifCount > 0): ?>
                                <span class="badge"><?php echo $notifCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notifications-dropdown">
                            <div class="dropdown-header">
                                <span>Notifications</span>
                            </div>
                            <?php 
                            $notifications = getUserNotifications($pdo, 5);
                            if (!empty($notifications)): 
                            ?>
                                <div class="notifications-list">
                                    <?php foreach ($notifications as $notif): ?>
                                        <div class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                                            <i class="fas fa-bell"></i>
                                            <div class="content">
                                                <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                                <small><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-3 text-muted">No notifications</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>
        <div class="content-wrapper">

<?php else: ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book-reader me-2"></i>AliStack LMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php else: ?>
                        <?php if (isStudent()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="student/dashboard.php">Dashboard</a>
                            </li>
                        <?php elseif (isLibrarian()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="librarian/dashboard.php">Dashboard</a>
                            </li>
                        <?php elseif (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Dashboard</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['name']); ?>)</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="public-main">
    
<?php endif; ?>
