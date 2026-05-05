<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../functions/functions.php';

session_start();
requireAdmin();

$db = db();
$errors  = [];
$success = '';

// ── Save settings ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf)) {
        $errors[] = 'Invalid security token.';
    } else {
        $tab = $_POST['active_tab'] ?? 'general';

        $fields = [];

        if ($tab === 'general') {
            $fields = [
                'site_name'        => sanitize($_POST['site_name'] ?? ''),
                'site_tagline'     => sanitize($_POST['site_tagline'] ?? ''),
                'site_description' => sanitize($_POST['site_description'] ?? ''),
                'footer_text'      => sanitize($_POST['footer_text'] ?? ''),
                'established_year' => sanitize($_POST['established_year'] ?? ''),
                'properties_count' => sanitize($_POST['properties_count'] ?? ''),
                'clients_count'    => sanitize($_POST['clients_count'] ?? ''),
                'experience_years' => sanitize($_POST['experience_years'] ?? ''),
            ];
            // Logo upload
            if (!empty($_FILES['logo']['name'])) {
                $uploaded = uploadImage($_FILES['logo'], 'settings/');
                if ($uploaded['success']) {
                    $fields['logo'] = $uploaded['path'];
                } else {
                    $errors[] = 'Logo: ' . $uploaded['message'];
                }
            }
            // Favicon upload
            if (!empty($_FILES['favicon']['name'])) {
                $uploaded = uploadImage($_FILES['favicon'], 'settings/');
                if ($uploaded['success']) {
                    $fields['favicon'] = $uploaded['path'];
                } else {
                    $errors[] = 'Favicon: ' . $uploaded['message'];
                }
            }
        }

        if ($tab === 'contact') {
            $fields = [
                'phone'         => sanitize($_POST['phone'] ?? ''),
                'phone_alt'     => sanitize($_POST['phone_alt'] ?? ''),
                'email'         => sanitize($_POST['email'] ?? ''),
                'email_alt'     => sanitize($_POST['email_alt'] ?? ''),
                'address'       => sanitize($_POST['address'] ?? ''),
                'whatsapp'      => sanitize($_POST['whatsapp'] ?? ''),
                'map_embed'     => $_POST['map_embed'] ?? '',
                'office_hours'  => sanitize($_POST['office_hours'] ?? ''),
            ];
        }

        if ($tab === 'social') {
            $fields = [
                'facebook'   => sanitize($_POST['facebook'] ?? ''),
                'instagram'  => sanitize($_POST['instagram'] ?? ''),
                'twitter'    => sanitize($_POST['twitter'] ?? ''),
                'youtube'    => sanitize($_POST['youtube'] ?? ''),
                'linkedin'   => sanitize($_POST['linkedin'] ?? ''),
                'pinterest'  => sanitize($_POST['pinterest'] ?? ''),
            ];
        }

        if ($tab === 'seo') {
            $fields = [
                'meta_title'       => sanitize($_POST['meta_title'] ?? ''),
                'meta_description' => sanitize($_POST['meta_description'] ?? ''),
                'meta_keywords'    => sanitize($_POST['meta_keywords'] ?? ''),
                'google_analytics' => sanitize($_POST['google_analytics'] ?? ''),
                'google_tag'       => sanitize($_POST['google_tag'] ?? ''),
                'schema_markup'    => sanitize($_POST['schema_markup'] ?? ''),
            ];
        }

        if ($tab === 'sections') {
            $section_keys = ['show_hero','show_featured','show_stats','show_services','show_testimonials','show_team','show_blog','show_cta','show_popup_lead','show_sticky_buttons'];
            foreach ($section_keys as $k) {
                $fields[$k] = isset($_POST[$k]) ? '1' : '0';
            }
        }

        if ($tab === 'email') {
            $fields = [
                'smtp_host'     => sanitize($_POST['smtp_host'] ?? ''),
                'smtp_port'     => sanitize($_POST['smtp_port'] ?? ''),
                'smtp_user'     => sanitize($_POST['smtp_user'] ?? ''),
                'smtp_pass'     => sanitize($_POST['smtp_pass'] ?? ''),
                'smtp_from'     => sanitize($_POST['smtp_from'] ?? ''),
                'smtp_from_name'=> sanitize($_POST['smtp_from_name'] ?? ''),
                'smtp_secure'   => sanitize($_POST['smtp_secure'] ?? 'tls'),
                'notify_email'  => sanitize($_POST['notify_email'] ?? ''),
            ];
        }

        if (empty($errors)) {
            foreach ($fields as $key => $value) {
                updateSetting($key, $value);
            }
            $success = 'Settings saved successfully!';
        }
    }
}

// Load all settings
$settings = getAllSettings();
$s = $settings; // shorthand

