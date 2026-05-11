<?php 
require_once 'config/database.php';
require_once 'includes/functions.php';

$stats = getSystemStats($pdo);
$basePath = '.';
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero d-flex align-items-center" style="min-height: 100vh; background: linear-gradient(135deg, rgba(26, 35, 50, 0.92), rgba(30, 58, 95, 0.88)), url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover fixed;">
    <div class="container text-center text-white" style="padding-top: 80px;" data-aos="fade-up">
        <h1 class="font-serif fw-800 mb-4" style="font-size: 3.5rem;">AliStack Digital Library</h1>
        <p class="lead mb-5 mx-auto" style="max-width: 600px; color: rgba(255,255,255,0.85);">
            Oxford-style premium library management. Search, reserve, and manage academic resources with our enterprise-grade digital platform.
        </p>
        
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="register.php" class="btn btn-gold btn-lg px-4 rounded-pill">
                <i class="fas fa-user-plus me-2"></i>Get Started
            </a>
            <a href="login.php" class="btn btn-outline-light btn-lg px-4 rounded-pill border-2">
                <i class="fas fa-sign-in-alt me-2"></i>Login to Portal
            </a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-white">
    <div class="container" data-aos="fade-up">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="p-4 rounded-4 shadow-sm" style="background: var(--gray-50); border: 1px solid var(--gray-200);">
                    <i class="fas fa-book fa-3x text-oxford mb-3"></i>
                    <h2 class="font-serif fw-bold m-0"><?php echo $stats['total_books']; ?></h2>
                    <p class="text-muted small m-0 text-uppercase fw-bold">Total Books</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-4 rounded-4 shadow-sm" style="background: var(--gray-50); border: 1px solid var(--gray-200);">
                    <i class="fas fa-user-graduate fa-3x mb-3" style="color: var(--success);"></i>
                    <h2 class="font-serif fw-bold m-0"><?php echo $stats['total_students']; ?></h2>
                    <p class="text-muted small m-0 text-uppercase fw-bold">Active Students</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-4 rounded-4 shadow-sm" style="background: var(--gray-50); border: 1px solid var(--gray-200);">
                    <i class="fas fa-book-reader fa-3x mb-3" style="color: var(--warning);"></i>
                    <h2 class="font-serif fw-bold m-0"><?php echo $stats['total_issued']; ?></h2>
                    <p class="text-muted small m-0 text-uppercase fw-bold">Books Borrowed</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-4 rounded-4 shadow-sm" style="background: var(--gray-50); border: 1px solid var(--gray-200);">
                    <i class="fas fa-check-circle fa-3x text-gold mb-3"></i>
                    <h2 class="font-serif fw-bold m-0"><?php echo $stats['available_books']; ?></h2>
                    <p class="text-muted small m-0 text-uppercase fw-bold">Available Now</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-oxford text-white">
    <div class="container py-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-gold text-dark px-3 py-2 rounded-pill mb-3">Premium Features</span>
            <h2 class="font-serif fw-bold display-5">Enterprise Management</h2>
            <p class="text-white-50 mx-auto" style="max-width: 600px;">Experience a seamless workflow across all roles with our state-of-the-art tools.</p>
        </div>
        
        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="glass p-4 rounded-4 h-100 text-center transition-hover">
                    <div class="bg-white text-oxford d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 70px; height: 70px; font-size: 1.5rem;">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h4 class="font-serif fw-bold">Smart Cards</h4>
                    <p class="text-white-50 small mb-0">Automated library card generation with printable formats and integrated barcode scanning capabilities.</p>
                </div>
            </div>
            <!-- Feature 2 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="glass p-4 rounded-4 h-100 text-center transition-hover">
                    <div class="bg-gold text-dark d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 70px; height: 70px; font-size: 1.5rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="font-serif fw-bold">Real-time Analytics</h4>
                    <p class="text-white-50 small mb-0">Track library usage, borrowing trends, and fine collections via interactive Chart.js dashboards.</p>
                </div>
            </div>
            <!-- Feature 3 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="glass p-4 rounded-4 h-100 text-center transition-hover">
                    <div class="bg-white text-oxford d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 70px; height: 70px; font-size: 1.5rem;">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h4 class="font-serif fw-bold">Instant Notifications</h4>
                    <p class="text-white-50 small mb-0">Automated alerts for due dates, reservations, and fine calculations via our AJAX polling system.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Developer -->
<section id="about" class="py-5 bg-white">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="font-serif fw-bold display-5 mb-4 text-oxford">About the System</h2>
                <p class="text-muted">
                    The AliStack Digital Library is a highly optimized, PHP 8+ PDO-driven application crafted by <strong>Ali Ikram</strong>. It replaces legacy systems with a modern <em>Oxford-style</em> UI, strictly enforcing Role-Based Access Control (RBAC).
                </p>
                <p class="text-muted mb-4">
                    Featuring glassmorphism design elements, AJAX notifications, DataTables, and SweetAlert2 integration, it sets a new standard in academic software.
                </p>
                <a href="https://wa.me/923361711707" target="_blank" class="btn btn-success rounded-pill px-4 py-2">
                    <i class="fab fa-whatsapp me-2"></i>Contact Developer
                </a>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="position-relative p-4 rounded-4" style="background: linear-gradient(135deg, var(--gray-100), var(--gray-200)); border: 1px solid var(--gray-300);">
                    <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-3 border-secondary">
                        <i class="fas fa-code fa-2x text-oxford"></i>
                        <h4 class="m-0 font-serif fw-bold text-dark">Tech Stack</h4>
                    </div>
                    <ul class="list-unstyled m-0 text-muted">
                        <li class="mb-2"><i class="fas fa-check-circle text-gold me-2"></i><strong>Backend:</strong> PHP 8.x, PDO MySQL, OOP Patterns</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-gold me-2"></i><strong>Frontend:</strong> Bootstrap 5, Vanilla JS, CSS3 Variables</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-gold me-2"></i><strong>Libraries:</strong> Chart.js, DataTables, SweetAlert2, AOS</li>
                        <li><i class="fas fa-check-circle text-gold me-2"></i><strong>Security:</strong> CSRF Protection, Password Hashing, RBAC</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.transition-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.transition-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
</style>

<?php require_once 'includes/footer.php'; ?>
