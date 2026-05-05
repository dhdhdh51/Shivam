<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'functions/functions.php';
$settings      = getAllSettings();
$currentPage   = '';
$pageMetaTitle = 'Privacy Policy | ' . ($settings['site_name'] ?? 'LuxeEstate Realty');
$pageMetaDesc  = 'Privacy Policy for ' . ($settings['site_name'] ?? 'LuxeEstate Realty');
require_once 'includes/header.php';
?>

<main>
  <!-- Page Banner -->
  <section class="page-banner" style="background:linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%); padding:80px 0 60px; margin-top:80px">
    <div class="container text-center text-white">
      <h1 class="display-5 fw-bold mb-2">Privacy Policy</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center mb-0">
          <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
          <li class="breadcrumb-item active text-white">Privacy Policy</li>
        </ol>
      </nav>
    </div>
  </section>

  <!-- Content -->
  <section style="padding:60px 0">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">
              <p class="text-muted mb-4"><i class="fas fa-calendar me-2"></i>Last updated: <?= date('F d, Y') ?></p>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">1. Information We Collect</h4>
                <p>When you use <?= htmlspecialchars($settings['site_name'] ?? SITE_NAME) ?>, we may collect the following types of information:</p>
                <ul>
                  <li><strong>Personal Information:</strong> Name, email address, phone number, and other contact details you provide when submitting enquiries or contact forms.</li>
                  <li><strong>Property Preferences:</strong> Budget, property type, location preferences, and other search criteria you provide.</li>
                  <li><strong>Technical Information:</strong> IP address, browser type, device information, and pages visited on our website.</li>
                  <li><strong>Cookies:</strong> We use cookies to improve your browsing experience and understand how visitors use our site.</li>
                </ul>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">2. How We Use Your Information</h4>
                <p>We use the collected information to:</p>
                <ul>
                  <li>Respond to your enquiries and provide property information</li>
                  <li>Connect you with suitable properties that match your requirements</li>
                  <li>Send you updates about new listings, offers, and real estate news (with your consent)</li>
                  <li>Improve our website and services</li>
                  <li>Comply with legal obligations</li>
                  <li>Prevent fraud and ensure security</li>
                </ul>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">3. Information Sharing</h4>
                <p>We respect your privacy. We do <strong>not</strong> sell, trade, or rent your personal information to third parties. We may share information only in these circumstances:</p>
                <ul>
                  <li><strong>Property Owners/Builders:</strong> To facilitate property enquiries with your explicit consent</li>
                  <li><strong>Service Providers:</strong> Trusted third parties who assist in operating our website (hosting, analytics)</li>
                  <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
                </ul>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">4. Data Security</h4>
                <p>We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no internet transmission is 100% secure, and we cannot guarantee absolute security.</p>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">5. Cookies Policy</h4>
                <p>Our website uses cookies to enhance your experience. You can choose to disable cookies through your browser settings, but this may affect some functionality of our website. We use:</p>
                <ul>
                  <li><strong>Essential Cookies:</strong> Required for the website to function properly</li>
                  <li><strong>Analytics Cookies:</strong> Help us understand how visitors interact with our site</li>
                  <li><strong>Preference Cookies:</strong> Remember your settings and preferences</li>
                </ul>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">6. Your Rights</h4>
                <p>You have the right to:</p>
                <ul>
                  <li>Access the personal information we hold about you</li>
                  <li>Request correction of inaccurate data</li>
                  <li>Request deletion of your personal data</li>
                  <li>Opt-out of marketing communications at any time</li>
                  <li>Lodge a complaint with the relevant data protection authority</li>
                </ul>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">7. Third-Party Links</h4>
                <p>Our website may contain links to third-party websites. We are not responsible for the privacy practices of these external sites and encourage you to review their privacy policies.</p>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">8. Changes to This Policy</h4>
                <p>We may update this Privacy Policy from time to time. We will notify you of significant changes by posting the new policy on this page with an updated date. Your continued use of our website after changes constitutes acceptance of the updated policy.</p>
              </div>

              <div class="policy-section">
                <h4 class="text-maroon fw-bold mb-3">9. Contact Us</h4>
                <p>If you have any questions about this Privacy Policy or how we handle your data, please contact us:</p>
                <div class="bg-beige p-4 rounded">
                  <p class="mb-1"><i class="fas fa-building me-2 text-maroon"></i><strong><?= htmlspecialchars($settings['site_name'] ?? SITE_NAME) ?></strong></p>
                  <?php if (!empty($settings['address'])): ?>
                    <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-maroon"></i><?= htmlspecialchars($settings['address']) ?></p>
                  <?php endif; ?>
                  <?php if (!empty($settings['email'])): ?>
                    <p class="mb-1"><i class="fas fa-envelope me-2 text-maroon"></i><a href="mailto:<?= htmlspecialchars($settings['email']) ?>"><?= htmlspecialchars($settings['email']) ?></a></p>
                  <?php endif; ?>
                  <?php if (!empty($settings['phone'])): ?>
                    <p class="mb-0"><i class="fas fa-phone me-2 text-maroon"></i><a href="tel:<?= htmlspecialchars($settings['phone']) ?>"><?= htmlspecialchars($settings['phone']) ?></a></p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<style>
.policy-section { border-left: 4px solid var(--gold); padding-left: 1.5rem; }
.policy-section ul { margin-top: 0.5rem; }
.policy-section li { margin-bottom: 0.4rem; }
.bg-beige { background: var(--beige) !important; }
</style>

<?php require_once 'includes/footer.php'; ?>