$csrf_token = generateCsrfToken();
$active_tab = $_POST['active_tab'] ?? ($_GET['tab'] ?? 'general');
$page_title = 'Settings';
require_once 'layout-header.php';
?>

<div class="admin-content">
  <div class="page-header mb-4">
    <h4 class="mb-1"><i class="fas fa-cog me-2 text-gold"></i>Site Settings</h4>
    <p class="text-muted mb-0">Configure all aspects of your website</p>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= implode('<br>', $errors) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Sidebar Tabs -->
    <div class="col-md-3">
      <div class="card sticky-top" style="top:80px">
        <div class="card-body p-2">
          <nav class="nav flex-column" id="settingsTabs">
            <?php
            $tabs = [
                'general'  => ['icon'=>'fa-home',        'label'=>'General'],
                'contact'  => ['icon'=>'fa-phone',       'label'=>'Contact'],
                'social'   => ['icon'=>'fa-share-alt',   'label'=>'Social Media'],
                'seo'      => ['icon'=>'fa-search',      'label'=>'SEO & Analytics'],
                'sections' => ['icon'=>'fa-toggle-on',   'label'=>'Section Control'],
                'email'    => ['icon'=>'fa-envelope',    'label'=>'Email / SMTP'],
            ];
            foreach ($tabs as $key => $tab):
            ?>
              <a href="#" class="nav-link settings-tab-link px-3 py-2 rounded <?= $active_tab === $key ? 'active bg-maroon text-white' : 'text-dark' ?>" data-tab="<?= $key ?>">
                <i class="fas <?= $tab['icon'] ?> me-2"></i><?= $tab['label'] ?>
              </a>
            <?php endforeach; ?>
          </nav>
        </div>
      </div>
    </div>

    <!-- Settings Form -->
    <div class="col-md-9">
      <form method="POST" enctype="multipart/form-data" id="settingsForm">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="active_tab" id="activeTabInput" value="<?= $active_tab ?>">

        <!-- GENERAL -->
        <div class="settings-panel card" id="panel-general" style="<?= $active_tab !== 'general' ? 'display:none' : '' ?>">
          <div class="card-header"><h5 class="mb-0"><i class="fas fa-home me-2"></i>General Settings</h5></div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($s['site_name'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Tagline</label>
                <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars($s['site_tagline'] ?? '') ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Site Description</label>
                <textarea name="site_description" class="form-control" rows="3"><?= htmlspecialchars($s['site_description'] ?? '') ?></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="form-control" accept="image/*">
                <?php if (!empty($s['logo'])): ?>
                  <div class="mt-2"><img src="<?= SITE_URL . '/' . $s['logo'] ?>" style="max-height:50px" alt="Logo"></div>
                <?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Favicon</label>
                <input type="file" name="favicon" class="form-control" accept="image/*">
                <?php if (!empty($s['favicon'])): ?>
                  <div class="mt-2"><img src="<?= SITE_URL . '/' . $s['favicon'] ?>" style="max-height:32px" alt="Favicon"></div>
                <?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Established Year</label>
                <input type="text" name="established_year" class="form-control" value="<?= htmlspecialchars($s['established_year'] ?? '') ?>" placeholder="e.g. 2010">
              </div>
              <div class="col-md-6">
                <label class="form-label">Footer Text</label>
                <input type="text" name="footer_text" class="form-control" value="<?= htmlspecialchars($s['footer_text'] ?? '') ?>">
              </div>
              <div class="col-12"><hr><h6 class="text-muted">Homepage Stats</h6></div>
              <div class="col-md-4">
                <label class="form-label">Properties Sold</label>
                <input type="text" name="properties_count" class="form-control" value="<?= htmlspecialchars($s['properties_count'] ?? '500+') ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Happy Clients</label>
                <input type="text" name="clients_count" class="form-control" value="<?= htmlspecialchars($s['clients_count'] ?? '1200+') ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Years Experience</label>
                <input type="text" name="experience_years" class="form-control" value="<?= htmlspecialchars($s['experience_years'] ?? '15+') ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- CONTACT -->
        <div class="settings-panel card" id="panel-contact" style="<?= $active_tab !== 'contact' ? 'display:none' : '' ?>">
          <div class="card-header"><h5 class="mb-0"><i class="fas fa-phone me-2"></i>Contact Information</h5></div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Primary Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($s['phone'] ?? '') ?>" placeholder="+91 98765 43210">
              </div>
              <div class="col-md-6">
                <label class="form-label">Alternate Phone</label>
                <input type="text" name="phone_alt" class="form-control" value="<?= htmlspecialchars($s['phone_alt'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Primary Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($s['email'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Alternate Email</label>
                <input type="email" name="email_alt" class="form-control" value="<?= htmlspecialchars($s['email_alt'] ?? '') ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Office Address</label>
                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($s['address'] ?? '') ?></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">WhatsApp Number</label>
                <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($s['whatsapp'] ?? '') ?>" placeholder="919876543210 (with country code, no +)">
              </div>
              <div class="col-md-6">
                <label class="form-label">Office Hours</label>
                <input type="text" name="office_hours" class="form-control" value="<?= htmlspecialchars($s['office_hours'] ?? '') ?>" placeholder="Mon-Sat: 9AM - 7PM">
              </div>
              <div class="col-12">
                <label class="form-label">Google Maps Embed URL/Code</label>
                <textarea name="map_embed" class="form-control" rows="4" placeholder="Paste your Google Maps embed iframe code here..."><?= htmlspecialchars($s['map_embed'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- SOCIAL -->
        <div class="settings-panel card" id="panel-social" style="<?= $active_tab !== 'social' ? 'display:none' : '' ?>">
          <div class="card-header"><h5 class="mb-0"><i class="fas fa-share-alt me-2"></i>Social Media Links</h5></div>
          <div class="card-body">
            <div class="row g-3">
              <?php $socials = [
                'facebook'  => ['icon'=>'fa-facebook-f',  'color'=>'#1877F2', 'label'=>'Facebook'],
                'instagram' => ['icon'=>'fa-instagram',   'color'=>'#E4405F', 'label'=>'Instagram'],
                'twitter'   => ['icon'=>'fa-twitter',     'color'=>'#1DA1F2', 'label'=>'Twitter / X'],
                'youtube'   => ['icon'=>'fa-youtube',     'color'=>'#FF0000', 'label'=>'YouTube'],
                'linkedin'  => ['icon'=>'fa-linkedin-in', 'color'=>'#0A66C2', 'label'=>'LinkedIn'],
                'pinterest' => ['icon'=>'fa-pinterest-p', 'color'=>'#E60023', 'label'=>'Pinterest'],
              ]; foreach ($socials as $key => $social): ?>
              <div class="col-md-6">
                <label class="form-label">
                  <i class="fab <?= $social['icon'] ?> me-1" style="color:<?= $social['color'] ?>"></i>
                  <?= $social['label'] ?>
                </label>
                <input type="url" name="<?= $key ?>" class="form-control" value="<?= htmlspecialchars($s[$key] ?? '') ?>" placeholder="https://...">
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- SEO -->
        <div class="settings-panel card" id="panel-seo" style="<?= $active_tab !== 'seo' ? 'display:none' : '' ?>">
          <div class="card-header"><h5 class="mb-0"><i class="fas fa-search me-2"></i>SEO & Analytics</h5></div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Default Meta Title</label>
                <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($s['meta_title'] ?? '') ?>" maxlength="70">
                <div class="form-text">Recommended: 50-60 characters</div>
              </div>
              <div class="col-12">
                <label class="form-label">Default Meta Description</label>
                <textarea name="meta_description" class="form-control" rows="3" maxlength="160"><?= htmlspecialchars($s['meta_description'] ?? '') ?></textarea>
                <div class="form-text">Recommended: 150-160 characters</div>
              </div>
              <div class="col-12">
                <label class="form-label">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control" value="<?= htmlspecialchars($s['meta_keywords'] ?? '') ?>" placeholder="real estate, property, apartments, ...">
              </div>
              <div class="col-md-6">
                <label class="form-label">Google Analytics ID</label>
                <input type="text" name="google_analytics" class="form-control" value="<?= htmlspecialchars($s['google_analytics'] ?? '') ?>" placeholder="G-XXXXXXXXXX or UA-XXXXXXXXX">
              </div>
              <div class="col-md-6">
                <label class="form-label">Google Tag Manager ID</label>
                <input type="text" name="google_tag" class="form-control" value="<?= htmlspecialchars($s['google_tag'] ?? '') ?>" placeholder="GTM-XXXXXXX">
              </div>
            </div>
          </div>
        </div>

        <!-- SECTIONS -->
        <div class="settings-panel card" id="panel-sections" style="<?= $active_tab !== 'sections' ? 'display:none' : '' ?>">
          <div class="card-header"><h5 class="mb-0"><i class="fas fa-toggle-on me-2"></i>Homepage Section Control</h5></div>
          <div class="card-body">
            <p class="text-muted mb-3">Toggle which sections appear on your homepage.</p>
            <div class="row g-3">
              <?php
              $section_opts = [
                'show_hero'           => ['icon'=>'fa-image',         'label'=>'Hero / Banner Section',          'desc'=>'Full-width hero with search'],
                'show_featured'       => ['icon'=>'fa-home',          'label'=>'Featured Properties',            'desc'=>'Grid of featured listings'],
                'show_stats'          => ['icon'=>'fa-chart-bar',     'label'=>'Statistics Counter',             'desc'=>'Properties sold, clients, years'],
                'show_services'       => ['icon'=>'fa-concierge-bell','label'=>'Services Section',               'desc'=>'What we offer cards'],
                'show_testimonials'   => ['icon'=>'fa-quote-right',   'label'=>'Testimonials / Reviews',         'desc'=>'Client testimonial slider'],
                'show_team'           => ['icon'=>'fa-users',         'label'=>'Team Section',                   'desc'=>'Meet our team cards'],
                'show_blog'           => ['icon'=>'fa-blog',          'label'=>'Latest Blog Posts',              'desc'=>'Recent articles on homepage'],
                'show_cta'            => ['icon'=>'fa-bullhorn',      'label'=>'Call-to-Action Section',         'desc'=>'Bottom CTA / contact banner'],
                'show_popup_lead'     => ['icon'=>'fa-bell',          'label'=>'Lead Popup Form',                'desc'=>'Triggered on scroll'],
                'show_sticky_buttons' => ['icon'=>'fa-thumbtack',     'label'=>'Sticky Call / WhatsApp Buttons', 'desc'=>'Floating action buttons'],
              ];
              foreach ($section_opts as $key => $opt):
                $checked = ($s[$key] ?? '1') === '1';
              ?>
              <div class="col-md-6">
                <div class="card border p-3 h-100 <?= $checked ? 'border-success' : 'border-secondary' ?>">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex gap-3 align-items-center">
                      <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:var(--beige-dark)">
                        <i class="fas <?= $opt['icon'] ?> text-maroon"></i>
                      </div>
                      <div>
                        <div class="fw-semibold"><?= $opt['label'] ?></div>
                        <small class="text-muted"><?= $opt['desc'] ?></small>
                      </div>
                    </div>
                    <div class="form-check form-switch ms-3">
                      <input class="form-check-input section-toggle" type="checkbox" name="<?= $key ?>" id="<?= $key ?>" <?= $checked ? 'checked' : '' ?> onchange="updateSectionCard(this)">
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- EMAIL / SMTP -->
        <div class="settings-panel card" id="panel-email" style="<?= $active_tab !== 'email' ? 'display:none' : '' ?>">
          <div class="card-header"><h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email / SMTP Configuration</h5></div>
          <div class="card-body">
            <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Configure SMTP to send lead notification emails.</div>
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label">SMTP Host</label>
                <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($s['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
              </div>
              <div class="col-md-4">
                <label class="form-label">SMTP Port</label>
                <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($s['smtp_port'] ?? '587') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">SMTP Username</label>
                <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($s['smtp_user'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">SMTP Password</label>
                <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($s['smtp_pass'] ?? '') ?>" placeholder="Leave blank to keep current">
              </div>
              <div class="col-md-6">
                <label class="form-label">From Email</label>
                <input type="email" name="smtp_from" class="form-control" value="<?= htmlspecialchars($s['smtp_from'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">From Name</label>
                <input type="text" name="smtp_from_name" class="form-control" value="<?= htmlspecialchars($s['smtp_from_name'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Encryption</label>
                <select name="smtp_secure" class="form-select">
                  <option value="tls" <?= ($s['smtp_secure'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recommended)</option>
                  <option value="ssl" <?= ($s['smtp_secure'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                  <option value="" <?= ($s['smtp_secure'] ?? '') === '' ? 'selected' : '' ?>>None</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Notification Email</label>
                <input type="email" name="notify_email" class="form-control" value="<?= htmlspecialchars($s['notify_email'] ?? '') ?>" placeholder="Admin email to receive lead alerts">
              </div>
            </div>
          </div>
        </div>

        <!-- Save Button -->
        <div class="d-flex justify-content-end gap-2 mt-3">
          <button type="submit" class="btn btn-gold btn-lg px-5">
            <i class="fas fa-save me-2"></i>Save Settings
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Tab switching
document.querySelectorAll('.settings-tab-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const tab = this.dataset.tab;
        document.getElementById('activeTabInput').value = tab;
        document.querySelectorAll('.settings-panel').forEach(p => p.style.display = 'none');
        document.getElementById('panel-' + tab).style.display = '';
        document.querySelectorAll('.settings-tab-link').forEach(l => {
            l.classList.remove('active', 'bg-maroon', 'text-white');
            l.classList.add('text-dark');
        });
        this.classList.add('active', 'bg-maroon', 'text-white');
        this.classList.remove('text-dark');
    });
});

function updateSectionCard(checkbox) {
    const card = checkbox.closest('.card');
    if (checkbox.checked) {
        card.classList.remove('border-secondary');
        card.classList.add('border-success');
    } else {
        card.classList.remove('border-success');
        card.classList.add('border-secondary');
    }
}
</script>

<?php require_once 'layout-footer.php'; ?>
