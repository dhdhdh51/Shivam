<?php
/**
 * Admin Layout — included at top of every admin page.
 * Expects: $pageTitle, $activePage to be set before including.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/functions.php';

requireAdmin(); // Redirect to login if not authenticated

$siteName  = getSetting('site_name', 'LuxeEstate Realty');
$logoUrl   = getSetting('logo') ? UPLOAD_URL . getSetting('logo') : '';
$adminName = $_SESSION['admin_name'] ?? 'Admin';

// Quick stats for sidebar badges
$db = db();
$newLeadsCount = (int)$db->query("SELECT COUNT(*) FROM leads WHERE status='new'")->fetchColumn();
$totalProps    = (int)$db->query("SELECT COUNT(*) FROM properties WHERE status='active'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> | <?= htmlspecialchars($siteName) ?> Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
    --maroon: #800000; --maroon-dark: #5C0000; --maroon-light: #9B1C1C;
    --gold: #C9A84C; --gold-light: #E5C878;
    --gold-gradient: linear-gradient(135deg, #C9A84C, #E5C878, #C9A84C, #A07830);
    --beige: #F5EFE6; --beige-light: #FAF6F0; --beige-dark: #EDE3D5;
    --text: #2C1810; --text-muted: #6B5344;
    --sidebar-w: 260px;
    --header-h: 64px;
    --radius: 12px;
    --shadow: 0 2px 15px rgba(0,0,0,.08);
    --shadow-lg: 0 8px 30px rgba(0,0,0,.12);
    --transition: .2s ease;
    --success: #16A34A; --danger: #DC2626; --warning: #D97706; --info: #0369A1;
}
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'DM Sans',sans-serif; background:var(--beige-light); color:var(--text); line-height:1.6; }
a { text-decoration:none; color:inherit; }
img { max-width:100%; }
.admin-layout { display:flex; min-height:100vh; }

/* ── Sidebar ── */
.admin-sidebar {
    width: var(--sidebar-w);
    background: linear-gradient(180deg, var(--maroon-dark) 0%, var(--maroon) 60%, #6B0000 100%);
    position: fixed; top:0; left:0; bottom:0;
    z-index: 100; overflow-y:auto; overflow-x:hidden;
    transition: transform var(--transition);
    display:flex; flex-direction:column;
}
.sidebar-brand {
    padding: 22px 20px 18px;
    border-bottom: 1px solid rgba(255,255,255,.1);
    display:flex; align-items:center; gap:12px;
}
.sidebar-brand-logo img { height:36px; object-fit:contain; }
.sidebar-brand-text { font-family:'Cormorant Garamond',serif; font-size:18px; font-weight:600; color:white; }
.sidebar-brand-sub { font-size:10px; letter-spacing:2px; text-transform:uppercase; color:var(--gold-light); }
.sidebar-admin-info {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255,255,255,.1);
    display:flex; align-items:center; gap:10px;
}
.admin-avatar {
    width:38px; height:38px;
    border-radius:50%;
    background: var(--gold-gradient);
    display:flex; align-items:center; justify-content:center;
    font-weight:700; color:var(--maroon-dark);
    font-size:14px; flex-shrink:0;
}
.admin-name { font-size:13px; font-weight:600; color:white; }
.admin-role { font-size:11px; color:rgba(255,255,255,.55); }

.sidebar-nav { flex:1; padding:12px 0; }
.nav-section-label {
    font-size:10px; letter-spacing:1.5px; text-transform:uppercase;
    color:rgba(255,255,255,.35); padding:14px 20px 6px; font-weight:600;
}
.nav-item {
    display:flex; align-items:center; gap:10px;
    padding:11px 20px;
    font-size:13.5px; font-weight:500;
    color:rgba(255,255,255,.7);
    transition:all var(--transition);
    border-left:3px solid transparent;
    cursor:pointer; white-space:nowrap;
    position:relative;
}
.nav-item:hover { color:white; background:rgba(255,255,255,.08); }
.nav-item.active { color:white; background:rgba(201,168,76,.2); border-left-color:var(--gold); }
.nav-item .nav-icon { width:18px; text-align:center; font-size:14px; flex-shrink:0; }
.nav-badge {
    margin-left:auto;
    background:var(--gold-gradient);
    color:var(--maroon-dark);
    font-size:10px; font-weight:700;
    padding:2px 7px; border-radius:20px;
}
.sidebar-footer {
    padding: 16px 20px;
    border-top: 1px solid rgba(255,255,255,.1);
}
.sidebar-footer a {
    display:flex; align-items:center; gap:8px;
    font-size:13px; color:rgba(255,255,255,.55);
    transition:color var(--transition);
}
.sidebar-footer a:hover { color:white; }

