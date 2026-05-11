<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function getPageTitle() {
    $page = basename($_SERVER['PHP_SELF'], '.php');
    $titles = [
        'index' => 'Home', 'login' => 'Login', 'register' => 'Register',
        'dashboard' => 'Dashboard', 'search' => 'Search Books', 'my_books' => 'My Books',
        'users' => 'User Management', 'reports' => 'Reports & Analytics', 'books' => 'Book Inventory',
        'issue_book' => 'Issue Book', 'return_book' => 'Return Book', 'reservations' => 'Reservations',
        'logs' => 'System Logs', 'settings' => 'Settings', 'profile' => 'My Profile',
        'cards' => 'Library Cards', 'fines' => 'Fine Management', 'card' => 'My Library Card',
        'requests' => 'Book Requests', 'notifications' => 'Notifications', 'scan' => 'Card Scanner'
    ];
    return $titles[$page] ?? 'AliStack Digital Library';
}

$isDashboardPage = false;
$currentPage = basename($_SERVER['PHP_SELF']);
if (isLoggedIn()) {
    $dashboardPages = ['dashboard.php','search.php','my_books.php','users.php','reports.php',
        'books.php','issue_book.php','return_book.php','reservations.php','logs.php',
        'settings.php','profile.php','cards.php','fines.php','card.php','requests.php',
        'notifications.php','scan.php'];
    $isDashboardPage = in_array($currentPage, $dashboardPages);
}

$currentUser = isLoggedIn() ? getCurrentUser($pdo) : null;
$unreadCount = isLoggedIn() ? getUnreadNotificationCount($pdo) : 0;
$userInitial = $currentUser ? getUserInitial($currentUser['name']) : 'U';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AliStack Digital Library - Oxford-Style Academic Library Management System">
    <title><?php echo getPageTitle(); ?> — AliStack Digital Library</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Enhanced Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- App CSS -->
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

