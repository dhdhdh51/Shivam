<?php
/**
 * LuxeEstate Realty - About Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/functions.php';

$team         = db()->query("SELECT * FROM team_members WHERE status='active' ORDER BY sort_order ASC")->fetchAll();
$testimonials = db()->query("SELECT * FROM testimonials WHERE status='active' ORDER BY RAND() LIMIT 6")->fetchAll();

$stats = [
    ['value' => getSetting('stat_properties', '500+'), 'label' => 'Properties Sold', 'icon' => 'fa-home'],
    ['value' => getSetting('stat_clients',    '1200+'), 'label' => 'Happy Clients',   'icon' => 'fa-smile'],
    ['value' => getSetting('stat_cities',     '15+'),   'label' => 'Cities Covered',  'icon' => 'fa-city'],
    ['value' => getSetting('stat_years',      '10+'),   'label' => 'Years Experience','icon' => 'fa-award'],
];

$currentPage   = 'about';
$siteName      = getSetting('site_name', 'LuxeEstate Realty');
$pageMetaTitle = "About Us | $siteName";
$pageMetaDesc  = "Learn about $siteName — our story, our team, and our mission to help you find your perfect home.";

include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="page-hero-content">
        <h1>About <span>Us</span></h1>
        <p>Building trust through excellence in real estate</p>
        <nav class="breadcrumb-nav">
            <a href="<?= SITE_URL ?>">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>About Us</span>
        </nav>
    </div>
</section>

<!-- Story Section -->
<section class="about-story section-pad">
    <div class="container">
        <div class="about-grid">
            <div class="about-image fade-left">
                <?php $aboutImg = getSetting('about_image'); ?>
                <?php if ($aboutImg): ?>
                <img src="<?= UPLOAD_URL . htmlspecialchars($aboutImg) ?>" alt="About <?= htmlspecialchars($siteName) ?>">
                <?php else: ?>
                <div class="about-img-placeholder">
                    <div class="about-img-inner">
                        <i class="fas fa-building" style="font-size:80px;color:var(--gold)"></i>
                        <p style="color:var(--beige);margin-top:20px;font-size:18px"><?= htmlspecialchars($siteName) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <div class="about-exp-badge">
                    <span class="exp-number"><?= getSetting('stat_years', '10') ?></span>
                    <span class="exp-label">Years of<br>Excellence</span>
                </div>
            </div>
            <div class="about-content fade-right">
                <div class="section-label">Our Story</div>
                <h2 class="section-title"><?= htmlspecialchars(getSetting('about_tagline', 'Your Trusted Real Estate Partner')) ?></h2>
                <div class="about-text">
                    <?= getSetting('about_content', '<p>At ' . $siteName . ', we believe that finding the perfect home is one of life\'s most important journeys. Founded with a passion for real estate and a commitment to client satisfaction, we have been guiding families, investors, and businesses to their ideal properties for over a decade.</p><p>Our team of experienced professionals understands that every client has unique needs and aspirations. We combine deep market knowledge with personalized service to deliver exceptional results — from the first consultation to the final handshake.</p><p>Whether you\'re a first-time buyer, seasoned investor, or looking to sell, we\'re here to make your real estate experience seamless, transparent, and rewarding.</p>') ?>
                </div>
                <div class="about-highlights">
                    <div class="highlight-item">
                        <i class="fas fa-check-circle"></i>
                        <span>RERA Registered & Compliant</span>
                    </div>
                    <div class="highlight-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Transparent Pricing, Zero Hidden Charges</span>
                    </div>
                    <div class="highlight-item">
                        <i class="fas fa-check-circle"></i>
                        <span>End-to-End Property Assistance</span>
                    </div>
                    <div class="highlight-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Legal & Documentation Support</span>
                    </div>
                </div>
                <div class="about-cta-row">
                    <a href="<?= SITE_URL ?>/contact.php" class="btn-gold">Get in Touch</a>
                    <a href="<?= SITE_URL ?>/properties.php" class="btn-outline">Browse Properties</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <?php foreach ($stats as $stat): ?>
            <div class="stat-item fade-up">
                <div class="stat-icon"><i class="fas <?= $stat['icon'] ?>"></i></div>
                <div class="stat-number counter" data-target="<?= preg_replace('/[^0-9]/', '', $stat['value']) ?>">
                    <?= htmlspecialchars($stat['value']) ?>
                </div>
                <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="why-us-section section-pad">
    <div class="container">
        <div class="section-header">
            <div class="section-label">Why Choose Us</div>
            <h2 class="section-title">What Sets Us <span>Apart</span></h2>
            <p class="section-subtitle">We go beyond transactions to build lasting relationships</p>
        </div>
        <div class="why-us-grid">
            <div class="why-card fade-up">
                <div class="why-icon"><i class="fas fa-medal"></i></div>
                <h3>Award-Winning Service</h3>
                <p>Recognized for excellence in customer satisfaction and industry expertise year after year.</p>
            </div>
            <div class="why-card fade-up" style="transition-delay:.1s">
                <div class="why-icon"><i class="fas fa-shield-alt"></i></div>
                <h3>Verified Properties</h3>
                <p>Every listing is thoroughly verified for legal compliance, construction quality, and fair pricing.</p>
            </div>
            <div class="why-card fade-up" style="transition-delay:.2s">
                <div class="why-icon"><i class="fas fa-handshake"></i></div>
                <h3>End-to-End Support</h3>
                <p>From property discovery to registration — we walk every step of the journey with you.</p>
            </div>
            <div class="why-card fade-up" style="transition-delay:.3s">
                <div class="why-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Market Intelligence</h3>
                <p>Data-driven insights to help you make the best investment decisions in the right locations.</p>
            </div>
            <div class="why-card fade-up" style="transition-delay:.4s">
                <div class="why-icon"><i class="fas fa-headset"></i></div>
                <h3>24/7 Support</h3>
                <p>Our dedicated team is always available to answer your questions and address your concerns.</p>
            </div>
            <div class="why-card fade-up" style="transition-delay:.5s">
                <div class="why-icon"><i class="fas fa-file-contract"></i></div>
                <h3>Legal Assistance</h3>
                <p>In-house legal experts ensure all your documentation and agreements are airtight.</p>
            </div>
        </div>
    </div>
</section>

<!-- Team -->
<?php if (!empty($team)): ?>
<section class="team-section section-pad" style="background:var(--beige-light)">
    <div class="container">
        <div class="section-header">
            <div class="section-label">Our Team</div>
            <h2 class="section-title">Meet Our <span>Experts</span></h2>
            <p class="section-subtitle">Passionate professionals dedicated to your property success</p>
        </div>
        <div class="team-grid">
            <?php foreach ($team as $member): ?>
            <div class="team-card fade-up">
                <div class="team-avatar">
                    <?php if ($member['image']): ?>
                    <img src="<?= UPLOAD_URL . htmlspecialchars($member['image']) ?>"
                         alt="<?= htmlspecialchars($member['name']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="team-avatar-placeholder"><i class="fas fa-user-tie"></i></div>
                    <?php endif; ?>
                </div>
                <div class="team-info">
                    <h3><?= htmlspecialchars($member['name']) ?></h3>
                    <span class="team-role"><?= htmlspecialchars($member['role']) ?></span>
                    <?php if (!empty($member['experience_years'])): ?>
                    <span class="team-exp"><?= htmlspecialchars($member['experience_years']) ?> yrs experience</span>
                    <?php endif; ?>
                    <div class="team-social">
                        <?php if ($member['linkedin']): ?>
                        <a href="<?= htmlspecialchars($member['linkedin']) ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                        <?php if ($member['phone']): ?>
                        <a href="tel:<?= htmlspecialchars($member['phone']) ?>"><i class="fas fa-phone"></i></a>
                        <?php endif; ?>
                        <?php if ($member['email']): ?>
                        <a href="mailto:<?= htmlspecialchars($member['email']) ?>"><i class="fas fa-envelope"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="testimonials-section section-pad">
    <div class="container">
        <div class="section-header">
            <div class="section-label">Client Stories</div>
            <h2 class="section-title">What Our <span>Clients Say</span></h2>
        </div>
        <div class="testimonials-grid">
            <?php foreach ($testimonials as $t): ?>
            <div class="testimonial-card fade-up">
                <div class="testimonial-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?= $i <= ($t['rating'] ?? 5) ? 'filled' : '' ?>"></i>
                    <?php endfor; ?>
                </div>
                <p class="testimonial-text">"<?= htmlspecialchars($t['review']) ?>"</p>
                <div class="testimonial-author">
                    <?php if ($t['image']): ?>
                    <img src="<?= UPLOAD_URL . htmlspecialchars($t['image']) ?>" alt="<?= htmlspecialchars($t['name']) ?>">
                    <?php else: ?>
                    <div class="testimonial-avatar"><i class="fas fa-user"></i></div>
                    <?php endif; ?>
                    <div>
                        <strong><?= htmlspecialchars($t['name']) ?></strong>
                        <?php if ($t['designation']): ?>
                        <span><?= htmlspecialchars($t['designation']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
