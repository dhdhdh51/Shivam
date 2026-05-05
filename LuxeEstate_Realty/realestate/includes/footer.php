<?php
$siteName    = getSetting('site_name', 'LuxeEstate Realty');
$address     = getSetting('contact_address');
$phone       = getSetting('contact_phone', '+91 98765 43210');
$phone2      = getSetting('contact_phone2');
$email       = getSetting('contact_email');
$whatsapp    = getSetting('whatsapp_number', '919876543210');
$facebook    = getSetting('facebook_url');
$instagram   = getSetting('instagram_url');
$youtube     = getSetting('youtube_url');
$linkedin    = getSetting('linkedin_url');
$footerAbout = getSetting('footer_about');
?>
<!-- POPUP -->
<?php if (getSetting('popup_enabled', '1') === '1'): ?>
<div class="popup-overlay" id="lead-popup" data-delay="<?= getSetting('popup_delay', '5000') ?>">
    <div class="popup-card">
        <button class="popup-close" aria-label="Close">✕</button>
        <div class="popup-badge">🏠 Limited Time Offer</div>
        <h2 class="popup-title"><?= getSetting('popup_title', 'Get Free Consultation') ?></h2>
        <p class="popup-subtitle">Let our experts help you find your perfect property. No obligations!</p>
        <form action="<?= SITE_URL ?>/ajax.php" method="POST" data-lead-form>
            <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
            <input type="hidden" name="source" value="popup">
            <div class="form-body">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Your Full Name *" class="form-control" required>
                </div>
                <div class="form-group">
                    <input type="tel" name="phone" placeholder="Mobile Number *" class="form-control" required pattern="[6-9][0-9]{9}">
                </div>
                <div class="form-group">
                    <select name="budget" class="form-control form-select">
                        <option value="">Budget Range</option>
                        <option>Under ₹50 Lakhs</option>
                        <option>₹50L - ₹1 Crore</option>
                        <option>₹1Cr - ₹2 Crore</option>
                        <option>₹2Cr - ₹5 Crore</option>
                        <option>Above ₹5 Crore</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-gold btn-block">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 2 15 22 11 13 2 9 22 2"></polyline></svg>
                    Get Free Consultation
                </button>
                <p style="font-size:0.72rem;color:var(--text-muted);text-align:center;margin-top:10px;">🔒 Your information is 100% confidential</p>
            </div>
            <div class="form-success">
                <div class="form-success-icon">🎉</div>
                <h3>Thank You!</h3>
                <p>Our expert will call you within 24 hours.</p>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- FOOTER -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- About -->
            <div>
                <a href="<?= SITE_URL ?>" class="logo footer-logo display-font"><?= htmlspecialchars($siteName) ?></a>
                <p class="footer-about"><?= htmlspecialchars($footerAbout) ?></p>
                <div class="social-links">
                    <?php if ($facebook): ?><a href="<?= $facebook ?>" target="_blank" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                    <?php if ($instagram): ?><a href="<?= $instagram ?>" target="_blank" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a><?php endif; ?>
                    <?php if ($youtube): ?><a href="<?= $youtube ?>" target="_blank" class="social-link" title="YouTube"><i class="fab fa-youtube"></i></a><?php endif; ?>
                    <?php if ($linkedin): ?><a href="<?= $linkedin ?>" target="_blank" class="social-link" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
                    <a href="https://wa.me/<?= $whatsapp ?>" target="_blank" class="social-link" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <!-- Quick Links -->
            <div>
                <h4 class="footer-heading">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/properties.php">All Properties</a></li>
                    <li><a href="<?= SITE_URL ?>/properties.php?type=apartment">Apartments</a></li>
                    <li><a href="<?= SITE_URL ?>/properties.php?type=villa">Villas</a></li>
                    <li><a href="<?= SITE_URL ?>/properties.php?type=commercial">Commercial</a></li>
                    <li><a href="<?= SITE_URL ?>/about.php">About Us</a></li>
                    <li><a href="<?= SITE_URL ?>/blog.php">Blog</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.php">Contact Us</a></li>
                </ul>
            </div>
            <!-- Property Types -->
            <div>
                <h4 class="footer-heading">Property Types</h4>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/properties.php?bhk=1">1 BHK</a></li>
                    <li><a href="<?= SITE_URL ?>/properties.php?bhk=2">2 BHK</a></li>
                    <li><a href="<?= SITE_URL ?>/properties.php?bhk=3">3 BHK</a></li>
                    <li><a href="<?= SITE_URL ?>/properties.php?bhk=4">4 BHK +</a></li>
                    <li><a href="<?= SITE_URL ?>/properties.php?type=penthouse">Penthouse</a></li>
                    <li><a href="<?= SITE_URL ?>/properties.php?type=plot">Plots / Land</a></li>
                </ul>
            </div>
            <!-- Contact -->
            <div>
                <h4 class="footer-heading">Get In Touch</h4>
                <div class="footer-contact-item">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span><?= htmlspecialchars($address) ?></span>
                </div>
                <div class="footer-contact-item">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                    <div><a href="tel:<?= preg_replace('/\s/', '', $phone) ?>" style="color:rgba(255,255,255,0.7)"><?= htmlspecialchars($phone) ?></a>
                    <?php if ($phone2): ?><br><a href="tel:<?= preg_replace('/\s/', '', $phone2) ?>" style="color:rgba(255,255,255,0.7)"><?= htmlspecialchars($phone2) ?></a><?php endif; ?></div>
                </div>
                <div class="footer-contact-item">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <a href="mailto:<?= htmlspecialchars($email) ?>" style="color:rgba(255,255,255,0.7)"><?= htmlspecialchars($email) ?></a>
                </div>
                <a href="https://wa.me/<?= $whatsapp ?>?text=Hi%2C+I+need+property+assistance" target="_blank" class="btn btn-outline-gold btn-sm mt-3" style="margin-top:1rem">
                    <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                </a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. All Rights Reserved.</p>
            <div style="display:flex;gap:1.5rem">
                <a href="<?= SITE_URL ?>/privacy.php">Privacy Policy</a>
                <a href="<?= SITE_URL ?>/terms.php">Terms of Use</a>
                <a href="<?= SITE_URL ?>/sitemap.xml" style="color:rgba(255,255,255,0.4)">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<!-- STICKY BUTTONS -->
<div class="sticky-buttons">
    <a href="tel:<?= preg_replace('/\s/', '', $phone) ?>" class="sticky-btn sticky-call" title="Call Now">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
    </a>
    <a href="https://wa.me/<?= $whatsapp ?>?text=Hi%2C+I+need+property+assistance" target="_blank" class="sticky-btn sticky-whatsapp" title="WhatsApp">
        <i class="fab fa-whatsapp" style="font-size:1.4rem"></i>
    </a>
</div>

<!-- BOTTOM CTA BAR (mobile) -->
<div class="bottom-cta-bar">
    <a href="tel:<?= preg_replace('/\s/', '', $phone) ?>" class="btn btn-maroon" style="flex:1;justify-content:center">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
        Call Now
    </a>
    <a href="https://wa.me/<?= $whatsapp ?>?text=Hi%2C+I+need+property+assistance" target="_blank" class="btn" style="flex:1;justify-content:center;background:#25D366;color:white">
        <i class="fab fa-whatsapp"></i> WhatsApp
    </a>
</div>

<button class="back-to-top" title="Back to top" aria-label="Back to top">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"></polyline></svg>
</button>

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