<div class="app-wrapper">
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <nav id="sidebar" class="app-sidebar">
        <div class="sidebar-header">
            <a href="../index.php" class="sidebar-brand">
                <div class="brand-logo">
                    <i class="fas fa-university"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-name">AliStack</span>
                    <span class="brand-sub">Digital Library</span>
                </div>
            </a>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-label">Main Navigation</div>
            <ul class="nav flex-column">
                <?php if (isStudent()): ?>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-th-large"></i><span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="search.php" class="nav-link <?php echo $currentPage == 'search.php' ? 'active' : ''; ?>">
                            <i class="fas fa-search"></i><span>Search Books</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="my_books.php" class="nav-link <?php echo $currentPage == 'my_books.php' ? 'active' : ''; ?>">
                            <i class="fas fa-book-open"></i><span>My Books</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="card.php" class="nav-link <?php echo $currentPage == 'card.php' ? 'active' : ''; ?>">
                            <i class="fas fa-id-card"></i><span>Library Card</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="requests.php" class="nav-link <?php echo $currentPage == 'requests.php' ? 'active' : ''; ?>">
                            <i class="fas fa-paper-plane"></i><span>Book Requests</span>
                        </a>
                    </li>
                    
                <?php elseif (isLibrarian()): ?>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-th-large"></i><span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="issue_book.php" class="nav-link <?php echo $currentPage == 'issue_book.php' ? 'active' : ''; ?>">
                            <i class="fas fa-book-open"></i><span>Issue Book</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="return_book.php" class="nav-link <?php echo $currentPage == 'return_book.php' ? 'active' : ''; ?>">
                            <i class="fas fa-undo-alt"></i><span>Return Book</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="books.php" class="nav-link <?php echo $currentPage == 'books.php' ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i><span>Books</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reservations.php" class="nav-link <?php echo $currentPage == 'reservations.php' ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-check"></i><span>Reservations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="fines.php" class="nav-link <?php echo $currentPage == 'fines.php' ? 'active' : ''; ?>">
                            <i class="fas fa-coins"></i><span>Fines</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="scan.php" class="nav-link <?php echo $currentPage == 'scan.php' ? 'active' : ''; ?>">
                            <i class="fas fa-qrcode"></i><span>Card Scanner</span>
                        </a>
                    </li>
                    
                <?php elseif (isAdmin()): ?>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-th-large"></i><span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link <?php echo $currentPage == 'users.php' ? 'active' : ''; ?>">
                            <i class="fas fa-users-cog"></i><span>Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="books.php" class="nav-link <?php echo $currentPage == 'books.php' ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i><span>Books</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="cards.php" class="nav-link <?php echo $currentPage == 'cards.php' ? 'active' : ''; ?>">
                            <i class="fas fa-id-card"></i><span>Library Cards</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="fines.php" class="nav-link <?php echo $currentPage == 'fines.php' ? 'active' : ''; ?>">
                            <i class="fas fa-coins"></i><span>Fines</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reports.php" class="nav-link <?php echo $currentPage == 'reports.php' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar"></i><span>Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logs.php" class="nav-link <?php echo $currentPage == 'logs.php' ? 'active' : ''; ?>">
                            <i class="fas fa-history"></i><span>System Logs</span>
                        </a>
                    </li>
                    
                    <div class="menu-label mt-3">System</div>
                    <li class="nav-item">
                        <a href="settings.php" class="nav-link <?php echo $currentPage == 'settings.php' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i><span>Settings</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">
                    <?php if (!empty($currentUser['profile_image'])): ?>
                        <img src="../uploads/profiles/<?php echo $currentUser['profile_image']; ?>" alt="Profile">
                    <?php else: ?>
                        <span class="avatar-initial"><?php echo $userInitial; ?></span>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
            <div class="sidebar-actions">
                <a href="profile.php" class="btn-sidebar-action" title="Profile">
                    <i class="fas fa-user-circle"></i>
                </a>
                <a href="../logout.php" class="btn-sidebar-action logout" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Area -->
    <main class="app-main">
        <!-- Top Header Bar -->
        <header class="app-header">
            <div class="header-left">
                <button class="btn-sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title-area">
                    <h1 class="page-title"><?php echo getPageTitle(); ?></h1>
                    <nav class="breadcrumb-nav">
                        <span><i class="fas fa-home"></i> <?php echo ucfirst($_SESSION['role']); ?></span>
                        <span class="sep">/</span>
                        <span class="current"><?php echo getPageTitle(); ?></span>
                    </nav>
                </div>
            </div>
            <div class="header-right">
                <!-- Notification Bell -->
                <div class="header-notification dropdown">
                    <a href="#" class="notification-trigger" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-badge"><?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                        <div class="notif-header">
                            <h6>Notifications</h6>
                            <a href="#" onclick="markAllRead()" class="mark-read-btn">Mark all read</a>
                        </div>
                        <div class="notif-list" id="notificationList">
                            <?php 
                            $headerNotifs = getNotifications($pdo, 5);
                            if (!empty($headerNotifs)): 
                                foreach ($headerNotifs as $notif): ?>
                                <div class="notif-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                                    <div class="notif-icon">
                                        <i class="<?php echo $notif['icon'] ?? 'fas fa-bell'; ?>"></i>
                                    </div>
                                    <div class="notif-content">
                                        <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                        <small><?php echo timeAgo($notif['created_at']); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; 
                            else: ?>
                                <div class="notif-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No notifications</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="notif-footer">
                            <a href="notifications.php">View All Notifications</a>
                        </div>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="header-user dropdown">
                    <a href="#" class="user-trigger" data-bs-toggle="dropdown">
                        <div class="header-avatar">
                            <?php if (!empty($currentUser['profile_image'])): ?>
                                <img src="../uploads/profiles/<?php echo $currentUser['profile_image']; ?>" alt="">
                            <?php else: ?>
                                <span><?php echo $userInitial; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="header-user-info d-none d-md-block">
                            <span class="header-user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                            <span class="header-user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                        </div>
                        <i class="fas fa-chevron-down ms-1 d-none d-md-inline"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end user-dropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>My Profile</a></li>
                        <?php if (isStudent()): ?>
                        <li><a class="dropdown-item" href="card.php"><i class="fas fa-id-card me-2"></i>Library Card</a></li>
                        <?php endif; ?>
                        <?php if (isAdmin()): ?>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>
        
        <div class="app-content">

<?php else: ?>

    <!-- Public Page Navbar -->
    <nav class="navbar navbar-expand-lg public-navbar" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <div class="brand-logo"><i class="fas fa-university"></i></div>
                <div class="brand-text">
                    <span class="brand-name">AliStack</span>
                    <span class="brand-sub">Digital Library</span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                    <?php if (!isLoggedIn()): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-oxford" href="register.php">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    <?php else: ?>
                        <?php 
                        $dashUrl = isAdmin() ? 'admin/dashboard.php' : (isLibrarian() ? 'librarian/dashboard.php' : 'student/dashboard.php');
                        ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $dashUrl; ?>">Dashboard</a></li>
                        <li class="nav-item">
                            <a class="btn btn-oxford-outline" href="logout.php">
                                Logout (<?php echo htmlspecialchars($_SESSION['name']); ?>)
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="public-main">
    
<?php endif; ?>