/* ── Main ── */
.admin-main { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; min-height:100vh; }

/* ── Topbar ── */
.admin-topbar {
    height:var(--header-h);
    background:white;
    border-bottom:1px solid var(--beige-dark);
    display:flex; align-items:center; justify-content:space-between;
    padding:0 28px;
    position:sticky; top:0; z-index:50;
    box-shadow:var(--shadow);
}
.topbar-left { display:flex; align-items:center; gap:14px; }
.topbar-page-title { font-size:17px; font-weight:700; color:var(--text); }
.topbar-breadcrumb { font-size:12px; color:var(--text-muted); }
.topbar-right { display:flex; align-items:center; gap:12px; }
.topbar-btn {
    width:38px; height:38px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    background:var(--beige); color:var(--text-muted);
    border:none; cursor:pointer;
    transition:all var(--transition); font-size:14px;
    position:relative;
}
.topbar-btn:hover { background:var(--beige-dark); color:var(--maroon); }
.topbar-notif-badge {
    position:absolute; top:6px; right:6px;
    width:8px; height:8px; border-radius:50%;
    background:var(--danger); border:2px solid white;
}
.topbar-user {
    display:flex; align-items:center; gap:8px;
    cursor:pointer; padding:6px 10px;
    border-radius:10px;
    transition:background var(--transition);
}
.topbar-user:hover { background:var(--beige); }
.topbar-user-name { font-size:13px; font-weight:600; color:var(--text); }
.menu-toggle {
    display:none;
    background:none; border:none;
    font-size:20px; cursor:pointer;
    color:var(--text); padding:4px;
}

/* ── Content ── */
.admin-content { padding:28px; flex:1; }

/* ── Cards ── */
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.page-header h1 { font-size:22px; font-weight:700; color:var(--text); }
.page-header p { font-size:13px; color:var(--text-muted); margin-top:2px; }

.card {
    background:white; border-radius:var(--radius);
    box-shadow:var(--shadow); overflow:hidden;
}
.card-header {
    padding:16px 20px;
    border-bottom:1px solid var(--beige-dark);
    display:flex; align-items:center; justify-content:space-between;
    gap:12px;
}
.card-title { font-size:15px; font-weight:700; color:var(--text); }
.card-body { padding:20px; }

/* ── Stats Cards ── */
.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:18px; margin-bottom:24px; }
.stat-card {
    background:white; border-radius:var(--radius);
    padding:20px; box-shadow:var(--shadow);
    display:flex; align-items:center; gap:16px;
    transition:transform var(--transition), box-shadow var(--transition);
}
.stat-card:hover { transform:translateY(-2px); box-shadow:var(--shadow-lg); }
.stat-card-icon {
    width:52px; height:52px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:20px; flex-shrink:0;
}
.stat-card-icon.maroon { background:rgba(128,0,0,.1); color:var(--maroon); }
.stat-card-icon.gold   { background:rgba(201,168,76,.15); color:var(--gold); }
.stat-card-icon.green  { background:rgba(22,163,74,.1); color:var(--success); }
.stat-card-icon.blue   { background:rgba(3,105,161,.1); color:var(--info); }
.stat-card-icon.orange { background:rgba(217,119,6,.1); color:var(--warning); }
.stat-card-value { font-size:26px; font-weight:700; color:var(--text); line-height:1; }
.stat-card-label { font-size:12px; color:var(--text-muted); margin-top:3px; }

/* ── Table ── */
.table-wrap { overflow-x:auto; }
table { width:100%; border-collapse:collapse; font-size:13.5px; }
th { background:var(--beige); padding:11px 14px; text-align:left; font-size:11px;
     letter-spacing:.5px; text-transform:uppercase; color:var(--text-muted); font-weight:600; }
