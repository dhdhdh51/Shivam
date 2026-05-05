<?php
define('SITE_ROOT', __DIR__);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/functions/functions.php';

$currentPage = 'home';
$pageMetaTitle = getSetting('meta_title');
$pageMetaDesc  = getSetting('meta_description');

// Load data conditionally based on section settings
$featuredProps   = getSetting('section_featured', '1') === '1' ? getFeaturedProperties(6) : [];
$testimonials    = getSetting('section_testimonials', '1') === '1' ? getTestimonials(6) : [];
$teamMembers     = getSetting('section_team', '1') === '1' ? getTeamMembers(4) : [];
$blogs           = getSetting('section_blog', '1') === '1' ? getFeaturedBlogs(3) : [];
$stats           = getSetting('section_stats', '1') === '1' ? getSiteStats() : [];
$cities          = getCities();

$heroTitle       = getSetting('hero_title', 'Find Your Perfect Home');
$heroSubtitle    = getSetting('hero_subtitle', 'Premium properties curated for the discerning buyer.');
$heroBg          = getSetting('hero_bg_image');

include __DIR__ . '/includes/header.php';
?>

<!-- ====== HERO SECTION ====== -->
<?php if (getSetting('section_hero', '1') === '1'): ?>
<section class="hero">
    <div class="hero-bg <?= $heroBg ? 'has-bg-img' : '' ?>" <?php if ($heroBg): ?>style="background-image:url('<?= SITE_URL . '/' . $heroBg ?>');background-size:cover;background-position:center"<?php endif; ?>></div>
    <div class="hero-image-overlay <?= $heroBg ? 'has-bg-img' : '' ?>"></div>
    <div class="hero-particles"></div>
    
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">Premier Real Estate Company in India</div>
            <h1 class="hero-title">
                <?php
                $parts = explode(' ', $heroTitle, 4);
                $italic = array_pop($parts);
                echo implode(' ', $parts) . ' <em>' . htmlspecialchars($italic) . '</em>';
                ?>
            </h1>
            <p class="hero-subtitle"><?= htmlspecialchars($heroSubtitle) ?></p>
            <div class="hero-actions">
                <a href="<?= SITE_URL ?>/properties.php" class="btn btn-gold btn-lg">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Explore Properties
                </a>
                <a href="<?= SITE_URL ?>/contact.php" class="btn btn-outline-gold btn-lg">
                    Free Consultation
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
            <?php if (!empty($stats)): ?>
            <div class="hero-stats">
                <div class="hero-stat-item">
                    <div class="hero-stat-number"><?= $stats['properties'] ?>+</div>
                    <div class="hero-stat-label">Properties</div>
                </div>
                <div class="hero-stat-item">
                    <div class="hero-stat-number"><?= $stats['clients'] ?>+</div>
                    <div class="hero-stat-label">Happy Clients</div>
                </div>
                <div class="hero-stat-item">
                    <div class="hero-stat-number"><?= $stats['cities'] ?>+</div>
                    <div class="hero-stat-label">Cities</div>
                </div>
                <div class="hero-stat-item">
                    <div class="hero-stat-number"><?= $stats['years'] ?>+</div>
                    <div class="hero-stat-label">Years Experience</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ====== HERO SEARCH ====== -->
<div class="hero-search">
    <div class="container">
        <div class="search-card">
            <div class="search-tabs">
                <button class="search-tab active" type="button">Buy</button>
                <button class="search-tab" type="button">Rent</button>
                <button class="search-tab" type="button">Commercial</button>
                <button class="search-tab" type="button">Plot</button>
            </div>
            <form id="hero-search-form">
                <div class="search-fields">
                    <div class="search-field">
                        <label>City / Location</label>
                        <select name="city">
                            <option value="">Any City</option>
                            <?php foreach ($cities as $city): ?><option><?= htmlspecialchars($city) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="search-field">
                        <label>Property Type</label>
                        <select name="type">
                            <option value="">Any Type</option>
                            <option value="apartment">Apartment</option>
                            <option value="villa">Villa</option>
                            <option value="penthouse">Penthouse</option>
                            <option value="studio">Studio</option>
                            <option value="duplex">Duplex</option>
                            <option value="plot">Plot / Land</option>
                            <option value="commercial">Commercial</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <label>BHK Configuration</label>
                        <select name="bhk">
                            <option value="">Any BHK</option>
                            <option value="1">1 BHK</option>
                            <option value="2">2 BHK</option>
                            <option value="3">3 BHK</option>
                            <option value="4">4 BHK</option>
                            <option value="5">5+ BHK</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <label>Budget</label>
                        <select name="max_price">
                            <option value="">Any Budget</option>
                            <option value="5000000">Under ₹50 Lakhs</option>
                            <option value="10000000">Under ₹1 Crore</option>
                            <option value="20000000">Under ₹2 Crore</option>
                            <option value="50000000">Under ₹5 Crore</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-maroon" style="height:48px;width:100%;font-size:0.95rem">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ====== FEATURED PROPERTIES ====== -->
