<?php
if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config/config.php';
    require_once dirname(__DIR__) . '/functions/functions.php';
}
$siteName     = getSetting('site_name', 'LuxeEstate Realty');
$siteTagline  = getSetting('site_tagline', 'Your Dream Home Awaits');
$siteLogo     = getSetting('site_logo');
$phone        = getSetting('contact_phone', '+91 98765 43210');
$whatsapp     = getSetting('whatsapp_number', '919876543210');
$metaTitle    = $pageMetaTitle ?? getSetting('meta_title', $siteName . ' - Premium Properties');
$metaDesc     = $pageMetaDesc ?? getSetting('meta_description');
$currentPage  = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($metaTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
<meta name="keywords" content="<?= getSetting('meta_keywords') ?>">
<meta name="robots" content="index, follow">
<meta property="og:title" content="<?= htmlspecialchars($metaTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= SITE_URL ?>">
<meta name="theme-color" content="#800000">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<?php if ($ga = getSetting('google_analytics')): ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?= $ga ?>"></script><?php endif; ?>
</head>
<body>

<header id="site-header">
    <div class="header-inner">
        <a href="<?= SITE_URL ?>" class="logo">
            <?php if ($siteLogo && file_exists(SITE_ROOT . '/' . $siteLogo)): ?>
                <img src="<?= SITE_URL . '/' . $siteLogo ?>" alt="<?= htmlspecialchars($siteName) ?>">
            <?php else: ?>
                <div class="logo-icon">L</div>
                <div class="logo-text"><?= htmlspecialchars($siteName) ?><span><?= htmlspecialchars($siteTagline) ?></span></div>
            <?php endif; ?>
        </a>
        
        <nav class="main-nav" id="main-nav">
            <a href="<?= SITE_URL ?>/" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Home</a>
            <a href="<?= SITE_URL ?>/properties.php" class="<?= $currentPage === 'properties' ? 'active' : '' ?>">Properties</a>
            <a href="<?= SITE_URL ?>/about.php" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About</a>
            <a href="<?= SITE_URL ?>/blog.php" class="<?= $currentPage === 'blog' ? 'active' : '' ?>">Blog</a>
            <a href="<?= SITE_URL ?>/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a>
        </nav>
        
        <div class="header-cta">
            <a href="tel:<?= preg_replace('/\s/', '', $phone) ?>" class="header-phone">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                <?= htmlspecialchars($phone) ?>
            </a>
            <a href="<?= SITE_URL ?>/contact.php" class="btn btn-gold btn-sm">Free Consultation</a>
        </div>
        
        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<script>
window.SITE_CONFIG = {
    siteUrl: '<?= SITE_URL ?>',
    ajaxUrl: '<?= SITE_URL ?>/ajax.php',
    adminAjaxUrl: '<?= ADMIN_URL ?>/ajax.php',
    whatsapp: '<?= $whatsapp ?>',
    listingUrl: '<?= SITE_URL ?>/properties.php'
};
</script>
