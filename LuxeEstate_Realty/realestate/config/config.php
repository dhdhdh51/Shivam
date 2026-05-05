<?php
// =====================================================
// Database Configuration
// =====================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'realestate_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'http://localhost/realestate');
define('SITE_ROOT', dirname(__DIR__));
define('UPLOAD_DIR', SITE_ROOT . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('ADMIN_URL', SITE_URL . '/admin');

// Security
define('CSRF_TOKEN_EXPIRE', 3600); // 1 hour
define('SESSION_EXPIRE', 7200);     // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT', 900);       // 15 minutes

// Image Settings
define('MAX_FILE_SIZE', 5242880);   // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('THUMB_WIDTH', 800);
define('THUMB_HEIGHT', 600);

// Pagination
define('PROPERTIES_PER_PAGE', 9);
define('BLOGS_PER_PAGE', 6);
define('ADMIN_PER_PAGE', 15);
