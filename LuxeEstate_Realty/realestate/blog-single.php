<?php
/**
 * LuxeEstate Realty - Blog Single Post
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/functions.php';

$slug = sanitize($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . SITE_URL . '/blog.php');
    exit;
}

$db   = db();
$stmt = $db->prepare("SELECT b.*, u.name AS author_name
    FROM blogs b LEFT JOIN users u ON b.author_id = u.id
    WHERE b.slug = ? AND b.status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$blog = $stmt->fetch();

if (!$blog) {
    http_response_code(404);
    include __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:100px 20px;text-align:center;">
        <h2 style="color:var(--maroon)">Article Not Found</h2>
        <p>This article does not exist or has been removed.</p>
        <a href="' . SITE_URL . '/blog.php" class="btn-gold">Back to Blog</a>
    </div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Increment views
$db->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?")->execute([$blog['id']]);

// Related posts
$related = $db->prepare("SELECT id, title, slug, featured_image AS image, created_at, category
    FROM blogs WHERE status='published' AND id != ? AND category = ? ORDER BY created_at DESC LIMIT 3");
$related->execute([$blog['id'], $blog['category'] ?: '']);
$relatedPosts = $related->fetchAll();

// If not enough, fill with latest
if (count($relatedPosts) < 3) {
    $ids   = array_column($relatedPosts, 'id');
    $ids[] = $blog['id'];
    $notIn = implode(',', array_map('intval', $ids));
    $more  = $db->query("SELECT id, title, slug, featured_image AS image, created_at, category
        FROM blogs WHERE status='published' AND id NOT IN ($notIn) ORDER BY created_at DESC LIMIT " . (3 - count($relatedPosts)))->fetchAll();
    $relatedPosts = array_merge($relatedPosts, $more);
}

// Prev / Next
$prev = $db->prepare("SELECT title, slug FROM blogs WHERE status='published' AND created_at < ? ORDER BY created_at DESC LIMIT 1");
$prev->execute([$blog['created_at']]); $prev = $prev->fetch();

$next = $db->prepare("SELECT title, slug FROM blogs WHERE status='published' AND created_at > ? ORDER BY created_at ASC LIMIT 1");
$next->execute([$blog['created_at']]); $next = $next->fetch();

$currentPage   = 'blog';
$siteName      = getSetting('site_name', 'LuxeEstate Realty');
$pageMetaTitle = ($blog['meta_title'] ?: $blog['title']) . " | $siteName";
$pageMetaDesc  = $blog['meta_description'] ?: mb_substr(strip_tags($blog['content']), 0, 160);

include __DIR__ . '/includes/header.php';
?>

<!-- Blog Single -->
<section class="blog-single-section section-pad">
    <div class="container">
        <div class="blog-layout">

            <!-- Main Article -->
            <article class="blog-article">

                <!-- Hero Image -->
                <?php if (!empty($blog['featured_image'])): ?>
                <div class="blog-hero-img">
                    <img src="<?= UPLOAD_URL . htmlspecialchars($blog['featured_image']) ?>"
                         alt="<?= htmlspecialchars($blog['title']) ?>">
                    <?php if ($blog['category']): ?>
                    <span class="blog-cat-badge"><?= htmlspecialchars($blog['category']) ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Breadcrumb -->
                <nav class="breadcrumb-nav" style="margin:20px 0">
                    <a href="<?= SITE_URL ?>">Home</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="<?= SITE_URL ?>/blog.php">Blog</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= htmlspecialchars(mb_substr($blog['title'], 0, 40)) ?>...</span>
                </nav>

                <!-- Meta -->
                <div class="article-meta">
                    <span><i class="fas fa-calendar-alt"></i> <?= date('d F Y', strtotime($blog['created_at'])) ?></span>
                    <?php if ($blog['author_name']): ?>
                    <span><i class="fas fa-user-edit"></i> By <?= htmlspecialchars($blog['author_name']) ?></span>
                    <?php endif; ?>
                    <span><i class="fas fa-eye"></i> <?= number_format(($blog['views'] ?? 0) + 1) ?> views</span>
                    <?php if ($blog['category']): ?>
                    <a href="<?= SITE_URL ?>/blog.php?category=<?= urlencode($blog['category']) ?>" class="article-cat">
                        <i class="fas fa-tag"></i> <?= htmlspecialchars($blog['category']) ?>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Title -->
                <h1 class="article-title"><?= htmlspecialchars($blog['title']) ?></h1>

                <?php if ($blog['excerpt']): ?>
                <p class="article-excerpt"><?= htmlspecialchars($blog['excerpt']) ?></p>
                <?php endif; ?>

                <!-- Content -->
                <div class="article-content">
                    <?= $blog['content'] /* HTML content from editor */ ?>
                </div>

                <!-- Tags (if any) -->
                <?php if ($blog['tags']): ?>
                <div class="article-tags">
                    <i class="fas fa-tags"></i>
                    <?php foreach (explode(',', $blog['tags']) as $tag): ?>
                    <span class="tag-badge"><?= htmlspecialchars(trim($tag)) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Share Bar -->
                <div class="article-share">
                    <span>Share this article:</span>
                    <div class="share-buttons">
                        <a href="https://wa.me/?text=<?= urlencode($blog['title'] . ' - ' . SITE_URL . '/blog/' . $blog['slug']) ?>"
                           target="_blank" class="share-btn share-wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/blog/' . $blog['slug']) ?>"
                           target="_blank" class="share-btn share-fb"><i class="fab fa-facebook-f"></i> Facebook</a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(SITE_URL . '/blog/' . $blog['slug']) ?>&title=<?= urlencode($blog['title']) ?>"
                           target="_blank" class="share-btn share-li"><i class="fab fa-linkedin-in"></i> LinkedIn</a>
                        <button onclick="copyLink(this)" class="share-btn share-copy">
                            <i class="fas fa-link"></i> Copy Link
                        </button>
                    </div>
                </div>

                <!-- Prev/Next Nav -->
                <?php if ($prev || $next): ?>
                <div class="article-nav">
                    <?php if ($prev): ?>
                    <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($prev['slug']) ?>" class="article-nav-item article-nav-prev">
                        <span><i class="fas fa-chevron-left"></i> Previous</span>
                        <strong><?= htmlspecialchars(mb_substr($prev['title'], 0, 50)) ?>...</strong>
                    </a>
                    <?php else: ?><div></div><?php endif; ?>
                    <?php if ($next): ?>
                    <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($next['slug']) ?>" class="article-nav-item article-nav-next">
                        <span>Next <i class="fas fa-chevron-right"></i></span>
                        <strong><?= htmlspecialchars(mb_substr($next['title'], 0, 50)) ?>...</strong>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </article>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <div class="sidebar-widget sidebar-cta">
                    <h3>Looking for Your Dream Property?</h3>
                    <p>Get expert advice and exclusive listings tailored just for you.</p>
                    <a href="<?= SITE_URL ?>/properties.php" class="btn-gold btn-full">Browse Properties</a>
                    <a href="<?= SITE_URL ?>/contact.php" class="btn-outline btn-full" style="margin-top:10px">
                        Talk to an Expert
                    </a>
                </div>

                <?php if (!empty($relatedPosts)): ?>
                <div class="sidebar-widget">
                    <h3 class="widget-title">Related Articles</h3>
                    <div class="recent-posts">
                        <?php foreach ($relatedPosts as $rp): ?>
                        <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($rp['slug']) ?>" class="recent-post">
                            <?php if ($rp['image']): ?>
                            <img src="<?= UPLOAD_URL . htmlspecialchars($rp['image']) ?>"
                                 alt="<?= htmlspecialchars($rp['title']) ?>" loading="lazy">
                            <?php else: ?>
                            <div class="recent-post-placeholder"><i class="fas fa-newspaper"></i></div>
                            <?php endif; ?>
                            <div class="recent-post-info">
                                <span class="recent-post-title"><?= htmlspecialchars(mb_substr($rp['title'], 0, 55)) ?>...</span>
                                <span class="recent-post-date"><?= date('d M Y', strtotime($rp['created_at'])) ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </aside>

        </div>
    </div>
