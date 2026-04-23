<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$studentId = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$books = getAllBooks($pdo, $search);

$userReservations = getStudentReservations($pdo, $studentId);
$reservedBookIds = array_column(array_filter($userReservations, function($r) { return $r['status'] === 'pending'; }), 'book_id');

$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Books - AliStack LMS</title>
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
            margin-left: 270px;
            padding: 30px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
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
        
        .content-card {
            background: #fff;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .content-card .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .search-box .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px;
        }
        
        .search-box .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79,70,229,0.1);
        }
        
        .search-box .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            padding: 14px 25px;
            border-radius: 10px;
            font-weight: 500;
        }
        
        .search-box .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca, #6d28d9);
        }
        
        .book-card {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 18px;
            transition: all 0.3s ease;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .book-card:hover {
            border-color: #4f46e5;
            box-shadow: 0 8px 25px rgba(79,70,229,0.15);
            transform: translateY(-2px);
        }
        
        .book-card .book-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
        }
        
        .book-card .book-author {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 12px;
        }
        
        .book-card .book-meta {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            align-items: center;
            font-size: 0.85rem;
        }
        
        .badge-available { background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a; }
        .badge-reserved { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706; }
        .badge-unavailable { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626; }
        
        .btn-reserve {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            color: #fff;
            padding: 10px 22px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(79,70,229,0.3);
        }
        
        .btn-reserve:hover {
            background: linear-gradient(135deg, #4338ca, #6d28d9);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79,70,229,0.4);
        }
        
        .btn-reserved {
            background: #f1f5f9;
            border: none;
            color: #64748b;
            padding: 10px 22px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
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
        
        .alert-custom {
            border: none;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success-custom { background: #dcfce7; color: #16a34a; }
        .alert-danger-custom { background: #fee2e2; color: #dc2626; }
        
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
            .content-card {
                padding: 15px;
                border-radius: 12px;
            }
            .book-card {
                padding: 15px;
                border-radius: 12px;
            }
            .book-card .book-title {
                font-size: 1rem;
            }
            .book-card .book-meta {
                gap: 10px;
                font-size: 0.8rem;
            }
            .search-box .row {
                gap: 10px;
            }
            .search-box .col-md-5,
            .search-box .col-md-4,
            .search-box .col-md-3 {
                width: 100%;
            }
            .book-card .d-flex {
                flex-direction: column;
                gap: 12px !important;
            }
            .book-card .d-flex > div:last-child {
                justify-content: flex-start;
            }
        }
        
        @media (max-width: 576px) {
            body { font-size: 13px; }
            .main-content { padding: 15px 10px; }
            .page-header h1 { font-size: 1.1rem; }
            .welcome-badge { font-size: 0.7rem; }
            .btn-reserve, .btn-reserved {
                padding: 8px 14px;
                font-size: 0.8rem;
            }
            .badge-available, .badge-reserved, .badge-unavailable {
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

<!-- Sidebar -->
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
            <a href="search.php" class="nav-link active">
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
            <h1><i class="fas fa-search me-2"></i>Search Books</h1>
        </div>
        <span class="welcome-badge"><i class="fas fa-user-graduate me-2"></i>Student Portal</span>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-custom alert-success-custom mb-4">
            <i class="fas fa-check-circle"></i><?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-custom alert-danger-custom mb-4">
            <i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="content-card search-box">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-medium">Search Term</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-2 border-end-0 border-secondary-subtle">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-2 border-start-0" name="search" placeholder="Title, author, ISBN..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Category</label>
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php 
                    $categories = $pdo->query("SELECT DISTINCT subject FROM books WHERE subject IS NOT NULL ORDER BY subject")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($categories as $cat): 
                    ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i> Search
                </button>
            </div>
        </form>
    </div>
    
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="card-title mb-0">
                <i class="fas fa-list text-primary"></i> Search Results
            </div>
            <span class="badge bg-primary fs-6"><?php echo count($books); ?> books found</span>
        </div>
        
        <?php 
        $filteredBooks = $books;
        if ($category) {
            $filteredBooks = array_filter($books, function($b) use ($category) {
                return $b['subject'] === $category;
            });
        }
        ?>
        
        <?php if (empty($filteredBooks)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h5>No books found</h5>
                <p>Try different search terms or browse all books.</p>
            </div>
        <?php else: ?>
            <?php foreach ($filteredBooks as $book): ?>
                <div class="book-card">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                            <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                            <div class="book-meta">
                                <span class="text-muted"><i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($book['subject'] ?? 'N/A'); ?></span>
                                <span class="text-muted"><i class="fas fa-barcode me-1"></i><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></span>
                                <span class="text-muted"><i class="fas fa-copy me-1"></i><?php echo $book['available_copies']; ?>/<?php echo $book['total_copies']; ?> copies</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <?php if ($book['status'] === 'available'): ?>
                                <?php if (in_array($book['id'], $reservedBookIds)): ?>
                                    <span class="badge badge-reserved fs-6"><i class="fas fa-clock me-1"></i>Reserved</span>
                                    <button class="btn btn-reserved" disabled>
                                        <i class="fas fa-check me-1"></i> Reserved
                                    </button>
                                <?php else: ?>
                                    <span class="badge badge-available fs-6"><i class="fas fa-check-circle me-1"></i>Available</span>
                                    <button class="btn btn-reserve" onclick="reserveBook(<?php echo $book['id']; ?>, '<?php echo htmlspecialchars($book['title']); ?>')">
                                        <i class="fas fa-bookmark me-1"></i> Reserve
                                    </button>
                                <?php endif; ?>
                            <?php elseif ($book['status'] === 'reserved'): ?>
                                <span class="badge badge-reserved fs-6"><i class="fas fa-clock me-1"></i>Reserved</span>
                                <button class="btn btn-reserved" disabled>
                                    <i class="fas fa-ban me-1"></i> Not Available
                                </button>
                            <?php else: ?>
                                <span class="badge badge-unavailable fs-6"><i class="fas fa-times-circle me-1"></i>Issued</span>
                                <button class="btn btn-reserved" disabled>
                                    <i class="fas fa-ban me-1"></i> Not Available
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="reserveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-bookmark text-primary me-2"></i>Confirm Reservation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Do you want to reserve this book?</p>
                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                            <i class="fas fa-book text-primary fa-lg"></i>
                        </div>
                        <div>
                            <strong id="reserveBookTitle" class="d-block"></strong>
                            <small class="text-muted">Please pick up within 48 hours</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmReserve" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none;">
                    <i class="fas fa-check me-1"></i> Confirm Reservation
                </button>
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
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});

let bookId = null;
const reserveModal = new bootstrap.Modal(document.getElementById('reserveModal'));

function reserveBook(id, title) {
    bookId = id;
    document.getElementById('reserveBookTitle').textContent = title;
    reserveModal.show();
}

document.getElementById('confirmReserve').addEventListener('click', function() {
    if (bookId) {
        window.location.href = 'reserve_book.php?id=' + bookId;
    }
});
</script>

</body>
</html>
