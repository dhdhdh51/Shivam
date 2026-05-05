<?php
/**
 * LuxeEstate Realty - Contact Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/functions.php';

$csrf          = generateCSRF();
$currentPage   = 'contact';
$siteName      = getSetting('site_name', 'LuxeEstate Realty');
$pageMetaTitle = "Contact Us | $siteName";
$pageMetaDesc  = "Get in touch with $siteName. We're here to help you find your perfect property.";

include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="page-hero-content">
        <h1>Contact <span>Us</span></h1>
        <p>We'd love to hear from you</p>
        <nav class="breadcrumb-nav">
            <a href="<?= SITE_URL ?>">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Contact</span>
        </nav>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section section-pad">
    <div class="container">
        <div class="contact-grid">

            <!-- Contact Info -->
            <div class="contact-info fade-left">
                <div class="section-label">Get In Touch</div>
                <h2 class="section-title">Let's Start a <span>Conversation</span></h2>
                <p class="contact-intro">
                    Whether you're looking to buy, sell, or invest — our experts are ready to guide you every step of the way.
                </p>

                <div class="contact-details">
                    <div class="contact-detail-item">
                        <div class="contact-detail-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="contact-detail-body">
                            <strong>Office Address</strong>
                            <span><?= nl2br(htmlspecialchars(getSetting('contact_address', '123 Real Estate Tower, MG Road, Bangalore - 560001'))) ?></span>
                        </div>
                    </div>
                    <div class="contact-detail-item">
                        <div class="contact-detail-icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="contact-detail-body">
                            <strong>Phone</strong>
                            <a href="tel:<?= preg_replace('/[^0-9+]/', '', getSetting('contact_phone', '+91 98765 43210')) ?>">
                                <?= htmlspecialchars(getSetting('contact_phone', '+91 98765 43210')) ?>
                            </a>
                        </div>
                    </div>
                    <div class="contact-detail-item">
                        <div class="contact-detail-icon"><i class="fas fa-envelope"></i></div>
                        <div class="contact-detail-body">
                            <strong>Email</strong>
                            <a href="mailto:<?= htmlspecialchars(getSetting('contact_email', 'info@luxestate.com')) ?>">
                                <?= htmlspecialchars(getSetting('contact_email', 'info@luxestate.com')) ?>
                            </a>
                        </div>
                    </div>
                    <div class="contact-detail-item">
                        <div class="contact-detail-icon"><i class="fas fa-clock"></i></div>
                        <div class="contact-detail-body">
                            <strong>Office Hours</strong>
                            <span><?= htmlspecialchars(getSetting('office_hours', 'Mon – Sat: 9:00 AM – 7:00 PM')) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="contact-social">
                    <?php $fb = getSetting('facebook_url'); $ig = getSetting('instagram_url'); $li = getSetting('linkedin_url'); $yt = getSetting('youtube_url'); ?>
                    <?php if ($fb): ?><a href="<?= htmlspecialchars($fb) ?>" target="_blank" class="social-link"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                    <?php if ($ig): ?><a href="<?= htmlspecialchars($ig) ?>" target="_blank" class="social-link"><i class="fab fa-instagram"></i></a><?php endif; ?>
                    <?php if ($li): ?><a href="<?= htmlspecialchars($li) ?>" target="_blank" class="social-link"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
                    <?php if ($yt): ?><a href="<?= htmlspecialchars($yt) ?>" target="_blank" class="social-link"><i class="fab fa-youtube"></i></a><?php endif; ?>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-wrap fade-right">
                <div class="contact-form-card">
                    <h3><i class="fas fa-paper-plane"></i> Send Us a Message</h3>
                    <form id="contactForm" novalidate>
                        <input type="hidden" name="action" value="contact_message">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="name" placeholder="Your name" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Phone Number *</label>
                                <input type="tel" name="phone" placeholder="+91 98765 43210" required class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" placeholder="your@email.com" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Subject</label>
                                <select name="subject" class="form-control">
                                    <option value="General Enquiry">General Enquiry</option>
                                    <option value="Buy Property">Buy Property</option>
                                    <option value="Sell Property">Sell Property</option>
                                    <option value="Rent Property">Rent Property</option>
                                    <option value="Investment Advice">Investment Advice</option>
                                    <option value="Legal Assistance">Legal Assistance</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Your Message *</label>
                            <textarea name="message" rows="5" placeholder="Tell us how we can help you..." required class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn-gold btn-full btn-lg">
                            <span class="btn-text"><i class="fas fa-paper-plane"></i> Send Message</span>
                            <span class="btn-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i> Sending...</span>
                        </button>
                        <div class="form-success" style="display:none;text-align:center;padding:20px">
                            <i class="fas fa-check-circle" style="font-size:40px;color:var(--maroon);margin-bottom:10px;display:block"></i>
                            <h4>Message Sent!</h4>
                            <p>Thank you for reaching out. We'll get back to you within 24 hours.</p>
                        </div>
                    </form>
                </div>
            </div>

        </div><!-- /.contact-grid -->

        <!-- Map Embed -->
        <?php $mapEmbed = getSetting('google_map_embed'); ?>
        <?php if ($mapEmbed): ?>
        <div class="map-section fade-up" style="margin-top:60px">
            <h3 class="section-title" style="margin-bottom:20px">Find <span>Our Office</span></h3>
            <div class="map-container">
                <?= $mapEmbed ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /.container -->
</section>

<!-- CTA Strip -->
<section class="cta-strip">
    <div class="container">
        <div class="cta-strip-content">
            <h2>Ready to Find Your Dream Property?</h2>
            <p>Browse our exclusive listings and connect with our experts today.</p>
        </div>
        <div class="cta-strip-actions">
            <a href="<?= SITE_URL ?>/properties.php" class="btn-gold">Browse Properties</a>
            <a href="tel:<?= preg_replace('/[^0-9+]/', '', getSetting('contact_phone')) ?>" class="btn-white-outline">
                <i class="fas fa-phone"></i> Call Now
            </a>
        </div>
    </div>
</section>

<script>
document.getElementById('contactForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form   = this;
    const btn    = form.querySelector('button[type=submit]');
    const data   = new FormData(form);
    btn.querySelector('.btn-text').style.display   = 'none';
    btn.querySelector('.btn-loading').style.display = 'inline';
    btn.disabled = true;

    fetch('<?= SITE_URL ?>/ajax.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                form.querySelector('.form-success').style.display = 'block';
                btn.style.display = 'none';
                Array.from(form.querySelectorAll('.form-row, .form-group:not(.hidden)')).forEach(el => el.style.display = 'none');
            } else {
                alert(res.message);
                btn.querySelector('.btn-text').style.display   = 'inline';
                btn.querySelector('.btn-loading').style.display = 'none';
                btn.disabled = false;
            }
        })
        .catch(() => {
            alert('Something went wrong. Please try again.');
            btn.querySelector('.btn-text').style.display   = 'inline';
            btn.querySelector('.btn-loading').style.display = 'none';
            btn.disabled = false;
        });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