</section>

<!-- Related Posts Grid -->
<?php if (!empty($relatedPosts)): ?>
<section class="related-blogs-section section-pad" style="background:var(--beige-light)">
    <div class="container">
        <h2 class="section-title" style="margin-bottom:30px">More <span>Articles</span></h2>
        <div class="blog-grid blog-grid-3">
            <?php foreach ($relatedPosts as $rp): ?>
            <article class="blog-card fade-up">
                <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($rp['slug']) ?>" class="blog-card-img-wrap">
                    <?php if ($rp['image']): ?>
                    <img src="<?= UPLOAD_URL . htmlspecialchars($rp['image']) ?>"
                         alt="<?= htmlspecialchars($rp['title']) ?>" loading="lazy" class="blog-card-img">
                    <?php else: ?>
                    <div class="blog-card-img blog-no-img"><i class="fas fa-newspaper"></i></div>
                    <?php endif; ?>
                    <?php if ($rp['category']): ?>
                    <span class="blog-cat-badge"><?= htmlspecialchars($rp['category']) ?></span>
                    <?php endif; ?>
                </a>
                <div class="blog-card-body">
                    <div class="blog-meta">
                        <span><i class="fas fa-calendar-alt"></i> <?= date('d M Y', strtotime($rp['created_at'])) ?></span>
                    </div>
                    <h3 class="blog-card-title">
                        <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($rp['slug']) ?>">
                            <?= htmlspecialchars($rp['title']) ?>
                        </a>
                    </h3>
                    <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($rp['slug']) ?>" class="read-more-link">
                        Read More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
function copyLink(btn) {
    navigator.clipboard.writeText(window.location.href).then(() => {
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => btn.innerHTML = '<i class="fas fa-link"></i> Copy Link', 2000);
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
