<?php 
require_once 'config/database.php';
require_once 'includes/functions.php';

$searchResults = [];
$search = $_GET['search'] ?? '';

if ($search) {
    $searchResults = getAllBooks($pdo, $search);
}

$stats = getSystemStats($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AliStack Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        /* Navigation */
        .navbar {
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(10px);
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .brand-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        
        .brand-text {
            color: #fff;
        }
        
        .text-accent {
            color: #4f46e5;
        }
        
        .navbar-toggler {
            border: none;
            padding: 8px 12px;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            padding: 10px 15px !important;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 0 3px;
        }
        
        .nav-link:hover {
            color: #fff !important;
            background: rgba(79, 70, 229, 0.2);
        }
        
        .nav-link.active {
            color: #fff !important;
            background: rgba(79, 70, 229, 0.3);
        }
        
        .dropdown-menu {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            margin-top: 10px;
        }
        
        .dropdown-item {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: rgba(79, 70, 229, 0.3);
            color: #fff;
        }
        
        .dropdown-toggle::after {
            display: none;
        }
        
        .btn-nav {
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(79, 70, 229, 0.5);
            background: linear-gradient(135deg, #4338ca, #6d28d9);
        }
        
        /* Hero Section */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            /* Background image with overlay */
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.92) 0%, rgba(30, 58, 95, 0.88) 50%, rgba(15, 23, 42, 0.92) 100%), 
                        url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            overflow: hidden;
            padding-top: 80px;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(79, 70, 229, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(236, 72, 153, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(59, 130, 246, 0.2) 0%, transparent 40%);
            animation: gradientMove 15s ease infinite;
        }
        
        @keyframes gradientMove {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(5deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 4rem;
            font-weight: 900;
            line-height: 1.15;
            background: linear-gradient(135deg, #fff 0%, #e2e8f0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeInUp 0.8s ease;
            letter-spacing: -1px;
        }
        
        .hero p {
            font-size: 1.35rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 2.5rem;
            animation: fadeInUp 0.8s ease 0.2s both;
            line-height: 1.7;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .hero-buttons {
            animation: fadeInUp 0.8s ease 0.4s both;
        }
        
        .hero-buttons .btn {
            padding: 15px 35px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-glow {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.4);
        }
        
        .btn-glow:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(79, 70, 229, 0.6);
            background: linear-gradient(135deg, #4338ca, #6d28d9);
        }
        
        .btn-outline-light {
            border-width: 2px;
            border-radius: 50px;
        }
        
        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }
        
        /* Floating Books Animation */
        .floating-books {
            position: absolute;
            right: 5%;
            top: 50%;
            transform: translateY(-50%);
            width: 400px;
            height: 400px;
        }
        
        .book-float {
            position: absolute;
            background: linear-gradient(135deg, #fff 0%, #f1f5f9 100%);
            border-radius: 8px;
            padding: 15px 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: float 6s ease-in-out infinite;
        }
        
        .book-float:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .book-float:nth-child(2) { top: 40%; right: 10%; animation-delay: 1s; }
        .book-float:nth-child(3) { bottom: 20%; left: 20%; animation-delay: 2s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(-5deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        
        /* Search Box */
        .search-wrapper {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
            animation: fadeInUp 0.8s ease 0.6s both;
        }
        
        .search-wrapper h3 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .search-wrapper .form-control {
            border: none;
            border-radius: 50px;
            padding: 15px 25px;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .search-wrapper .btn {
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: 600;
        }
        
        .search-results {
            margin-top: 20px;
            max-height: 350px;
            overflow-y: auto;
        }
        
        .search-result-item {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            border: none;
        }
        
        .search-result-item:hover {
            transform: translateX(10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        /* Features Section */
        .features {
            padding: 100px 0;
            background: #fff;
        }
        
        .section-title {
            font-size: 2.75rem;
            font-weight: 800;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }
        
        .section-subtitle {
            color: #64748b;
            font-size: 1.15rem;
            font-weight: 400;
            margin-bottom: 3rem;
            line-height: 1.6;
        }
        
        /* Features Section */
        .features {
            padding: 100px 0;
            background: #f8fafc;
            position: relative;
            overflow: hidden;
        }
        
        .features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 10% 10%, rgba(79, 70, 229, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(236, 72, 153, 0.05) 0%, transparent 40%);
        }
        
        .features .container {
            position: relative;
            z-index: 2;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-header .badge {
            background: linear-gradient(135deg, #1e3a5f, #0d253f);
            color: #fff;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 15px;
            display: inline-block;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
        }
        
        .section-subtitle {
            color: #64748b;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .feature-box {
            background: #fff;
            border-radius: 20px;
            padding: 25px 20px;
            text-align: center;
            transition: all 0.4s ease;
            border: 1px solid #e2e8f0;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .feature-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1e3a5f, #0d253f);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        
        .feature-box:hover::before {
            transform: scaleX(1);
        }
        
        .feature-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(30, 58, 95, 0.12);
            border-color: #1e3a5f;
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .feature-box:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .feature-icon.blue {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e3a5f;
            box-shadow: 0 10px 30px rgba(30, 58, 95, 0.2);
        }
        
        .feature-icon.green {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #166534;
            box-shadow: 0 10px 30px rgba(22, 101, 52, 0.2);
        }
        
        .feature-icon.purple {
            background: linear-gradient(135deg, #f3e8ff, #e9d5ff);
            color: #7c3aed;
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.2);
        }
        
        .feature-icon.orange {
            background: linear-gradient(135deg, #ffedd5, #fed7aa);
            color: #c2410c;
            box-shadow: 0 10px 30px rgba(194, 65, 12, 0.2);
        }
        
        .feature-box h4 {
            font-weight: 600;
            margin-bottom: 8px;
            color: #1e293b;
            font-size: 1rem;
        }
        
        .feature-box p {
            color: #64748b;
            line-height: 1.5;
            font-size: 0.95rem;
            margin: 0;
        }
        
        .feature-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #1e3a5f;
            font-weight: 600;
            text-decoration: none;
            margin-top: 20px;
            font-size: 0.9rem;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        
        .feature-box:hover .feature-link {
            opacity: 1;
            transform: translateY(0);
        }
        
        .feature-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .features {
                padding: 60px 0;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .feature-box {
                padding: 30px 20px;
            }
            
            /* Hero Section Responsive */
            .hero {
                min-height: auto;
                padding: 120px 0 80px;
                background-attachment: scroll;
            }
            
            .hero h1 {
                font-size: 2.5rem;
                line-height: 1.2;
            }
            
            .hero p {
                font-size: 1.1rem;
                margin-bottom: 2rem;
            }
            
            .hero-buttons .btn {
                padding: 12px 25px;
                font-size: 0.95rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero h1 {
                font-size: 2rem;
                letter-spacing: -0.5px;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 1.75rem;
            }
            
            .stats-header h2 {
                font-size: 1.75rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
        
        /* Stats Section */
        .stats {
            padding: 80px 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            position: relative;
            overflow: hidden;
        }
        
        .stats::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 20% 20%, rgba(79, 70, 229, 0.3) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(236, 72, 153, 0.2) 0%, transparent 50%);
        }
        
        .stats .container {
            position: relative;
            z-index: 2;
        }
        
        .stats-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .stats-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
        }
        
        .stats-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 35px 25px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            color: #fff;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            font-weight: 500;
        }
        
        /* CTA Section */
        .cta {
            padding: 100px 0;
            background: #f8fafc;
            position: relative;
            overflow: hidden;
        }
        
        .cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 10% 90%, rgba(79, 70, 229, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 10%, rgba(236, 72, 153, 0.05) 0%, transparent 40%);
        }
        
        .cta .container {
            position: relative;
            z-index: 2;
        }
        
        .cta-card {
            background: linear-gradient(135deg, #1e3a5f 0%, #0d253f 100%);
            border-radius: 30px;
            padding: 70px 50px;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(30, 58, 95, 0.3);
        }
        
        .cta-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.4) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .cta-card::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.3) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .cta-card > * {
            position: relative;
            z-index: 2;
        }
        
        .cta-card .cta-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2rem;
        }
        
        .cta-card h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .cta-card p {
            font-size: 1.15rem;
            opacity: 0.85;
            margin-bottom: 35px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .cta-buttons .btn {
            padding: 16px 40px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .cta-buttons .btn-light {
            background: #fff;
            color: #1e3a5f;
            border: none;
        }
        
        .cta-buttons .btn-light:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .cta-buttons .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: #fff;
        }
        
        .cta-buttons .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #fff;
            transform: translateY(-3px);
        }
        
        @media (max-width: 768px) {
            .stats-header h2 {
                font-size: 2rem;
            }
            
            .stat-item {
                padding: 25px 20px;
            }
            
            .stat-number {
                font-size: 2.5rem;
            }
            
            .cta-card {
                padding: 50px 30px;
            }
            
            .cta-card h2 {
                font-size: 1.8rem;
            }
            
            .cta-buttons .btn {
                padding: 14px 30px;
            }
        }
        
        /* Footer */
        .main-footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 60px 0 30px;
        }
        
        .footer-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 20px;
        }
        
        .footer-brand i {
            color: #4f46e5;
        }
        
        .footer-link {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-link:hover {
            color: #4f46e5;
        }
        
        .footer-bottom {
            border-top: 1px solid #1e293b;
            padding-top: 30px;
            margin-top: 40px;
        }
        
        /* Footer Responsive */
        @media (max-width: 991px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .floating-books {
                display: none;
            }
            
            .hero {
                min-height: auto;
                padding: 100px 0 60px;
            }
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .cta-card {
                padding: 40px 20px;
            }
            
            .cta-card h2 {
                font-size: 1.8rem;
            }
            
            /* Footer Mobile */
            .main-footer {
                padding: 40px 0 25px;
            }
            
            .main-footer .col-lg-4, 
            .main-footer .col-lg-2 {
                margin-bottom: 30px;
            }
            
            .footer-brand {
                font-size: 1.3rem;
            }
            
            .main-footer h6 {
                font-size: 1rem;
                margin-bottom: 15px;
            }
            
            .main-footer ul li {
                margin-bottom: 10px;
            }
            
            .footer-bottom {
                margin-top: 30px;
                padding-top: 20px;
            }
            
            .footer-bottom p {
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-footer {
                padding: 35px 0 20px;
            }
            
            .footer-brand {
                font-size: 1.2rem;
            }
            
            .footer-bottom p {
                font-size: 0.8rem;
            }
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #4f46e5;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <span class="brand-icon"><i class="fas fa-book-reader"></i></span>
            <span class="brand-text">AliStack <span class="text-accent">LMS</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">
                        <i class="fas fa-user-plus me-1"></i> Register
                    </a>
                </li>
                <li class="nav-item ms-lg-3">
                    <a href="login.php" class="btn btn-primary btn-nav">
                        <i class="fas fa-sign-in-alt me-1"></i> Get Started
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-12 col-lg-8 hero-content">
                <h1 class="mb-4">AliStack Library<br>Management System</h1>
                <p>Your complete solution for managing library resources efficiently. Search, reserve, and borrow books with ease.</p>
                
                <div class="hero-buttons d-flex gap-3 flex-wrap justify-content-center">
                    <a href="register.php" class="btn btn-glow text-white">
                        <i class="fas fa-user-plus me-2"></i>Get Started
                    </a>
                    <a href="login.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
            </div>
            </div>
        </div>
    </div>
    
    <!-- Decorative Elements -->
        </div>
    </div>
    
    <!-- Decorative Elements -->
    <div class="floating-books d-none d-lg-block">
        <div class="book-float">
            <i class="fas fa-book fa-2x text-primary"></i>
        </div>
        <div class="book-float">
            <i class="fas fa-book-open fa-2x text-success"></i>
        </div>
        <div class="book-float">
            <i class="fas fa-library fa-2x text-warning"></i>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <div class="section-header">
            <span class="badge">Features</span>
            <h2 class="section-title">Amazing Features</h2>
            <p class="section-subtitle">Everything you need to manage your library efficiently with our powerful and easy-to-use system</p>
        </div>
        
        <div class="row g-3">
            <div class="col-md-6">
                <div class="feature-box">
                    <div class="feature-icon blue">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h4>Student Portal</h4>
                    <p>Students can easily search and reserve books online, track their borrowing history, and manage due dates.</p>
                    <a href="login.php?role=student" class="feature-link">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-box">
                    <div class="feature-icon green">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <h4>Librarian Tools</h4>
                    <p>Efficiently manage issue and return transactions, handle reservations, and update book inventory.</p>
                    <a href="login.php?role=librarian" class="feature-link">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-box">
                    <div class="feature-icon purple">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h4>Admin Dashboard</h4>
                    <p>Complete system control with user management, detailed reports generation, and comprehensive monitoring.</p>
                    <a href="login.php?role=admin" class="feature-link">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-box">
                    <div class="feature-icon orange">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h4>Notifications</h4>
                    <p>Stay informed with real-time notifications for reservations, due dates, and system updates.</p>
                    <a href="register.php" class="feature-link">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Developer Section -->
<section id="developer" class="section section-alt">
    <div class="section-inner">
        <div class="about-grid">
            <div class="scroll-reveal">
                <h2 class="section-title">About the Developer</h2>
                <p class="section-subtitle">
                    Hi, I'm <strong>Ali Ikram</strong>, a passionate Full-Stack Developer and UI/UX enthusiast. AliStack was born from my vision to revolutionize educational assessment through intelligent, AI-driven solutions.
                </p>
                <p class="section-subtitle">
                    With a deep understanding of standard web technologies and a focus on premium, modern aesthetics, I strive to build platforms that are not only extremely functional but visually striking and highly accessible.
                </p>
                <div class="developer-socials">
                    <a href="https://wa.me/923361711707" target="_blank" class="btn btn-whatsapp">
                        <i class="fab fa-whatsapp"></i> Let's Connect
                    </a>
                    <div class="social-links">
                        <a href="https://web.facebook.com/aliikram57" target="_blank" class="social-link social-facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.youtube.com/@AliStackOfficial" target="_blank" class="social-link social-youtube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="about-img scroll-reveal">
                <img src="assets/images/developer.png" alt="Ali Ikram - Full Stack Developer">
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats">
    <div class="container">
        <div class="stats-header">
            <h2>Library Statistics</h2>
            <p>Real-time overview of our library system</p>
        </div>
        <div class="row g-4">
            <div class="col-6 col-lg-3">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['total_books']; ?></div>
                    <div class="stat-label">Total Books</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-item">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['total_students']; ?></div>
                    <div class="stat-label">Students</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-item">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['total_issued']; ?></div>
                    <div class="stat-label">Books Issued</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-item">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['available_books']; ?></div>
                    <div class="stat-label">Available Books</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta-card">
            <div class="cta-icon">
                <i class="fas fa-rocket"></i>
            </div>
            <h2>Ready to Get Started?</h2>
            <p>Join our library system today and start borrowing books easily. It's free and only takes a minute!</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-light">
                    <i class="fas fa-user-plus me-2"></i>Register Now
                </a>
                <a href="login.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="main-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="footer-brand">
                    <i class="fas fa-book-reader me-2"></i>AliStack LMS
                </div>
                <p>A comprehensive library management system designed for modern libraries.</p>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="text-white mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="index.php" class="footer-link">Home</a></li>
                    <li class="mb-2"><a href="login.php" class="footer-link">Login</a></li>
                    <li class="mb-2"><a href="register.php" class="footer-link">Register</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="text-white mb-3">Roles</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="footer-link">Student</a></li>
                    <li class="mb-2"><a href="#" class="footer-link">Librarian</a></li>
                    <li class="mb-2"><a href="#" class="footer-link">Administrator</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-4">
                <h6 class="text-white mb-3">Contact</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-envelope me-2"></i>support@alistack.com</li>
                    <li class="mb-2"><i class="fas fa-phone me-2"></i>+1 234 567 890</li>
                    <li class="mb-2"><i class="fas fa-map-marker me-2"></i>Library Street, City</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom text-center">
            <p class="mb-0">&copy; 2026 AliStack Library Management System. All rights reserved. | Developed by Ali Stack</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