<?php if (!empty($featuredProps)): ?>
<section class="section" id="featured-properties">
    <div class="container">
        <div class="flex-between mb-4 animate-fade-up">
            <div>
                <div class="section-label">Handpicked For You</div>
                <h2 class="section-title">Featured <em>Properties</em></h2>
                <p class="section-subtitle">Discover our exclusive collection of premium properties, personally curated for discerning buyers.</p>
            </div>
            <a href="<?= SITE_URL ?>/properties.php" class="btn btn-outline d-none" style="display:flex" >View All <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
        </div>
        
        <div class="properties-grid">
            <?php foreach ($featuredProps as $i => $property): ?>
                <?php 
                $animClass = 'animate-delay-' . min($i + 1, 4);
                ?>
                <div class="<?= $animClass ?>">
                    <?php include __DIR__ . '/includes/property-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align:center;margin-top:3rem" class="animate-fade-up">
            <a href="<?= SITE_URL ?>/properties.php" class="btn btn-maroon btn-lg">
                View All Properties
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ====== STATS SECTION ====== -->
<?php if (!empty($stats)): ?>
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item animate-fade-up">
                <div class="stat-number" data-target="<?= $stats['properties'] ?>"><span class="stat-suffix">+</span></div>
                <div class="stat-divider"></div>
                <p class="stat-label">Premium Properties</p>
            </div>
            <div class="stat-item animate-fade-up animate-delay-1">
                <div class="stat-number" data-target="<?= $stats['clients'] ?>"><span class="stat-suffix">+</span></div>
                <div class="stat-divider"></div>
                <p class="stat-label">Happy Families</p>
            </div>
            <div class="stat-item animate-fade-up animate-delay-2">
                <div class="stat-number" data-target="<?= $stats['cities'] ?>"><span class="stat-suffix">+</span></div>
                <div class="stat-divider"></div>
                <p class="stat-label">Cities Covered</p>
            </div>
            <div class="stat-item animate-fade-up animate-delay-3">
                <div class="stat-number" data-target="<?= $stats['years'] ?>"><span class="stat-suffix">+</span></div>
                <div class="stat-divider"></div>
                <p class="stat-label">Years of Excellence</p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ====== SERVICES ====== -->
