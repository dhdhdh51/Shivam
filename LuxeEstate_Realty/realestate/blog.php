<?php
/**
 * LuxeEstate Realty - Blog Listing
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/functions.php';

$page     = max(1, (int)($_GET['page'] ?? 1));
$category = sanitize($_GET['category'] ?? '');
$search   = sanitize($_GET['search'] ?? '');
$perPage  = BLOGS_PER_PAGE;
$offset   = ($page - 1) * $perPage;

// Get blogs
$db     = db();
$where  = ['b.status = "published"'];
$params = [];

if ($category) {
    $where[]  = 'b.category = :cat';
    $params[':cat'] = $category;
}
if ($search) {
    $where[]  = '(b.title LIKE :s OR b.content LIKE :s2)';
    $params[':s']  = "%$search%";
    $params[':s2'] = "%$search%";
}

$whereSQL = implode(' AND ', $where);

$totalStmt = $db->prepare("SELECT COUNT(*) FROM blogs b WHERE $whereSQL");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = ceil($total / $perPage);

$stmt = $db->prepare("SELECT b.*, u.name AS author_name FROM blogs b
    LEFT JOIN users u ON b.author_id = u.id
    WHERE $whereSQL ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$blogs = $stmt->fetchAll();

// Categories
$cats = $db->query("SELECT DISTINCT category FROM blogs WHERE status='published' AND category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// Featured blogs (for sidebar)
$featured = $db->query("SELECT id, title, slug, image, created_at FROM blogs WHERE status='published' AND featured=1 ORDER BY created_at DESC LIMIT 4")->fetchAll();

$siteName  = getSetting('site_name', 'LuxeEstate Realty');
$pageTitle = "Real Estate Blog | $siteName";
$metaDesc  = "Expert insights, market trends, and buying guides from $siteName. Your trusted real estate resource.";

include __DIR__ . '/includes/header.php';
?>

<!-- Blog Hero -->
<section class="page-hero">
    <div class="page-hero-content">
        <h1>Real Estate <span>Insights</span></h1>
        <p>Market trends, buying guides & expert advice</p>
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="<?= SITE_URL ?>">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Blog</span>
        </nav>
    </div>
</section>

<!-- Blog Content -->
<section class="blog-section section-pad">
    <div class="container">
        <div class="blog-layout">

            <!-- Main Blog Grid -->
            <div class="blog-main">

                <!-- Search + Filter Bar -->
                <div class="blog-filter-bar">
                    <form method="GET" action="<?= SITE_URL ?>/blog.php" class="blog-search-form">
                        <div class="search-input-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Search articles..." class="form-control">
                        </div>
                        <button type="submit" class="btn-maroon">Search</button>
                        <?php if ($search || $category): ?>
                        <a href="<?= SITE_URL ?>/blog.php" class="btn-outline">Clear</a>
                        <?php endif; ?>
                    </form>

                    <?php if (!empty($cats)): ?>
                    <div class="blog-categories-bar">
                        <a href="<?= SITE_URL ?>/blog.php" class="cat-tag <?= !$category ? 'active' : '' ?>">All</a>
                        <?php foreach ($cats as $cat): ?>
                        <a href="<?= SITE_URL ?>/blog.php?category=<?= urlencode($cat) ?>"
                           class="cat-tag <?= $category === $cat ? 'active' : '' ?>">
                            <?= htmlspecialchars($cat) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($total > 0): ?>
                <p class="results-count">Showing <?= count($blogs) ?> of <?= $total ?> articles
                    <?= $search ? "for \"<strong>$search</strong>\"" : '' ?>
                    <?= $category ? "in <strong>$category</strong>" : '' ?>
                </p>
                <?php endif; ?>

                <!-- Blog Grid -->
                <?php if (empty($blogs)): ?>
                <div class="no-results">
                    <i class="fas fa-newspaper"></i>
                    <h3>No Articles Found</h3>
                    <p>Try a different search term or browse all articles.</p>
                    <a href="<?= SITE_URL ?>/blog.php" class="btn-gold">View All Articles</a>
                </div>
                <?php else: ?>
                <div class="blog-grid">
                    <?php foreach ($blogs as $i => $blog): ?>
                    <article class="blog-card <?= $i === 0 && $page === 1 && !$search && !$category ? 'blog-card-featured' : '' ?> fade-up">
                        <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($blog['slug']) ?>" class="blog-card-img-wrap">
                            <?php if ($blog['image']): ?>
                            <img src="<?= UPLOAD_URL . htmlspecialchars($blog['image']) ?>"
                                 alt="<?= htmlspecialchars($blog['title']) ?>" loading="lazy" class="blog-card-img">
                            <?php else: ?>
                            <div class="blog-card-img blog-no-img">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <?php endif; ?>
                            <?php if ($blog['category']): ?>
                            <span class="blog-cat-badge"><?= htmlspecialchars($blog['category']) ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="blog-card-body">
                            <div class="blog-meta">
                                <span><i class="fas fa-calendar-alt"></i> <?= date('d M Y', strtotime($blog['created_at'])) ?></span>
                                <?php if ($blog['author_name']): ?>
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($blog['author_name']) ?></span>
                                <?php endif; ?>
                                <span><i class="fas fa-eye"></i> <?= number_format($blog['views'] ?? 0) ?> views</span>
                            </div>
                            <h2 class="blog-card-title">
                                <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($blog['slug']) ?>">
                                    <?= htmlspecialchars($blog['title']) ?>
                                </a>
                            </h2>
                            <p class="blog-card-excerpt">
                                <?= htmlspecialchars(mb_substr(strip_tags($blog['excerpt'] ?: $blog['content']), 0, 140)) ?>...
                            </p>
                            <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($blog['slug']) ?>" class="read-more-link">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pages > 1): ?>
                <div class="pagination-wrap">
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?= $page-1 ?><?= $category ? '&category='.urlencode($category) : '' ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                           class="page-btn"><i class="fas fa-chevron-left"></i></a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="?page=<?= $i ?><?= $category ? '&category='.urlencode($category) : '' ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                           class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $pages): ?>
                        <a href="?page=<?= $page+1 ?><?= $category ? '&category='.urlencode($category) : '' ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                           class="page-btn"><i class="fas fa-chevron-right"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>

            </div><!-- /.blog-main -->

            <!-- Sidebar -->
            <aside class="blog-sidebar">

                <!-- Recent Posts -->
                <?php if (!empty($featured)): ?>
                <div class="sidebar-widget">
                    <h3 class="widget-title">Featured Articles</h3>
                    <div class="recent-posts">
                        <?php foreach ($featured as $fp): ?>
                        <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($fp['slug']) ?>" class="recent-post">
                            <?php if ($fp['image']): ?>
                            <img src="<?= UPLOAD_URL . htmlspecialchars($fp['image']) ?>"
                                 alt="<?= htmlspecialchars($fp['title']) ?>" loading="lazy">
                            <?php else: ?>
                            <div class="recent-post-placeholder"><i class="fas fa-newspaper"></i></div>
                            <?php endif; ?>
                            <div class="recent-post-info">
                                <span class="recent-post-title"><?= htmlspecialchars(mb_substr($fp['title'], 0, 55)) ?>...</span>
                                <span class="recent-post-date"><?= date('d M Y', strtotime($fp['created_at'])) ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Categories -->
                <?php if (!empty($cats)): ?>
                <div class="sidebar-widget">
                    <h3 class="widget-title">Categories</h3>
                    <ul class="cat-list">
                        <li><a href="<?= SITE_URL ?>/blog.php" class="<?= !$category ? 'active' : '' ?>">
                            All Articles <span class="cat-count"><?= $total ?></span>
                        </a></li>
                        <?php foreach ($cats as $cat): ?>
                        <?php
                        $cStmt = $db->prepare("SELECT COUNT(*) FROM blogs WHERE status='published' AND category=?");
                        $cStmt->execute([$cat]); $cCount = $cStmt->fetchColumn();
                        ?>
                        <li><a href="<?= SITE_URL ?>/blog.php?category=<?= urlencode($cat) ?>"
                               class="<?= $category === $cat ? 'active' : '' ?>">
                            <?= htmlspecialchars($cat) ?> <span class="cat-count"><?= $cCount ?></span>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- CTA Widget -->
                <div class="sidebar-widget sidebar-cta">
                    <h3>Ready to Find Your Dream Home?</h3>
                    <p>Let our experts guide you through your real estate journey.</p>
                    <a href="<?= SITE_URL ?>/#lead-form" class="btn-gold btn-full">Get Free Consultation</a>
                    <a href="<?= SITE_URL ?>/properties.php" class="btn-outline btn-full" style="margin-top:10px">Browse Properties</a>
                </div>

            </aside>

        </div><!-- /.blog-layout -->
    </div><!-- /.container -->
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
