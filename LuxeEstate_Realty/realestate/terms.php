<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'functions/functions.php';
$settings      = getAllSettings();
$currentPage   = '';
$pageMetaTitle = 'Terms & Conditions | ' . ($settings['site_name'] ?? 'LuxeEstate Realty');
$pageMetaDesc  = 'Terms and Conditions for ' . ($settings['site_name'] ?? 'LuxeEstate Realty');
require_once 'includes/header.php';
?>

<main>
  <!-- Page Banner -->
  <section class="page-banner" style="background:linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%); padding:80px 0 60px; margin-top:80px">
    <div class="container text-center text-white">
      <h1 class="display-5 fw-bold mb-2">Terms &amp; Conditions</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center mb-0">
          <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
          <li class="breadcrumb-item active text-white">Terms &amp; Conditions</li>
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
              <p class="lead mb-4">Please read these Terms and Conditions carefully before using our website and services. By accessing or using <?= htmlspecialchars($settings['site_name'] ?? SITE_NAME) ?>, you agree to be bound by these terms.</p>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">1. Acceptance of Terms</h4>
                <p>By accessing and using this website, you accept and agree to be bound by these Terms and Conditions and our Privacy Policy. If you do not agree to these terms, please do not use our services.</p>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">2. Services Description</h4>
                <p><?= htmlspecialchars($settings['site_name'] ?? SITE_NAME) ?> provides real estate services including:</p>
                <ul>
                  <li>Property listing and search services</li>
                  <li>Property buying, selling, and rental assistance</li>
                  <li>Real estate consultation and advisory services</li>
                  <li>Property investment guidance</li>
                  <li>Real estate market information and insights</li>
                </ul>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">3. Property Information Disclaimer</h4>
                <p>While we strive to provide accurate and up-to-date property information:</p>
                <ul>
                  <li>Property details, prices, and availability are subject to change without notice</li>
                  <li>All property listings are for informational purposes only</li>
                  <li>We do not guarantee the accuracy, completeness, or timeliness of property information</li>
                  <li>Users should verify all information independently before making any property decisions</li>
                  <li>Property prices listed are indicative and may vary based on negotiations and market conditions</li>
                </ul>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">4. User Obligations</h4>
                <p>As a user of our website, you agree to:</p>
                <ul>
                  <li>Provide accurate and truthful information when submitting enquiries</li>
                  <li>Use our services only for lawful purposes</li>
                  <li>Not engage in any activity that could harm our website or other users</li>
                  <li>Not attempt to gain unauthorized access to our systems</li>
                  <li>Respect intellectual property rights</li>
                  <li>Not use our platform for spamming or unsolicited communications</li>
                </ul>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">5. Intellectual Property</h4>
                <p>All content on this website, including text, graphics, logos, images, and software, is the property of <?= htmlspecialchars($settings['site_name'] ?? SITE_NAME) ?> or its content suppliers and is protected by applicable intellectual property laws. You may not reproduce, distribute, or create derivative works without our explicit written permission.</p>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">6. Limitation of Liability</h4>
                <p><?= htmlspecialchars($settings['site_name'] ?? SITE_NAME) ?> shall not be liable for:</p>
                <ul>
                  <li>Any direct, indirect, incidental, or consequential damages arising from website use</li>
                  <li>Losses resulting from reliance on property information provided on the website</li>
                  <li>Interruptions or errors in website availability</li>
                  <li>Actions of third parties including property owners, builders, or sellers</li>
                  <li>Any investment or financial losses related to property transactions</li>
                </ul>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">7. Commission & Fees</h4>
                <p>Our commission and service fee structure will be disclosed and agreed upon before entering into any formal service agreement. All fees are subject to applicable taxes as per local regulations. We reserve the right to modify our fee structure with prior notice.</p>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">8. Third-Party Services</h4>
                <p>Our website may integrate with third-party services such as payment gateways, map services, and communication platforms. Use of these services is subject to their respective terms and conditions. We are not responsible for the practices of these third-party providers.</p>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">9. Dispute Resolution</h4>
                <p>Any disputes arising from these terms or our services shall be:</p>
                <ul>
                  <li>First attempted to be resolved through mutual discussion and negotiation</li>
                  <li>Subject to the jurisdiction of courts in <?= isset($settings['contact_address']) ? htmlspecialchars(explode(',', $settings['contact_address'])[0]) : 'our registered city' ?></li>
                  <li>Governed by applicable Indian laws and regulations</li>
                </ul>
              </div>

              <div class="policy-section mb-4">
                <h4 class="text-maroon fw-bold mb-3">10. Modifications to Terms</h4>
                <p>We reserve the right to modify these Terms and Conditions at any time. Changes will be effective immediately upon posting to the website. Your continued use of our services constitutes acceptance of the modified terms.</p>
              </div>

              <div class="policy-section">
                <h4 class="text-maroon fw-bold mb-3">11. Contact Information</h4>
                <p>For questions regarding these Terms and Conditions, please contact us:</p>
                <div class="bg-beige p-4 rounded">
                  <p class="mb-1"><i class="fas fa-building me-2 text-maroon"></i><strong><?= htmlspecialchars($settings['site_name'] ?? SITE_NAME) ?></strong></p>
                  <?php if (!empty($settings['contact_address'])): ?>
                    <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-maroon"></i><?= htmlspecialchars($settings['contact_address']) ?></p>
                  <?php endif; ?>
                  <?php if (!empty($settings['contact_email'])): ?>
                    <p class="mb-1"><i class="fas fa-envelope me-2 text-maroon"></i><a href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>"><?= htmlspecialchars($settings['contact_email']) ?></a></p>
                  <?php endif; ?>
                  <?php if (!empty($settings['contact_phone'])): ?>
                    <p class="mb-0"><i class="fas fa-phone me-2 text-maroon"></i><a href="tel:<?= htmlspecialchars($settings['contact_phone']) ?>"><?= htmlspecialchars($settings['contact_phone']) ?></a></p>
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
