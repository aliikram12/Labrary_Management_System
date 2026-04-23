<?php 
require_once 'config/database.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !checkCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $roleNumber = sanitize($_POST['role_number']);
        $department = sanitize($_POST['department']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $result = registerUser($name, $email, $roleNumber, $department, $password, $pdo);
            
            if ($result['success']) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = $result['message'];
            }
        }
    }
}

$_SESSION['csrf_token'] = generateToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AliStack Library Management System</title>
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
            padding: 30px 20px;
        }
        
        .register-container {
            width: 100%;
            max-width: 480px;
        }
        
        .register-card {
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.12);
        }
        
        /* Header */
        .register-header {
            background: linear-gradient(160deg, #1e3a5f 0%, #0d253f 100%);
            padding: 40px 30px;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        
        .register-header::before {
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
        
        .register-header > * {
            position: relative;
            z-index: 1;
        }
        
        .register-header .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .register-header .brand-icon {
            width: 65px;
            height: 65px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        
        .register-header h2 {
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 1.6rem;
        }
        
        .register-header p {
            opacity: 0.85;
            margin: 0;
        }
        
        /* Form Body */
        .register-body {
            padding: 35px 30px;
        }
        
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
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
        
        .btn-register {
            background: linear-gradient(135deg, #1e3a5f, #0d253f);
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            color: #fff;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(30, 58, 95, 0.3);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .login-link a {
            color: #1e3a5f;
            font-weight: 600;
            text-decoration: none;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: #1e3a5f;
        }
        
        .alert {
            border-radius: 12px;
            padding: 16px;
        }
        
        /* Password Hint */
        .password-hint {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 6px;
        }
        
        @media (max-width: 576px) {
            body {
                padding: 15px 10px;
            }
            
            .register-container {
                max-width: 100%;
            }
            
            .register-body {
                padding: 20px 15px;
            }
            
            .register-header {
                padding: 25px 15px;
            }
            
            .register-header .brand-icon {
                width: 50px;
                height: 50px;
                font-size: 1.4rem;
            }
            
            .register-header h2 {
                font-size: 1.3rem;
            }
            
            .form-label {
                font-size: 0.85rem;
            }
            
            .form-control, .form-select {
                padding: 12px 14px;
                font-size: 0.9rem;
            }
            
            .btn-register {
                padding: 14px;
                font-size: 0.95rem;
            }
        }
        
        @media (max-width: 380px) {
            .register-header h2 {
                font-size: 1.2rem;
            }
            
            .register-header p {
                font-size: 0.85rem;
            }
            
            .form-control, .form-select {
                padding: 11px 12px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="register-card">
        <div class="register-header">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
            <h2>Create Account</h2>
            <p>Register as a Student</p>
        </div>
        
        <div class="register-body">
            <?php if ($error): ?>
                <div class="alert alert-danger fade-in">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success fade-in">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <a href="login.php" class="btn btn-sm btn-success ms-2">Go to Login</a>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user me-1"></i> Full Name</label>
                    <input type="text" class="form-control" name="name" required placeholder="Enter your full name">
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-envelope me-1"></i> Email Address</label>
                    <input type="email" class="form-control" name="email" required placeholder="your.email@example.com">
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-id-card me-1"></i> Role Number (Student ID)</label>
                    <input type="text" class="form-control" name="role_number" required placeholder="e.g., STU001">
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-building me-1"></i> Department</label>
                    <input type="text" class="form-control" name="department" required placeholder="Enter your department">
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-lock me-1"></i> Password</label>
                    <input type="password" class="form-control" name="password" required minlength="6" placeholder="Create a password">
                    <p class="password-hint"><i class="fas fa-info-circle"></i> Must be at least 6 characters</p>
                </div>
                
                <div class="mb-4">
                    <label class="form-label"><i class="fas fa-lock me-1"></i> Confirm Password</label>
                    <input type="password" class="form-control" name="confirm_password" required placeholder="Confirm your password">
                </div>
                
                <button type="submit" class="btn btn-register">
                    <i class="fas fa-user-plus me-2"></i> Create Account
                </button>
            </form>
            
            <div class="login-link">
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
                <span class="text-muted">|</span>
                <p class="mb-0 text-muted">Already have an account?</p>
                <a href="login.php">Login</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
