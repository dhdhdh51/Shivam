<?php
/**
 * LuxeEstate Realty - Property Detail Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/functions.php';

$slug = sanitize($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . SITE_URL . '/properties.php');
    exit;
}

$property = getPropertyBySlug($slug);
if (!$property) {
    http_response_code(404);
    include __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:100px 20px;text-align:center;">
        <h2 style="color:var(--maroon)">Property Not Found</h2>
        <p>The property you are looking for does not exist or has been removed.</p>
        <a href="' . SITE_URL . '/properties.php" class="btn-gold">Browse Properties</a>
    </div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Parse JSON fields
$amenities   = json_decode($property['amenities'] ?? '[]', true) ?: [];
$nearby      = json_decode($property['nearby'] ?? '[]', true) ?: [];
$images      = $property['images'] ?? [];
$primaryImage = '';
foreach ($images as $img) {
    if ($img['is_primary']) { $primaryImage = $img['image_path']; break; }
}
if (!$primaryImage && !empty($images)) $primaryImage = $images[0]['image_path'];

// Related properties
$related = getProperties(['type' => $property['type'], 'status' => 'active'], 3, 0)['properties'];
$related = array_filter($related, fn($p) => $p['id'] !== $property['id']);
$related = array_slice($related, 0, 3);

$csrf = generateCSRF();
$whatsappMsg = urlencode("Hi, I'm interested in {$property['title']} priced at " . formatPrice($property['price']) . ". Please share more details.");
$whatsappPhone = preg_replace('/[^0-9]/', '', getSetting('whatsapp_number', '919876543210'));

$metaTitle = $property['meta_title'] ?: "{$property['title']} | " . getSetting('site_name');
$metaDesc  = $property['meta_desc']  ?: substr(strip_tags($property['description']), 0, 160);

include __DIR__ . '/includes/header.php';
?>

<!-- Property Detail -->
<section class="property-detail-section">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="<?= SITE_URL ?>">Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= SITE_URL ?>/properties.php">Properties</a>
            <i class="fas fa-chevron-right"></i>
            <span><?= htmlspecialchars($property['title']) ?></span>
        </nav>

        <div class="property-detail-grid">

            <!-- LEFT: Gallery + Details -->
            <div class="property-detail-main">

                <!-- Gallery -->
                <div class="property-gallery" id="propertyGallery">
                    <div class="gallery-main">
                        <?php if (!empty($images)): ?>
                            <img src="<?= UPLOAD_URL . htmlspecialchars($primaryImage) ?>"
                                 alt="<?= htmlspecialchars($property['title']) ?>"
                                 id="galleryMainImg" class="gallery-main-img">
                        <?php else: ?>
                            <img src="<?= SITE_URL ?>/assets/images/property-placeholder.jpg"
                                 alt="Property" class="gallery-main-img">
                        <?php endif; ?>
                        <div class="gallery-badges">
                            <?php if ($property['featured']): ?>
                                <span class="badge badge-gold"><i class="fas fa-star"></i> Featured</span>
                            <?php endif; ?>
                            <?php if ($property['trending']): ?>
                                <span class="badge badge-maroon"><i class="fas fa-fire"></i> Trending</span>
                            <?php endif; ?>
                            <span class="badge badge-status <?= $property['status'] === 'active' ? 'badge-available' : 'badge-sold' ?>">
                                <?= ucfirst($property['status'] === 'active' ? 'Available' : $property['status']) ?>
                            </span>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <button class="gallery-arrow gallery-prev" id="galleryPrev"><i class="fas fa-chevron-left"></i></button>
                            <button class="gallery-arrow gallery-next" id="galleryNext"><i class="fas fa-chevron-right"></i></button>
                        <?php endif; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div class="gallery-thumbs" id="galleryThumbs">
                        <?php foreach ($images as $i => $img): ?>
                            <div class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>"
                                 data-index="<?= $i ?>"
                                 data-src="<?= UPLOAD_URL . htmlspecialchars($img['image_path']) ?>">
                                <img src="<?= UPLOAD_URL . htmlspecialchars($img['image_path']) ?>"
                                     alt="Photo <?= $i + 1 ?>" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Title + Price -->
                <div class="property-detail-header">
                    <div class="property-detail-title-row">
                        <div>
                            <h1 class="property-detail-title"><?= htmlspecialchars($property['title']) ?></h1>
                            <div class="property-detail-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($property['location']) ?>, <?= htmlspecialchars($property['city']) ?>
                            </div>
                        </div>
                        <div class="property-detail-price-block">
                            <div class="property-detail-price"><?= formatPrice($property['price']) ?></div>
                            <div class="property-detail-price-label">Onwards</div>
                        </div>
                    </div>

                    <div class="property-detail-specs">
                        <?php if ($property['bhk']): ?>
                        <div class="spec-item">
                            <i class="fas fa-bed"></i>
                            <span><?= htmlspecialchars($property['bhk']) ?> BHK</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($property['area']): ?>
                        <div class="spec-item">
                            <i class="fas fa-vector-square"></i>
                            <span><?= number_format($property['area']) ?> sq.ft</span>
                        </div>
                        <?php endif; ?>
                        <div class="spec-item">
                            <i class="fas fa-building"></i>
                            <span><?= htmlspecialchars($property['type']) ?></span>
                        </div>
                        <?php if ($property['possession']): ?>
                        <div class="spec-item">
                            <i class="fas fa-calendar-check"></i>
                            <span><?= htmlspecialchars($property['possession']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($property['rera_id']): ?>
                        <div class="spec-item">
                            <i class="fas fa-certificate"></i>
                            <span>RERA: <?= htmlspecialchars($property['rera_id']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="property-quick-actions">
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', getSetting('phone')) ?>"
                       class="btn-maroon btn-action"><i class="fas fa-phone"></i> Call Now</a>
                    <a href="https://wa.me/<?= $whatsappPhone ?>?text=<?= $whatsappMsg ?>"
                       target="_blank" class="btn-whatsapp btn-action"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <button class="btn-outline btn-action" onclick="scrollToEnquiry()">
                        <i class="fas fa-envelope"></i> Send Enquiry
                    </button>
                </div>

                <!-- Description -->
                <div class="property-section-block">
                    <h2 class="section-heading-inline">About This Property</h2>
                    <div class="property-description">
                        <?= nl2br(htmlspecialchars($property['description'])) ?>
                    </div>
                </div>

                <!-- Amenities -->
                <?php if (!empty($amenities)): ?>
                <div class="property-section-block">
                    <h2 class="section-heading-inline">Amenities & Features</h2>
                    <div class="amenities-grid">
                        <?php foreach ($amenities as $amenity): ?>
                        <div class="amenity-item">
                            <div class="amenity-icon"><i class="fas fa-check-circle"></i></div>
                            <span><?= htmlspecialchars($amenity) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Nearby Places -->
                <?php if (!empty($nearby)): ?>
                <div class="property-section-block">
                    <h2 class="section-heading-inline">Location & Nearby</h2>
                    <div class="nearby-grid">
                        <?php
                        $nearbyIcons = ['School' => 'fa-school', 'Hospital' => 'fa-hospital', 'Mall' => 'fa-shopping-bag',
                            'Metro' => 'fa-train', 'Airport' => 'fa-plane', 'Park' => 'fa-tree', 'Bank' => 'fa-university',
                            'Restaurant' => 'fa-utensils', 'Gym' => 'fa-dumbbell', 'Temple' => 'fa-place-of-worship'];
                        foreach ($nearby as $place):
                            $icon = 'fa-map-pin';
                            foreach ($nearbyIcons as $k => $v) {
                                if (stripos($place, $k) !== false) { $icon = $v; break; }
                            }
                        ?>
                        <div class="nearby-item">
                            <i class="fas <?= $icon ?>"></i>
                            <span><?= htmlspecialchars($place) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /.property-detail-main -->

            <!-- RIGHT: Enquiry Sidebar -->
            <div class="property-detail-sidebar" id="enquirySidebar">

                <!-- Enquiry Form -->
                <div class="enquiry-card sticky-sidebar" id="enquiryCard">
                    <div class="enquiry-card-header">
                        <h3><i class="fas fa-paper-plane"></i> Get a Callback</h3>
                        <p>Our experts will contact you within 2 hours</p>
                    </div>
                    <div class="enquiry-card-body">
                        <form id="quickEnquiryForm" novalidate>
                            <input type="hidden" name="action" value="quick_enquiry">
                            <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <div class="form-group">
                                <input type="text" name="name" placeholder="Your Full Name *" required class="form-control">
                            </div>
                            <div class="form-group">
                                <input type="tel" name="phone" placeholder="Phone Number *" required class="form-control">
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" placeholder="Email Address" class="form-control">
                            </div>
                            <div class="form-group">
                                <textarea name="message" placeholder="Your message (optional)" rows="3" class="form-control"></textarea>
                            </div>
                            <button type="submit" class="btn-gold btn-full">
                                <span class="btn-text"><i class="fas fa-paper-plane"></i> Send Enquiry</span>
                                <span class="btn-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i> Sending...</span>
                            </button>
                            <div class="form-success" style="display:none">
                                <i class="fas fa-check-circle"></i>
                                <p>Thank you! We'll call you back shortly.</p>
                            </div>
                        </form>
                        <div class="enquiry-divider"><span>OR</span></div>
                        <a href="https://wa.me/<?= $whatsappPhone ?>?text=<?= $whatsappMsg ?>"
                           target="_blank" class="btn-whatsapp btn-full">
                            <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    </div>
                </div>

                <!-- Agent Card -->
                <?php
                $agentName  = getSetting('agent_name',  'Rahul Sharma');
                $agentPhone = getSetting('phone', '+91 98765 43210');
                $agentEmail = getSetting('email', 'info@luxestate.com');
                ?>
                <div class="agent-card">
                    <div class="agent-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="agent-info">
                        <strong><?= htmlspecialchars($agentName) ?></strong>
                        <span>Property Expert</span>
                        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $agentPhone) ?>">
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($agentPhone) ?>
                        </a>
                    </div>
                </div>

                <!-- Share Property -->
                <div class="share-card">
                    <h4>Share This Property</h4>
                    <div class="share-buttons">
                        <a href="https://wa.me/?text=<?= urlencode($property['title'] . ' - ' . SITE_URL . '/property/' . $property['slug']) ?>"
                           target="_blank" class="share-btn share-wa"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/property/' . $property['slug']) ?>"
                           target="_blank" class="share-btn share-fb"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($property['title']) ?>&url=<?= urlencode(SITE_URL . '/property/' . $property['slug']) ?>"
                           target="_blank" class="share-btn share-tw"><i class="fab fa-twitter"></i></a>
                        <button onclick="copyPropertyLink()" class="share-btn share-copy" id="copyBtn">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>

            </div><!-- /.property-detail-sidebar -->

        </div><!-- /.property-detail-grid -->

        <!-- Related Properties -->
        <?php if (!empty($related)): ?>
        <div class="related-properties-section">
            <div class="section-title-row">
                <h2 class="section-title">Similar <span>Properties</span></h2>
                <a href="<?= SITE_URL ?>/properties.php?type=<?= urlencode($property['type']) ?>" class="view-all-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="properties-grid">
                <?php foreach ($related as $p): ?>
                    <?php include __DIR__ . '/includes/property-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /.container -->
</section>

<script>
// Property gallery
(function() {
    const images = <?= json_encode(array_values(array_map(fn($i) => UPLOAD_URL . $i['image_path'], $images))) ?>;
    let current = 0;
    const mainImg = document.getElementById('galleryMainImg');
    const thumbs  = document.querySelectorAll('.gallery-thumb');

    function setImage(idx) {
        if (!images.length) return;
        current = (idx + images.length) % images.length;
        if (mainImg) mainImg.src = images[current];
        thumbs.forEach((t, i) => t.classList.toggle('active', i === current));
        // scroll thumb into view
        if (thumbs[current]) thumbs[current].scrollIntoView({behavior:'smooth', block:'nearest', inline:'nearest'});
    }

    thumbs.forEach(t => t.addEventListener('click', () => setImage(+t.dataset.index)));
    const prev = document.getElementById('galleryPrev');
    const next = document.getElementById('galleryNext');
    if (prev) prev.addEventListener('click', () => setImage(current - 1));
    if (next) next.addEventListener('click', () => setImage(current + 1));

    // keyboard nav
    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowLeft') setImage(current - 1);
        if (e.key === 'ArrowRight') setImage(current + 1);
    });
})();

function scrollToEnquiry() {
    document.getElementById('enquiryCard').scrollIntoView({behavior:'smooth', block:'start'});
}

function copyPropertyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => btn.innerHTML = '<i class="fas fa-link"></i>', 2000);
    });
}

// Quick enquiry AJAX
document.getElementById('quickEnquiryForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form   = this;
    const btn    = form.querySelector('button[type=submit]');
    const data   = new FormData(form);
    btn.querySelector('.btn-text').style.display = 'none';
    btn.querySelector('.btn-loading').style.display = 'inline';
    btn.disabled = true;

    fetch('<?= SITE_URL ?>/ajax.php', { method:'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                form.querySelector('.form-success').style.display = 'block';
                form.querySelector('button[type=submit]').style.display = 'none';
                Array.from(form.querySelectorAll('.form-group')).forEach(g => g.style.display = 'none');
            } else {
                alert(res.message);
                btn.querySelector('.btn-text').style.display = 'inline';
                btn.querySelector('.btn-loading').style.display = 'none';
                btn.disabled = false;
            }
        })
        .catch(() => {
            btn.querySelector('.btn-text').style.display = 'inline';
            btn.querySelector('.btn-loading').style.display = 'none';
            btn.disabled = false;
        });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
