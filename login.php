<?php 
require_once 'config/database.php';
require_once 'includes/auth.php';

$error = '';
$success = '';
$selectedRole = $_GET['role'] ?? 'student';

if (isLoggedIn()) {
    $role = getUserRole();
    redirectToDashboard($role);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !checkCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $loginRole = $_POST['login_role'] ?? 'student';
        
        $result = loginUser($email, $password, $pdo);
        
        if ($result['success']) {
            if ($loginRole !== 'all' && $result['role'] !== $loginRole) {
                $error = 'Invalid credentials for ' . ucfirst($loginRole) . '. Please use correct credentials.';
            } else {
                redirectToDashboard($result['role']);
            }
        } else {
            $error = $result['message'];
        }
    }
}

$_SESSION['csrf_token'] = generateToken();

function redirectToDashboard($role) {
    if ($role === 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($role === 'librarian') {
        header('Location: librarian/dashboard.php');
    } else {
        header('Location: student/dashboard.php');
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AliStack Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 950px;
        }
        
        .login-card {
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
        }
        
        /* Left Side - Role Selection */
        .login-left {
            background: linear-gradient(160deg, #1e3a5f 0%, #0d253f 100%);
            padding: 45px 35px;
            color: #fff;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at top left, rgba(79, 70, 229, 0.4) 0%, transparent 50%),
                radial-gradient(ellipse at bottom right, rgba(236, 72, 153, 0.3) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .login-left > * {
            position: relative;
            z-index: 1;
        }
        
        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 25px;
            transition: all 0.3s;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            width: fit-content;
        }
        
        .back-home:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }
        
        .login-left h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .login-left .subtitle {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 35px;
            font-size: 1rem;
        }
        
        .role-cards {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .role-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .role-card:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateX(8px);
        }
        
        .role-card.active {
            background: rgba(255, 255, 255, 0.25);
            border-color: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        
        .role-card .icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        
        .role-card.active .icon {
            background: #fff;
            color: #1e3a5f;
        }
        
        .role-card h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .role-card small {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
        }
        
        /* Right Side - Login Form */
        .login-right {
            padding: 45px 40px;
            background: #fff;
        }
        
        .login-right .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 35px;
        }
        
        .login-right .brand-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #1e3a5f, #0d253f);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
        }
        
        .login-right .brand-text {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .login-right .brand-text span {
            color: #1e3a5f;
        }
        
        .login-right h3 {
            font-weight: 700;
            margin-bottom: 8px;
            color: #1e293b;
        }
        
        .login-right .subtitle {
            color: #64748b;
            margin-bottom: 30px;
        }
        
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #1e3a5f;
            box-shadow: 0 0 0 4px rgba(30, 58, 95, 0.1);
        }
        
        .input-group-text {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #64748b;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #1e3a5f, #0d253f);
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(30, 58, 95, 0.3);
        }
        
        .register-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
        }
        
        .register-link a {
            color: #1e3a5f;
            font-weight: 600;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 12px;
            padding: 16px;
        }
        
        .demo-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-top: 25px;
        }
        
        .demo-box h6 {
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            text-align: center;
        }
        
        .demo-credentials {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .demo-badge {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .demo-email {
            background: #e2e8f0;
            color: #475569;
        }
        
        .demo-pass {
            background: #1e3a5f;
            color: #fff;
        }
        
        @media (max-width: 768px) {
            .login-left {
                padding: 30px 20px;
            }
            
            .login-right {
                padding: 30px 20px;
            }
            
            .login-left h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="row g-0">
            <!-- Left Side - Role Selection -->
            <div class="col-lg-5">
                <div class="login-left">
                    <a href="index.php" class="back-home">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                    
                    <h2>Welcome Back!</h2>
                    <p class="subtitle">Select your role to continue</p>
                    
                    <div class="role-cards">
                        <div class="role-card <?php echo $selectedRole === 'student' ? 'active' : ''; ?>" onclick="selectRole('student')">
                            <div class="icon"><i class="fas fa-user-graduate"></i></div>
                            <div>
                                <h5>Student</h5>
                                <small>Access your books & reservations</small>
                            </div>
                        </div>
                        
                        <div class="role-card <?php echo $selectedRole === 'librarian' ? 'active' : ''; ?>" onclick="selectRole('librarian')">
                            <div class="icon"><i class="fas fa-book-reader"></i></div>
                            <div>
                                <h5>Librarian</h5>
                                <small>Manage books & transactions</small>
                            </div>
                        </div>
                        
                        <div class="role-card <?php echo $selectedRole === 'admin' ? 'active' : ''; ?>" onclick="selectRole('admin')">
                            <div class="icon"><i class="fas fa-user-shield"></i></div>
                            <div>
                                <h5>Administrator</h5>
                                <small>Full system control</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="col-lg-7">
                <div class="login-right">
                    <div class="brand">
                        <div class="brand-icon">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div class="brand-text">AliStack <span>LMS</span></div>
                    </div>
                    
                    <h3>Login as <?php echo ucfirst($selectedRole); ?></h3>
                    <p class="subtitle">Enter your credentials to continue</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger fade-in">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="loginForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="login_role" value="<?php echo $selectedRole; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <?php if ($selectedRole === 'student'): ?>
                                    <i class="fas fa-id-card me-1"></i> Role Number / Email
                                <?php elseif ($selectedRole === 'librarian'): ?>
                                    <i class="fas fa-user me-1"></i> Username / Email
                                <?php else: ?>
                                    <i class="fas fa-user-shield me-1"></i> Admin Email
                                <?php endif; ?>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <?php if ($selectedRole === 'student'): ?>
                                        <i class="fas fa-id-card"></i>
                                    <?php elseif ($selectedRole === 'librarian'): ?>
                                        <i class="fas fa-user"></i>
                                    <?php else: ?>
                                        <i class="fas fa-user-shield"></i>
                                    <?php endif; ?>
                                </span>
                                <input type="text" class="form-control" name="email" required 
                                       placeholder="<?php echo $selectedRole === 'student' ? 'Enter role number or email' : 'Enter email'; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-lock me-1"></i> Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" name="password" required placeholder="Enter password">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login text-white">
                            <i class="fas fa-sign-in-alt me-2"></i> Login as <?php echo ucfirst($selectedRole); ?>
                        </button>
                    </form>
                    
                    <?php if ($selectedRole === 'student'): ?>
                        <div class="register-link">
                            <p class="text-muted mb-0">Don't have an account? 
                                <a href="register.php">Register as Student</a>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="demo-box">
                        <h6>Demo Credentials</h6>
                        <div class="demo-credentials">
                            <?php if ($selectedRole === 'admin'): ?>
                                <span class="demo-badge demo-email">admin@alistack.com</span>
                            <?php elseif ($selectedRole === 'librarian'): ?>
                                <span class="demo-badge demo-email">librarian@alistack.com</span>
                            <?php else: ?>
                                <span class="demo-badge demo-email">john.doe@student.com</span>
                            <?php endif; ?>
                            <span class="demo-badge demo-pass">password</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectRole(role) {
    window.location.href = 'login.php?role=' + role;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
