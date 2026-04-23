# AliStack Library Management System

A complete, fully functional Library Management System built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

- **Student Module**: Register, login, search books, reserve books, view borrowing history
- **Librarian Module**: Manage reservations, issue/return books, view notifications, manage inventory
- **Admin Module**: User management, system monitoring, reports and analytics

## Requirements

- XAMPP/WAMP/LAMP with PHP 7.4+
- MySQL 5.7+
- Web browser

## Installation

### 1. Setup Database

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `alistack_lms`
3. Import the `database.sql` file

### 2. Configure Database Connection

The database configuration is in `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'alistack_lms');
```

Update these values if your MySQL credentials are different.

### 3. Run the Application

1. Start Apache and MySQL in XAMPP/WAMP
2. Open browser and navigate to: `http://localhost/Library_Management_System/`

## Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@alistack.com | password |
| Librarian | librarian@alistack.com | password |
| Student | john.doe@student.com | password |
| Student | jane.smith@student.com | password |

## Project Structure

```
alistack_lms/
├── config/
│   └── database.php       # Database configuration
├── includes/
│   ├── header.php         # Common header
│   ├── footer.php         # Common footer
│   ├── auth.php           # Authentication functions
│   └── functions.php      # Core functions
├── admin/
│   ├── dashboard.php      # Admin dashboard
│   ├── users.php          # User management
│   └── reports.php        # Reports & analytics
├── librarian/
│   ├── dashboard.php      # Librarian dashboard
│   ├── issue_book.php     # Issue books
│   ├── return_book.php    # Return books
│   ├── books.php          # Book inventory
│   └── mark_notification.php
├── student/
│   ├── dashboard.php      # Student dashboard
│   ├── search.php         # Search books
│   ├── my_books.php       # My books
│   ├── reserve_book.php   # Reserve book
│   └── cancel_reservation.php
├── assets/
│   ├── css/style.css      # Styles
│   └── js/main.js         # JavaScript
├── index.php              # Landing page
├── login.php             # Login page
├── register.php           # Registration page
├── logout.php             # Logout page
└── database.sql           # Database schema with sample data
```

## Security Features

- Password hashing using PHP's `password_hash()`
- Prepared statements to prevent SQL injection
- Session-based authentication
- CSRF token validation on forms
- Input sanitization

## Default Password

All demo accounts use: `password`

## License

Developed by Ali Stack
