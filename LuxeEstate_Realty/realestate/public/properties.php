<?php
define('SITE_ROOT', dirname(__DIR__));
require_once SITE_ROOT . '/config/config.php';
require_once SITE_ROOT . '/functions/functions.php';

$currentPage   = 'properties';
$pageMetaTitle = 'Properties - ' . getSetting('site_name');
$pageMetaDesc  = 'Browse our exclusive collection of premium properties.';

// Get filters from URL
$filters = [
    'city'      => sanitize($_GET['city'] ?? ''),
    'type'      => sanitize($_GET['type'] ?? ''),
    'bhk'       => sanitize($_GET['bhk'] ?? ''),
    'min_price' => sanitize($_GET['min_price'] ?? ''),
    'max_price' => sanitize($_GET['max_price'] ?? ''),
    'search'    => sanitize($_GET['search'] ?? ''),
    'sort'      => sanitize($_GET['sort'] ?? 'newest'),
];
$page = max(1, (int)($_GET['page'] ?? 1));

$result = getProperties($filters, $page);
$properties = $result['properties'];
$total      = $result['total'];
$pages      = $result['pages'];
$cities     = getCities();

include SITE_ROOT . '/includes/header.php';
?>

<div class="page-hero">
    <div class="container page-hero-content">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Home</a>
            <span class="sep">›</span>
            <span class="current">Properties</span>
        </div>
        <h1>All Properties</h1>
        <p>Browse <strong id="properties-count"><?= $total ?></strong> premium properties across India</p>
    </div>
</div>

<!-- FILTER BAR -->
<div class="filter-section">
    <div class="container">
        <form id="property-filter-form" class="filter-bar">
            <select name="city" class="filter-select">
                <option value="">All Cities</option>
                <?php foreach ($cities as $city): ?>
                <option value="<?= htmlspecialchars($city) ?>" <?= $filters['city'] === $city ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
                <?php endforeach; ?>
            </select>
            
            <select name="type" class="filter-select">
                <option value="">All Types</option>
                <option value="apartment" <?= $filters['type'] === 'apartment' ? 'selected' : '' ?>>Apartment</option>
                <option value="villa" <?= $filters['type'] === 'villa' ? 'selected' : '' ?>>Villa</option>
                <option value="penthouse" <?= $filters['type'] === 'penthouse' ? 'selected' : '' ?>>Penthouse</option>
                <option value="studio" <?= $filters['type'] === 'studio' ? 'selected' : '' ?>>Studio</option>
                <option value="duplex" <?= $filters['type'] === 'duplex' ? 'selected' : '' ?>>Duplex</option>
                <option value="plot" <?= $filters['type'] === 'plot' ? 'selected' : '' ?>>Plot / Land</option>
                <option value="commercial" <?= $filters['type'] === 'commercial' ? 'selected' : '' ?>>Commercial</option>
            </select>
            
            <select name="bhk" class="filter-select">
                <option value="">Any BHK</option>
                <option value="1" <?= $filters['bhk'] === '1' ? 'selected' : '' ?>>1 BHK</option>
                <option value="2" <?= $filters['bhk'] === '2' ? 'selected' : '' ?>>2 BHK</option>
                <option value="3" <?= $filters['bhk'] === '3' ? 'selected' : '' ?>>3 BHK</option>
                <option value="4" <?= $filters['bhk'] === '4' ? 'selected' : '' ?>>4 BHK</option>
            </select>
            
            <select name="max_price" class="filter-select">
                <option value="">Any Budget</option>
                <option value="5000000" <?= $filters['max_price'] === '5000000' ? 'selected' : '' ?>>Under ₹50 L</option>
                <option value="10000000" <?= $filters['max_price'] === '10000000' ? 'selected' : '' ?>>Under ₹1 Cr</option>
                <option value="20000000" <?= $filters['max_price'] === '20000000' ? 'selected' : '' ?>>Under ₹2 Cr</option>
                <option value="50000000" <?= $filters['max_price'] === '50000000' ? 'selected' : '' ?>>Under ₹5 Cr</option>
            </select>
            
            <select name="sort" class="filter-select">
                <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="popular" <?= $filters['sort'] === 'popular' ? 'selected' : '' ?>>Most Popular</option>
            </select>
            
            <div class="filter-btn-group">
                <span class="filter-count" id="properties-count"><?= $total ?> Properties</span>
                <a href="<?= SITE_URL ?>/properties.php" class="btn btn-sm btn-outline">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- PROPERTIES GRID -->
<section class="section-sm" id="properties-section">
    <div class="container">
        <div id="properties-ajax-grid" class="properties-grid" style="transition:opacity 0.3s">
            <?php if (empty($properties)): ?>
                <div style="grid-column:1/-1;text-align:center;padding:4rem 0;color:var(--text-light)">
                    <div style="font-size:3rem;margin-bottom:1rem">🏠</div>
                    <h3 style="color:var(--maroon);margin-bottom:0.5rem">No Properties Found</h3>
                    <p>Try adjusting your filters to see more results.</p>
                    <a href="<?= SITE_URL ?>/properties.php" class="btn btn-maroon mt-3" style="margin-top:1rem;display:inline-flex">Clear Filters</a>
                </div>
            <?php else: ?>
                <?php foreach ($properties as $property): ?>
                    <?php include SITE_ROOT . '/includes/property-card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- PAGINATION -->
        <div id="pagination">
            <?php if ($pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?><button class="page-btn" data-page="<?= $page - 1 ?>">‹</button><?php endif; ?>
                <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
                <button class="page-btn <?= $i === $page ? 'active' : '' ?>" data-page="<?= $i ?>"><?= $i ?></button>
                <?php endfor; ?>
                <?php if ($page < $pages): ?><button class="page-btn" data-page="<?= $page + 1 ?>">›</button><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include SITE_ROOT . '/includes/footer.php'; ?>