td { padding:12px 14px; border-bottom:1px solid var(--beige-light); vertical-align:middle; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:var(--beige-light); }
.table-img { width:50px; height:38px; object-fit:cover; border-radius:6px; }
.table-no-img { width:50px; height:38px; border-radius:6px; background:var(--beige); display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:14px; }

/* ── Badges ── */
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; letter-spacing:.3px; }
.badge-green  { background:#DCFCE7; color:var(--success); }
.badge-red    { background:#FEE2E2; color:var(--danger); }
.badge-yellow { background:#FEF9C3; color:#854D0E; }
.badge-orange { background:#FFEDD5; color:#9A3412; }
.badge-blue   { background:#DBEAFE; color:var(--info); }
.badge-gray   { background:var(--beige); color:var(--text-muted); }
.badge-gold   { background:rgba(201,168,76,.2); color:#7A5C1A; }
.badge-maroon { background:rgba(128,0,0,.1); color:var(--maroon); }

/* ── Buttons ── */
.btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:none; transition:all var(--transition); white-space:nowrap; }
.btn-sm { padding:5px 12px; font-size:12px; }
.btn-primary { background:var(--maroon); color:white; }
.btn-primary:hover { background:var(--maroon-dark); }
.btn-gold { background:var(--gold-gradient); color:var(--maroon-dark); }
.btn-gold:hover { opacity:.9; }
.btn-outline { background:transparent; color:var(--maroon); border:1.5px solid var(--maroon); }
.btn-outline:hover { background:var(--maroon); color:white; }
.btn-danger { background:var(--danger); color:white; }
.btn-danger:hover { background:#B91C1C; }
.btn-success { background:var(--success); color:white; }
.btn-success:hover { background:#15803D; }
.btn-gray { background:var(--beige-dark); color:var(--text-muted); }
.btn-gray:hover { background:var(--beige); }
.btn-icon { padding:7px; width:34px; height:34px; justify-content:center; border-radius:8px; }

/* ── Forms ── */
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
.form-grid-3 { grid-template-columns:1fr 1fr 1fr; }
.form-full { grid-column:1/-1; }
.form-group { display:flex; flex-direction:column; gap:5px; }
.form-label { font-size:12px; font-weight:600; color:var(--text); letter-spacing:.3px; text-transform:uppercase; }
.form-control {
    padding:10px 12px; border:1.5px solid var(--beige-dark);
    border-radius:8px; font-family:'DM Sans',sans-serif;
    font-size:13.5px; color:var(--text);
    background:white; transition:border-color var(--transition), box-shadow var(--transition);
    outline:none; width:100%;
}
.form-control:focus { border-color:var(--maroon); box-shadow:0 0 0 3px rgba(128,0,0,.08); }
textarea.form-control { resize:vertical; min-height:100px; }
select.form-control { cursor:pointer; }
.form-hint { font-size:11px; color:var(--text-muted); }
.form-check { display:flex; align-items:center; gap:8px; cursor:pointer; }
.form-check input { width:16px; height:16px; cursor:pointer; accent-color:var(--maroon); }
.form-check-label { font-size:13.5px; color:var(--text); }

/* ── Alerts ── */
.alert { padding:12px 16px; border-radius:10px; margin-bottom:18px; display:flex; align-items:flex-start; gap:10px; font-size:13.5px; }
.alert-success { background:#F0FDF4; border:1px solid #BBF7D0; color:var(--success); }
.alert-danger  { background:#FEF2F2; border:1px solid #FECACA; color:var(--danger); }
.alert-warning { background:#FFFBEB; border:1px solid #FDE68A; color:var(--warning); }
.alert-info    { background:#EFF6FF; border:1px solid #BFDBFE; color:var(--info); }

/* ── Pagination ── */
.pagination-bar { display:flex; align-items:center; justify-content:between; padding:14px 20px; border-top:1px solid var(--beige-dark); gap:6px; }
.page-btn { padding:6px 12px; border:1.5px solid var(--beige-dark); border-radius:7px; background:white; font-size:13px; color:var(--text-muted); cursor:pointer; transition:all var(--transition); }
.page-btn.active, .page-btn:hover { border-color:var(--maroon); color:var(--maroon); background:rgba(128,0,0,.05); }

/* ── Responsive ── */
@media (max-width:768px) {
    .admin-sidebar { transform:translateX(-100%); }
    .admin-sidebar.open { transform:translateX(0); }
    .admin-main { margin-left:0; }
    .menu-toggle { display:flex; }
    .form-grid { grid-template-columns:1fr; }
    .form-grid-3 { grid-template-columns:1fr; }
}
</style>
</head>
<body>
<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <?php if ($logoUrl): ?>
            <div class="sidebar-brand-logo"><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo"></div>
            <?php endif; ?>
            <div>
                <div class="sidebar-brand-text"><?= htmlspecialchars($siteName) ?></div>
                <div class="sidebar-brand-sub">Admin Panel</div>
            </div>
        </div>
        <div class="sidebar-admin-info">
            <div class="admin-avatar"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
            <div>
                <div class="admin-name"><?= htmlspecialchars($adminName) ?></div>
                <div class="admin-role">Administrator</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Main</div>
            <a href="<?= ADMIN_URL ?>/dashboard.php" class="nav-item <?= ($activePage??'')==='dashboard' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt nav-icon"></i> Dashboard
            </a>

            <div class="nav-section-label">Properties</div>
            <a href="<?= ADMIN_URL ?>/properties.php" class="nav-item <?= ($activePage??'')==='properties' ? 'active' : '' ?>">
                <i class="fas fa-building nav-icon"></i> All Properties
                <span class="nav-badge"><?= $totalProps ?></span>
            </a>
            <a href="<?= ADMIN_URL ?>/property-add.php" class="nav-item <?= ($activePage??'')==='property-add' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle nav-icon"></i> Add Property
            </a>

            <div class="nav-section-label">CRM</div>
            <a href="<?= ADMIN_URL ?>/leads.php" class="nav-item <?= ($activePage??'')==='leads' ? 'active' : '' ?>">
                <i class="fas fa-users nav-icon"></i> Leads & Enquiries
                <?php if ($newLeadsCount): ?>
                <span class="nav-badge"><?= $newLeadsCount ?></span>
                <?php endif; ?>
            </a>

            <div class="nav-section-label">Content</div>
            <a href="<?= ADMIN_URL ?>/blogs.php" class="nav-item <?= ($activePage??'')==='blogs' ? 'active' : '' ?>">
                <i class="fas fa-newspaper nav-icon"></i> Blog Posts
            </a>
            <a href="<?= ADMIN_URL ?>/team.php" class="nav-item <?= ($activePage??'')==='team' ? 'active' : '' ?>">
                <i class="fas fa-user-friends nav-icon"></i> Team Members
            </a>
            <a href="<?= ADMIN_URL ?>/testimonials.php" class="nav-item <?= ($activePage??'')==='testimonials' ? 'active' : '' ?>">
                <i class="fas fa-star nav-icon"></i> Testimonials
            </a>

            <div class="nav-section-label">System</div>
            <a href="<?= ADMIN_URL ?>/settings.php" class="nav-item <?= ($activePage??'')==='settings' ? 'active' : '' ?>">
                <i class="fas fa-cog nav-icon"></i> Site Settings
            </a>
            <a href="<?= SITE_URL ?>" target="_blank" class="nav-item">
                <i class="fas fa-external-link-alt nav-icon"></i> View Website
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= ADMIN_URL ?>/logout.php">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="document.getElementById('adminSidebar').classList.toggle('open')">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <div class="topbar-page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
                </div>
            </div>
            <div class="topbar-right">
                <?php if ($newLeadsCount): ?>
                <a href="<?= ADMIN_URL ?>/leads.php" class="topbar-btn" title="New Leads">
                    <i class="fas fa-bell"></i>
                    <span class="topbar-notif-badge"></span>
                </a>
                <?php endif; ?>
                <a href="<?= SITE_URL ?>" target="_blank" class="topbar-btn" title="View Site">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <a href="<?= ADMIN_URL ?>/logout.php" class="btn btn-sm btn-outline" style="margin-left:4px">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </header>

        <div class="admin-content">
