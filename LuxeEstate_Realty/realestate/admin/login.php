<?php
/**
 * LuxeEstate Realty - Admin Login
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/functions.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please refresh and try again.';
    } else {
        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter your email and password.';
        } else {
            $result = adminLogin($email, $password);
            if ($result['success']) {
                header('Location: ' . ADMIN_URL . '/dashboard.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Generate token AFTER any POST handling so it's never overwritten before validation
$csrf = generateCSRF();

$siteName = getSetting('site_name', 'LuxeEstate Realty');
$logoUrl  = getSetting('logo') ? UPLOAD_URL . getSetting('logo') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | <?= htmlspecialchars($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    :root {
        --maroon: #800000; --maroon-dark: #5C0000;
        --gold: #C9A84C; --beige: #F5EFE6; --beige-light: #FAF6F0;
        --text: #2C1810; --text-muted: #6B5344;
        --gold-gradient: linear-gradient(135deg, #C9A84C, #E5C878, #C9A84C, #A07830);
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--maroon-dark);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background-image: linear-gradient(135deg, #3D0000 0%, #800000 50%, #5C0000 100%);
        position: relative;
        overflow: hidden;
    }
    body::before {
        content: '';
        position: fixed;
        width: 600px; height: 600px;
        border-radius: 50%;
        background: rgba(201,168,76,.08);
        top: -200px; right: -200px;
        pointer-events: none;
    }
    body::after {
        content: '';
        position: fixed;
        width: 400px; height: 400px;
        border-radius: 50%;
        background: rgba(201,168,76,.05);
        bottom: -100px; left: -100px;
        pointer-events: none;
    }
    .login-wrapper {
        width: 100%; max-width: 440px; padding: 20px;
        position: relative; z-index: 1;
    }
    .login-card {
        background: var(--beige-light);
        border-radius: 20px;
        padding: 48px 40px;
        box-shadow: 0 30px 80px rgba(0,0,0,.4);
    }
    .login-logo {
        text-align: center;
        margin-bottom: 32px;
    }
    .login-logo img { height: 50px; object-fit: contain; }
    .login-logo-text {
        font-family: 'Cormorant Garamond', serif;
        font-size: 26px;
        font-weight: 600;
        color: var(--maroon);
        display: block;
    }
    .login-logo-sub {
        font-size: 12px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--gold);
        margin-top: 2px;
    }
    .login-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 6px;
    }
    .login-subtitle {
        font-size: 14px;
        color: var(--text-muted);
        margin-bottom: 28px;
    }
    .form-group { margin-bottom: 18px; }
    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 6px;
        letter-spacing: .3px;
    }
    .input-wrap {
        position: relative;
    }
    .input-icon {
        position: absolute;
        left: 14px; top: 50%;
        transform: translateY(-50%);
        color: var(--gold);
        font-size: 14px;
    }
    .form-control {
        width: 100%;
        padding: 12px 14px 12px 40px;
        border: 1.5px solid #E2D5C3;
        border-radius: 10px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        color: var(--text);
        background: white;
        transition: border-color .2s, box-shadow .2s;
        outline: none;
    }
    .form-control:focus {
        border-color: var(--maroon);
        box-shadow: 0 0 0 3px rgba(128,0,0,.1);
    }
    .toggle-pw {
        position: absolute;
        right: 14px; top: 50%;
        transform: translateY(-50%);
        background: none; border: none;
        color: var(--text-muted);
        cursor: pointer; font-size: 14px;
        padding: 0;
    }
    .alert-error {
        background: #FFF0F0;
        border: 1px solid #FFCCCC;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: #C0392B;
    }
    .btn-login {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 10px;
        background: var(--gold-gradient);
        color: var(--maroon-dark);
        font-family: 'DM Sans', sans-serif;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: .5px;
        cursor: pointer;
        transition: opacity .2s, transform .2s;
        margin-top: 8px;
    }
    .btn-login:hover { opacity: .9; transform: translateY(-1px); }
    .login-footer {
        text-align: center;
        margin-top: 24px;
        font-size: 13px;
        color: var(--text-muted);
    }
    .login-footer a { color: var(--maroon); text-decoration: none; font-weight: 500; }
    .back-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 20px;
        font-size: 13px;
        color: rgba(255,255,255,.7);
        text-decoration: none;
        transition: color .2s;
    }
    .back-link:hover { color: var(--gold); }
</style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-logo">
            <?php if ($logoUrl): ?>
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($siteName) ?>">
            <?php else: ?>
            <span class="login-logo-text"><?= htmlspecialchars($siteName) ?></span>
            <span class="login-logo-sub">Admin Panel</span>
            <?php endif; ?>
        </div>

        <h2 class="login-title">Welcome Back</h2>
        <p class="login-subtitle">Sign in to manage your real estate platform</p>

        <?php if ($error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="admin@example.com" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="pwField" class="form-control"
                           placeholder="Enter your password" required>
                    <button type="button" class="toggle-pw" onclick="togglePw()">
                        <i class="fas fa-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Sign In to Dashboard
            </button>
        </form>

        <div class="login-footer">
            <i class="fas fa-shield-alt" style="color:var(--gold)"></i>
            Secured with 256-bit encryption
        </div>
    </div>
    <a href="<?= SITE_URL ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Website
    </a>
</div>
<script>
function togglePw() {
    const f = document.getElementById('pwField');
    const i = document.getElementById('pwIcon');
    if (f.type === 'password') { f.type = 'text'; i.className = 'fas fa-eye-slash'; }
    else { f.type = 'password'; i.className = 'fas fa-eye'; }
}
</script>
</body>
</html>