<?php if (getSetting('section_services', '1') === '1'): ?>
<section class="section bg-beige">
    <div class="container">
        <div class="text-center mb-5 animate-fade-up">
            <div class="section-label">Why Choose Us</div>
            <h2 class="section-title">Our <em>Services</em></h2>
            <p class="section-subtitle">We provide end-to-end real estate solutions with unmatched expertise and personalized service.</p>
        </div>
        <div class="services-grid">
            <?php
            $services = [
                ['icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>', 'title' => 'Property Buying', 'desc' => 'Expert guidance through every step of purchasing your dream property, from search to registration.'],
                ['icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>', 'title' => 'Investment Advisory', 'desc' => 'Data-driven investment analysis to maximize your returns with minimum risk in the real estate market.'],
                ['icon' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>', 'title' => 'Free Consultation', 'desc' => 'No-obligation consultation with our certified experts to understand your requirements and budget perfectly.'],
                ['icon' => '<path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>', 'title' => 'Legal Assistance', 'desc' => 'Complete documentation, RERA verification, and legal compliance support for a worry-free transaction.'],
                ['icon' => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>', 'title' => 'Property Management', 'desc' => 'Comprehensive property management solutions for investors — from tenant search to maintenance.'],
                ['icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>', 'title' => 'Virtual Tours', 'desc' => 'Experience properties from anywhere with our immersive 360° virtual tours and video walkthroughs.'],
            ];
            foreach ($services as $i => $svc): ?>
            <div class="service-card animate-fade-up animate-delay-<?= $i + 1 ?>">
                <div class="service-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><?= $svc['icon'] ?></svg>
                </div>
                <h3 class="service-title"><?= $svc['title'] ?></h3>
                <p class="service-desc"><?= $svc['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ====== TESTIMONIALS ====== -->
<?php if (!empty($testimonials)): ?>
<section class="section testimonials-section">
    <div class="container">
        <div class="text-center mb-5 animate-fade-up">
            <div class="section-label">Client Stories</div>
            <h2 class="section-title">What Our <em>Clients Say</em></h2>
            <p class="section-subtitle">Hear from the families and investors who found their perfect property with us.</p>
        </div>
        <div class="testimonials-track">
            <?php foreach ($testimonials as $i => $t): ?>
            <div class="testimonial-card animate-fade-up animate-delay-<?= $i + 1 ?>">
                <div class="testimonial-stars">
                    <?= str_repeat('★', (int)$t['rating']) ?><?= str_repeat('☆', 5 - (int)$t['rating']) ?>
                </div>
                <p class="testimonial-text">"<?= htmlspecialchars($t['review']) ?>"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">
                        <?php if (!empty($t['image']) && file_exists(SITE_ROOT . '/' . $t['image'])): ?>
                            <img src="<?= SITE_URL . '/' . $t['image'] ?>" alt="<?= htmlspecialchars($t['name']) ?>">
                        <?php else: ?>
                            <?= strtoupper(substr($t['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="testimonial-name"><?= htmlspecialchars($t['name']) ?></div>
                        <?php if (!empty($t['designation'])): ?><div class="testimonial-role"><?= htmlspecialchars($t['designation']) ?></div><?php endif; ?>
                        <?php if (!empty($t['property_bought'])): ?><div class="testimonial-property">📍 <?= htmlspecialchars($t['property_bought']) ?></div><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ====== TEAM ====== -->
<?php if (!empty($teamMembers)): ?>
<section class="section">
    <div class="container">
        <div class="text-center mb-5 animate-fade-up">
            <div class="section-label">Meet The Experts</div>
            <h2 class="section-title">Our <em>Team</em></h2>
            <p class="section-subtitle">A dedicated team of real estate experts committed to making your property journey exceptional.</p>
        </div>
        <div class="team-grid">
            <?php foreach ($teamMembers as $i => $member): ?>
            <div class="team-card animate-fade-up animate-delay-<?= $i + 1 ?>">
                <div class="team-img">
                    <?php if (!empty($member['image']) && file_exists(SITE_ROOT . '/' . $member['image'])): ?>
                        <img src="<?= SITE_URL . '/' . $member['image'] ?>" alt="<?= htmlspecialchars($member['name']) ?>">
                    <?php else: ?>
                        <div class="team-img-placeholder"><?= strtoupper(substr($member['name'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <div class="team-social">
                        <?php if (!empty($member['phone'])): ?>
                        <a href="tel:<?= $member['phone'] ?>" title="Call"><i class="fas fa-phone"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($member['whatsapp'])): ?>
                        <a href="https://wa.me/<?= $member['whatsapp'] ?>" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($member['linkedin'])): ?>
                        <a href="<?= $member['linkedin'] ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="team-body">
                    <h3 class="team-name"><?= htmlspecialchars($member['name']) ?></h3>
                    <p class="team-role"><?= htmlspecialchars($member['role']) ?></p>
                    <div class="team-stats">
                        <?php if ($member['experience_years']): ?>
                        <div class="team-stat"><strong><?= $member['experience_years'] ?>+</strong><small>Years Exp.</small></div>
                        <?php endif; ?>
                        <?php if ($member['properties_sold']): ?>
                        <div class="team-stat"><strong><?= $member['properties_sold'] ?>+</strong><small>Properties</small></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ====== BLOG ====== -->
<?php if (!empty($blogs)): ?>
<section class="section bg-beige">
    <div class="container">
        <div class="flex-between mb-5 animate-fade-up">
            <div>
                <div class="section-label">Knowledge Hub</div>
                <h2 class="section-title">Latest <em>Insights</em></h2>
                <p class="section-subtitle">Expert advice, market trends, and guides to help you make the right property decisions.</p>
            </div>
            <a href="<?= SITE_URL ?>/blog.php" class="btn btn-outline" style="display:flex;align-items:center;gap:6px;white-space:nowrap">All Articles <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
        </div>
        <div class="blog-grid">
            <?php foreach ($blogs as $i => $blog): ?>
            <article class="blog-card animate-fade-up animate-delay-<?= $i + 1 ?>">
                <div class="blog-img">
                    <?php if (!empty($blog['featured_image']) && file_exists(SITE_ROOT . '/' . $blog['featured_image'])): ?>
                        <img data-src="<?= SITE_URL . '/' . $blog['featured_image'] ?>" alt="<?= htmlspecialchars($blog['title']) ?>" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="skeleton">
                    <?php else: ?>
                        <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--maroon),var(--maroon-light));display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.3);font-size:3rem">📰</div>
                    <?php endif; ?>
                    <span class="blog-category"><?= htmlspecialchars($blog['category']) ?></span>
                </div>
                <div class="blog-body">
                    <div class="blog-meta">
                        <span><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg><?= date('d M Y', strtotime($blog['created_at'])) ?></span>
                        <span><?= htmlspecialchars($blog['author_name'] ?? 'Admin') ?></span>
                        <span><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg><?= number_format($blog['views']) ?></span>
                    </div>
                    <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($blog['slug']) ?>" class="blog-title"><?= htmlspecialchars($blog['title']) ?></a>
                    <p class="blog-excerpt"><?= htmlspecialchars($blog['excerpt'] ?? '') ?></p>
                    <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($blog['slug']) ?>" class="blog-readmore">
                        Read More <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ====== CTA SECTION ====== -->
<?php if (getSetting('section_cta', '1') === '1'): ?>
<section class="cta-section">
    <div class="container">
        <div class="grid-2 cta-content" style="align-items:center;gap:4rem">
            <div class="animate-fade-up">
                <div class="section-label" style="color:var(--gold)">Ready to Begin?</div>
                <h2 class="cta-title">Let's Find Your <em>Dream Property</em> Together</h2>
                <p class="cta-subtitle">Our dedicated team of experts is ready to guide you every step of the way. Get a free, no-obligation consultation today.</p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:2rem">
                    <a href="<?= SITE_URL ?>/contact.php" class="btn btn-gold btn-lg">Schedule Consultation</a>
                    <a href="tel:<?= preg_replace('/\s/', '', getSetting('contact_phone')) ?>" class="btn btn-outline-gold btn-lg">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                        Call Us Now
                    </a>
                </div>
            </div>
            <div class="animate-fade-up animate-delay-2">
                <div class="lead-form-wrapper">
                    <h3 class="form-title">Quick Enquiry</h3>
                    <p class="form-subtitle">Get a callback from our property experts</p>
                    <form action="<?= SITE_URL ?>/ajax.php" method="POST" data-lead-form>
                        <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                        <input type="hidden" name="source" value="cta_section">
                        <div class="form-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <input type="text" name="name" placeholder="Full Name *" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <input type="tel" name="phone" placeholder="Mobile Number *" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <select name="budget" class="form-control form-select">
                                    <option value="">Select Budget Range</option>
                                    <option>Under ₹50 Lakhs</option>
                                    <option>₹50L - ₹1 Crore</option>
                                    <option>₹1Cr - ₹2 Crore</option>
                                    <option>₹2Cr - ₹5 Crore</option>
                                    <option>Above ₹5 Crore</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea name="message" placeholder="Tell us what you're looking for..." class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-maroon btn-block btn-lg">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 2 15 22 11 13 2 9 22 2"></polyline></svg>
                                Send Enquiry
                            </button>
                        </div>
                        <div class="form-success">
                            <div class="form-success-icon">✅</div>
                            <h3>Enquiry Received!</h3>
                            <p>We'll call you back within 24 hours.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
