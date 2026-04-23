<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$studentId = $_SESSION['user_id'];
$transactions = getStudentTransactions($pdo, $studentId);
$reservations = getStudentReservations($pdo, $studentId);

$borrowedBooks = array_filter($transactions, function($t) {
    return $t['status'] === 'issued';
});

$returnedBooks = array_filter($transactions, function($t) {
    return $t['status'] === 'returned' || $t['status'] === 'overdue';
});

$pendingReservations = array_filter($reservations, function($r) {
    return $r['status'] === 'pending';
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Books - AliStack LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        
        body {
            background: #f0f4f8;
            min-height: 100vh;
        }
        
        /* Student Top Bar */
        .student-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1001;
            margin-left: 270px;
        }
        
        .student-topbar .topbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .student-topbar .mobile-toggle {
            display: none;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            color: #fff;
            width: 38px;
            height: 38px;
            border-radius: 8px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }
        
        .student-topbar .topbar-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .student-topbar .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .student-topbar .notification-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #f1f5f9;
            border-radius: 10px;
            color: #64748b;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .student-topbar .notification-btn:hover {
            background: #4f46e5;
            color: #fff;
        }
        
        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #1e3a5f 0%, #0d253f 100%);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar.close {
            transform: translateX(-100%);
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 10px;
            margin-bottom: 30px;
            text-decoration: none;
        }
        
        .sidebar-brand .icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.3rem;
        }
        
        .sidebar-brand .text {
            color: #fff;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .sidebar-brand .text span { color: #4f46e5; }
        
        .nav-item { margin-bottom: 5px; }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 15px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        .nav-link i { width: 20px; }
        
        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }
        
        .user-card {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .user-card .avatar {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
        }
        
        .user-card .info .name {
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-card .info .role {
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px;
            background: rgba(239,68,68,0.2);
            color: #fca5a5;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: #ef4444;
            color: #fff;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .menu-toggle {
            display: none;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            color: #fff;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(79,70,229,0.3);
            transition: all 0.3s ease;
        }
        
        .menu-toggle:hover {
            background: linear-gradient(135deg, #4338ca, #6d28d9);
            transform: scale(1.05);
        }
        
        .welcome-badge {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-card .icon {
            width: 55px;
            height: 55px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        
        .stat-card .icon.blue { background: #dbeafe; color: #2563eb; }
        .stat-card .icon.green { background: #dcfce7; color: #16a34a; }
        .stat-card .icon.orange { background: #ffedd5; color: #ea580c; }
        
        .stat-card .info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .stat-card .info p {
            color: #64748b;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .content-card {
            background: #fff;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .nav-tabs-custom {
            border: none;
            gap: 12px;
            margin-bottom: 28px;
        }
        
        .nav-tabs-custom .nav-link {
            border: 2px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            border-radius: 12px;
            padding: 14px 28px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .nav-tabs-custom .nav-link:hover {
            border-color: #4f46e5;
            color: #4f46e5;
            transform: translateY(-2px);
        }
        
        .nav-tabs-custom .nav-link.active {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 4px 15px rgba(79,70,229,0.35);
        }
        
        .book-item {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 18px;
            transition: all 0.3s ease;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .book-item:hover {
            border-color: #4f46e5;
            box-shadow: 0 8px 25px rgba(79,70,229,0.15);
            transform: translateY(-2px);
        }
        
        .book-item .book-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
        }
        
        .book-item .book-author {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 14px;
        }
        
        .book-item .book-meta {
            display: flex;
            gap: 22px;
            flex-wrap: wrap;
            font-size: 0.85rem;
            color: #64748b;
        }
        
        .book-item .book-meta i {
            width: 20px;
            color: #94a3b8;
        }
        
        .badge-issued { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706; }
        .badge-returned { background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a; }
        .badge-overdue { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626; }
        .badge-pending { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb; }
        .badge-cancelled { background: #f1f5f9; color: #64748b; }
        
        .btn-cancel {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border: none;
            color: #dc2626;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #dc2626;
            color: #fff;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }
        
        .empty-state h5 { color: #64748b; margin-bottom: 8px; }
        .empty-state p { color: #94a3b8; }
        
        @media (max-width: 992px) {
            .sidebar { width: 240px; }
            .main-content { margin-left: 240px; }
            .page-header h1 { font-size: 1.6rem; }
        }
        
        @media (max-width: 768px) {
            .student-topbar {
                margin-left: 0;
                padding: 12px 15px;
            }
            
            .student-topbar .mobile-toggle {
                display: flex;
            }
            
            .student-topbar .topbar-title {
                font-size: 1rem;
            }
            
            .menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .sidebar {
                width: 280px;
                transform: translateX(-100%);
            }
            
            .sidebar.active { transform: translateX(0); }
            .main-content { 
                margin-left: 0; 
                padding: 20px 15px;
            }
            .page-header { 
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                flex-wrap: wrap;
            }
            .page-header h1 { 
                font-size: 1.3rem; 
                order: 2;
            }
            .welcome-badge {
                font-size: 0.75rem;
                padding: 6px 12px;
                order: 3;
            }
            .menu-toggle {
                order: 1;
                width: 38px;
                height: 38px;
            }
            .stats-row { 
                grid-template-columns: 1fr; 
                gap: 15px;
            }
            .stat-card {
                padding: 18px;
                flex-direction: row;
                text-align: left;
            }
            .stat-card .icon {
                width: 45px;
                height: 45px;
            }
            .stat-card .info h3 {
                font-size: 1.4rem;
            }
            .content-card {
                padding: 15px;
                border-radius: 12px;
            }
            .nav-tabs-custom {
                flex-wrap: nowrap;
                overflow-x: auto;
                gap: 8px;
                padding-bottom: 10px;
            }
            .nav-tabs-custom .nav-link {
                padding: 10px 16px;
                font-size: 0.85rem;
                white-space: nowrap;
            }
            .book-item {
                padding: 15px;
                border-radius: 12px;
            }
            .book-item .book-title {
                font-size: 1rem;
            }
            .book-item .book-meta {
                gap: 12px;
                font-size: 0.8rem;
            }
            .book-item .d-flex {
                flex-direction: column;
                gap: 12px !important;
            }
        }
        
        @media (max-width: 576px) {
            body { font-size: 13px; }
            .main-content { padding: 15px 10px; }
            .page-header h1 { font-size: 1.1rem; }
            .welcome-badge { font-size: 0.7rem; }
            .stat-card {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            .stat-card .icon {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
            }
            .stat-card .info h3 {
                font-size: 1.3rem;
            }
            .btn-cancel {
                padding: 8px 14px;
                font-size: 0.8rem;
            }
            .badge-issued, .badge-returned, .badge-overdue, .badge-pending {
                font-size: 0.75rem;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body>

    <!-- Student Top Bar -->
    <div class="student-topbar">
        <div class="topbar-left">
            <button class="mobile-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <span class="topbar-title">Student Portal</span>
        </div>
        <div class="topbar-right">
            <a href="notifications.php" class="notification-btn">
                <i class="fas fa-bell"></i>
            </a>
        </div>
    </div>

<nav class="sidebar">
    <a href="../index.php" class="sidebar-brand">
        <div class="icon"><i class="fas fa-book-reader"></i></div>
        <div class="text">AliStack <span>LMS</span></div>
    </a>
    
    <div class="nav">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
        <div class="nav-item">
            <a href="search.php" class="nav-link">
                <i class="fas fa-search"></i> Search Books
            </a>
        </div>
        <div class="nav-item">
            <a href="my_books.php" class="nav-link active">
                <i class="fas fa-book"></i> My Books
            </a>
        </div>
    </div>
    
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="avatar"><i class="fas fa-user"></i></div>
            <div class="info">
                <div class="name"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
                <div class="role">Student</div>
            </div>
        </div>
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-book me-2"></i>My Books</h1>
        </div>
        <span class="welcome-badge"><i class="fas fa-user-graduate me-2"></i>Student Portal</span>
    </div>
    
    <div class="stats-row">
        <div class="stat-card">
            <div class="icon blue"><i class="fas fa-book-reader"></i></div>
            <div class="info">
                <h3><?php echo count($borrowedBooks); ?></h3>
                <p>Currently Borrowed</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon green"><i class="fas fa-check-circle"></i></div>
            <div class="info">
                <h3><?php echo count($returnedBooks); ?></h3>
                <p>Books Returned</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon orange"><i class="fas fa-calendar-check"></i></div>
            <div class="info">
                <h3><?php echo count($pendingReservations); ?></h3>
                <p>Active Reservations</p>
            </div>
        </div>
    </div>
    
    <ul class="nav nav-tabs-custom" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#borrowed">
                <i class="fas fa-book-reader me-2"></i>Borrowed Books
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#history">
                <i class="fas fa-history me-2"></i>History
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reservations">
                <i class="fas fa-calendar-check me-2"></i>Reservations
            </button>
        </li>
    </ul>
    
    <div class="tab-content">
        <div class="tab-pane fade show active" id="borrowed">
            <div class="content-card">
                <?php if (empty($borrowedBooks)): ?>
                    <div class="empty-state">
                        <i class="fas fa-book-reader"></i>
                        <h5>No borrowed books</h5>
                        <p>You don't have any books currently borrowed.</p>
                        <a href="search.php" class="btn btn-primary mt-2" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none;">
                            <i class="fas fa-search me-2"></i>Browse Books
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($borrowedBooks as $book): ?>
                        <div class="book-item">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                    <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                                    <div class="book-meta">
                                        <span><i class="fas fa-calendar me-2"></i>Issued: <?php echo date('M d, Y', strtotime($book['issue_date'])); ?></span>
                                        <span><i class="fas fa-calendar-alt me-2"></i>Due: <?php echo date('M d, Y', strtotime($book['due_date'])); ?></span>
                                    </div>
                                </div>
                                <div>
                                    <?php if (strtotime($book['due_date']) < time()): ?>
                                        <span class="badge badge-overdue fs-6"><i class="fas fa-exclamation-triangle me-1"></i>Overdue</span>
                                    <?php else: ?>
                                        <span class="badge badge-issued fs-6"><i class="fas fa-clock me-1"></i>Issued</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="tab-pane fade" id="history">
            <div class="content-card">
                <?php if (empty($transactions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h5>No borrowing history</h5>
                        <p>Your borrowing history will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($transactions as $book): ?>
                        <div class="book-item">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                    <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                                    <div class="book-meta">
                                        <span><i class="fas fa-calendar me-2"></i>Issued: <?php echo date('M d, Y', strtotime($book['issue_date'])); ?></span>
                                        <span><i class="fas fa-calendar-check me-2"></i>Returned: <?php echo $book['return_date'] ? date('M d, Y', strtotime($book['return_date'])) : '-'; ?></span>
                                    </div>
                                </div>
                                <span class="badge badge-<?php echo $book['status'] === 'returned' ? 'returned' : 'overdue'; ?> fs-6">
                                    <i class="fas fa-<?php echo $book['status'] === 'returned' ? 'check' : 'exclamation'; ?>-circle me-1"></i>
                                    <?php echo ucfirst($book['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="tab-pane fade" id="reservations">
            <div class="content-card">
                <?php if (empty($reservations)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <h5>No reservations</h5>
                        <p>You don't have any book reservations.</p>
                        <a href="search.php" class="btn btn-primary mt-2" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none;">
                            <i class="fas fa-search me-2"></i>Browse Books
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($reservations as $res): ?>
                        <div class="book-item">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <div class="book-title"><?php echo htmlspecialchars($res['title']); ?></div>
                                    <div class="book-author">by <?php echo htmlspecialchars($res['author']); ?></div>
                                    <div class="book-meta">
                                        <span><i class="fas fa-calendar me-2"></i>Reserved: <?php echo date('M d, Y', strtotime($res['reserved_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-<?php 
                                        echo $res['status'] === 'pending' ? 'pending' : ($res['status'] === 'completed' ? 'returned' : 'cancelled'); 
                                    ?> fs-6">
                                        <i class="fas fa-<?php 
                                            echo $res['status'] === 'pending' ? 'clock' : ($res['status'] === 'completed' ? 'check' : 'times'); 
                                        ?>-circle me-1"></i>
                                        <?php echo ucfirst($res['status']); ?>
                                    </span>
                                    <?php if ($res['status'] === 'pending'): ?>
                                        <a href="cancel_reservation.php?id=<?php echo $res['id']; ?>" 
                                           class="btn btn-cancel"
                                           onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.querySelector('.menu-toggle');
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>

</body>
</html>
