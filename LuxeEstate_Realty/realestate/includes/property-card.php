<?php
/**
 * Property Card Component
 * Usage: include with $property array in scope
 * $property = getFeaturedProperties() row
 */
$imgUrl = SITE_URL . '/assets/images/property-placeholder.jpg';
if (!empty($property['primary_image'])) {
    $imgPath = SITE_ROOT . '/' . $property['primary_image'];
    if (file_exists($imgPath)) {
        $imgUrl = SITE_URL . '/' . $property['primary_image'];
    }
}
$price = formatPrice((float)$property['price']);
$slug  = $property['slug'];
$whatsapp = getSetting('whatsapp_number', '919876543210');
$wa_msg = urlencode("Hi, I'm interested in {$property['title']}. Please share more details.");
?>
<article class="property-card animate-fade-up">
    <a href="<?= SITE_URL ?>/property/<?= htmlspecialchars($slug) ?>" class="property-card-img">
        <?php if ($imgUrl !== SITE_URL . '/assets/images/property-placeholder.jpg'): ?>
            <img data-src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($property['title']) ?>" class="skeleton" loading="lazy" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
        <?php else: ?>
            <div class="property-card-img-placeholder">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                <span>No Image</span>
            </div>
        <?php endif; ?>
        
        <div class="card-badges">
            <?php if ($property['featured']): ?><span class="badge badge-featured">⭐ Featured</span><?php endif; ?>
            <?php if ($property['trending']): ?><span class="badge badge-trending">🔥 Trending</span><?php endif; ?>
            <?php if ($property['status'] === 'sold'): ?><span class="badge badge-sold">SOLD</span><?php endif; ?>
        </div>
        
        <button class="card-wishlist" aria-label="Save property">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
        </button>
        
        <span class="card-type-pill"><?= ucfirst($property['type']) ?></span>
    </a>
    
    <div class="property-card-body">
        <div class="property-price">
            <?= $price ?>
            <?php if (!empty($property['price_label'])): ?><span><?= htmlspecialchars($property['price_label']) ?></span><?php endif; ?>
        </div>
        <a href="<?= SITE_URL ?>/property/<?= htmlspecialchars($slug) ?>" class="property-title" style="display:block;text-decoration:none">
            <?= htmlspecialchars($property['title']) ?>
        </a>
        <div class="property-location">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <?= htmlspecialchars($property['location']) ?>
        </div>
        
        <div class="property-specs">
            <?php if ($property['bhk']): ?>
            <div class="property-spec">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <strong><?= $property['bhk'] ?> BHK</strong>
                <small>Beds</small>
            </div>
            <?php endif; ?>
            <?php if ($property['bathrooms']): ?>
            <div class="property-spec">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 12h16M4 12a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2M4 12l-1.5 6a2 2 0 002 2h15a2 2 0 002-2L20 12"/></svg>
                <strong><?= $property['bathrooms'] ?></strong>
                <small>Baths</small>
            </div>
            <?php endif; ?>
            <?php if ($property['area_sqft']): ?>
            <div class="property-spec">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 12h6M9 15h4"/></svg>
                <strong><?= number_format($property['area_sqft']) ?></strong>
                <small>Sq.ft</small>
            </div>
            <?php endif; ?>
            <?php if ($property['parking']): ?>
            <div class="property-spec">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v3h-7V8zM5.5 19a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM18.5 19a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>
                <strong><?= $property['parking'] ?></strong>
                <small>Parking</small>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card-footer">
        <div class="card-footer-meta">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <?= timeAgo($property['created_at']) ?>
            &nbsp;·&nbsp;
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <?= number_format($property['views']) ?> views
        </div>
        <a href="https://wa.me/<?= $whatsapp ?>?text=<?= $wa_msg ?>" target="_blank" style="color:#25D366;font-size:1.2rem" title="WhatsApp Enquiry">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
</article>
