<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$studentId = $_SESSION['user_id'];
$reservations = getStudentReservations($pdo, $studentId);
$transactions = getStudentTransactions($pdo, $studentId);

$currentBorrows = array_filter($transactions, function($t) {
    return $t['status'] === 'issued';
});

$overdueBooks = array_filter($transactions, function($t) {
    return $t['status'] === 'issued' && strtotime($t['due_date']) < time();
});

$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - AliStack LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
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
        
        /* Sidebar - Modern Dark Theme */
        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #1e3a5f 0%, #0d253f 100%);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
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
        
        .nav-item {
            margin-bottom: 5px;
        }
        
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
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }
        
        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            flex: 1;
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
            align-items: center;
            justify-content: center;
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
            white-space: nowrap;
        }
        
        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
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
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(79,70,229,0.08);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(79,70,229,0.15);
            border-color: rgba(79,70,229,0.2);
        }
        
        .stat-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-card .icon.blue { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb; }
        .stat-card .icon.green { background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a; }
        .stat-card .icon.red { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626; }
        .stat-card .icon.purple { background: linear-gradient(135deg, #f3e8ff, #e9d5ff); color: #9333ea; }
        
        .stat-card .icon i {
            background: rgba(255,255,255,0.5);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
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
        
        /* Cards - Modern Design */
        .content-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 30px;
            border: 1px solid rgba(79,70,229,0.08);
            overflow: hidden;
        }
        
        .card-header-custom {
            padding: 20px 25px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #f8fafc, #fff);
        }
        
        .card-header-custom h5 {
            margin: 0;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
        }
        
        .card-header-custom h5 i {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .card-body-custom {
            padding: 25px;
        }
        
        /* Table */
        .custom-table {
            margin: 0;
        }
        
        .custom-table th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            color: #64748b;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 14px 20px;
        }
        
        .custom-table td {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        
        .custom-table tr:hover {
            background: #f8fafc;
        }
        
        /* Badges - Modern Style */
        .badge-custom {
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
        }
        
        .badge-success { background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a; }
        .badge-warning { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706; }
        .badge-danger { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626; }
        .badge-primary { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb; }
        
        /* Alert */
        .alert-custom {
            border: none;
            border-radius: 14px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .alert-danger { 
            background: linear-gradient(135deg, #fee2e2, #fecaca); 
            color: #dc2626; 
        }
        .alert-success { 
            background: linear-gradient(135deg, #dcfce7, #bbf7d0); 
            color: #16a34a; 
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 15px;
        }
        
        .empty-state h5 { color: #64748b; margin-bottom: 5px; }
        .empty-state p { color: #94a3b8; margin-bottom: 20px; }
        
        /* Search Box */
        .search-box {
            background: #fff;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .search-box .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px;
        }
        
        .search-box .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79,70,229,0.1);
        }
        
        .search-box .btn {
            border-radius: 10px;
            padding: 14px 25px;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 992px) {
            .sidebar { width: 240px; }
            .main-content { margin-left: 240px; }
            .page-header h1 { font-size: 1.6rem; }
        }
        
        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .sidebar {
                width: 280px;
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }
            
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
            
            .stats-row { 
                grid-template-columns: 1fr; 
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px;
                flex-direction: row;
                text-align: left;
            }
            
            .stat-card .icon {
                width: 50px;
                height: 50px;
            }
            
            .stat-card .info h3 {
                font-size: 1.5rem;
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
            
            .content-card {
                padding: 15px;
                border-radius: 12px;
            }
            
            .card-header-custom {
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .card-body-custom {
                padding: 15px;
            }
            
            .custom-table {
                display: block;
                overflow-x: auto;
            }
            
            .custom-table th,
            .custom-table td {
                padding: 12px 10px;
                font-size: 0.85rem;
            }
            
            .search-box {
                padding: 15px;
            }
            
            .search-box .form-control,
            .search-box .form-select {
                padding: 12px;
            }
            
            .empty-state {
                padding: 30px 15px;
            }
            
            .empty-state i {
                font-size: 2.5rem;
            }
            
            .alert-custom {
                padding: 12px 15px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            body {
                font-size: 13px;
            }
            
            .main-content {
                padding: 15px 10px;
            }
            
            .page-header h1 {
                font-size: 1.1rem;
            }
            
            .welcome-badge {
                font-size: 0.7rem;
                padding: 5px 10px;
            }
            
            .stat-card {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            
            .stat-card .icon {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }
            
            .stat-card .info h3 {
                font-size: 1.3rem;
            }
            
            .btn {
                padding: 8px 14px;
                font-size: 0.85rem;
            }
            
            .badge-custom {
                padding: 4px 8px;
                font-size: 0.7rem;
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

<!-- Sidebar -->
<nav class="sidebar">
    <a href="../index.php" class="sidebar-brand">
        <div class="icon"><i class="fas fa-book-reader"></i></div>
        <div class="text">AliStack <span>LMS</span></div>
    </a>
    
    <div class="nav">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
        <div class="nav-item">
            <a href="search.php" class="nav-link">
                <i class="fas fa-search"></i> Search Books
            </a>
        </div>
        <div class="nav-item">
            <a href="my_books.php" class="nav-link">
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

<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <div>
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! 👋</h1>
        </div>
        <span class="welcome-badge"><i class="fas fa-user-graduate me-2"></i>Student Portal</span>
    </div>
    
    <?php if (!empty($overdueBooks)): ?>
        <div class="alert alert-custom alert-danger mb-4">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Attention!</strong> You have <strong><?php echo count($overdueBooks); ?></strong> overdue book(s). Please return them as soon as possible.
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($message): ?>
        <div class="alert alert-custom alert-success mb-4">
            <i class="fas fa-check-circle"></i><?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-custom alert-danger mb-4">
            <i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="icon blue"><i class="fas fa-book"></i></div>
            <div class="info">
                <h3><?php echo count($currentBorrows); ?></h3>
                <p>Currently Borrowed</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon green"><i class="fas fa-calendar-check"></i></div>
            <div class="info">
                <h3>
                    <?php 
                    $pendingCount = count(array_filter($reservations, function($r) { return $r['status'] === 'pending'; }));
                    echo $pendingCount;
                    ?>
                </h3>
                <p>Active Reservations</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="info">
                <h3><?php echo count($overdueBooks); ?></h3>
                <p>Overdue Books</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon purple"><i class="fas fa-history"></i></div>
            <div class="info">
                <h3><?php echo count($transactions); ?></h3>
                <p>Total Borrowed</p>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Borrowed Books -->
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-book"></i> Currently Borrowed Books</h5>
                    <a href="my_books.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body-custom">
                    <?php if (empty($currentBorrows)): ?>
                        <div class="empty-state">
                            <i class="fas fa-book-open"></i>
                            <h5>No Books Borrowed</h5>
                            <p>You don't have any borrowed books.</p>
                            <a href="search.php" class="btn btn-primary">Search Books</a>
                        </div>
                    <?php else: ?>
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($currentBorrows, 5) as $book): ?>
                                    <?php $isOverdue = strtotime($book['due_date']) < time(); ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($book['author']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($book['due_date'])); ?>
                                            <?php if ($isOverdue): ?>
                                                <br><small class="text-danger">Overdue!</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge-custom badge-<?php echo $isOverdue ? 'danger' : 'warning'; ?>">
                                                <?php echo $isOverdue ? 'Overdue' : 'Issued'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Reservations -->
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-calendar"></i> My Reservations</h5>
                    <a href="my_books.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body-custom">
                    <?php 
                    $pendingReservations = array_filter($reservations, function($r) { return $r['status'] === 'pending'; });
                    if (empty($pendingReservations)): 
                    ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-check"></i>
                            <h5>No Reservations</h5>
                            <p>You don't have any active reservations.</p>
                            <a href="search.php" class="btn btn-primary">Reserve a Book</a>
                        </div>
                    <?php else: ?>
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Reserved</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($pendingReservations, 5) as $res): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($res['title']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($res['author']); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($res['reserved_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger cancel-reservation" data-id="<?php echo $res['id']; ?>">Cancel</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Search -->
    <div class="search-box">
        <h5 class="mb-3"><i class="fas fa-search me-2 text-primary"></i>Quick Search Books</h5>
        <form action="search.php" method="GET" class="d-flex gap-2">
            <input type="text" class="form-control" name="search" placeholder="Search by title, author, subject or ISBN...">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-2"></i>Search</button>
        </form>
    </div>
</main>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cancel Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this reservation?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep It</button>
                <button type="button" class="btn btn-danger" id="confirmCancel">Yes, Cancel</button>
            </div>
        </div>
    </div>
</div>

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
    const mobileToggle = document.querySelector('.mobile-toggle');
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !toggle?.contains(e.target) && !mobileToggle?.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});

let reservationId = null;
const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));

document.querySelectorAll('.cancel-reservation').forEach(btn => {
    btn.addEventListener('click', function() {
        reservationId = this.dataset.id;
        cancelModal.show();
    });
});

document.getElementById('confirmCancel').addEventListener('click', function() {
    if (reservationId) {
        window.location.href = 'cancel_reservation.php?id=' + reservationId;
    }
});

// Toggle sidebar on mobile
document.addEventListener('DOMContentLoaded', function() {
    // Add mobile toggle functionality if needed
});
</script>

</body>
</html>
