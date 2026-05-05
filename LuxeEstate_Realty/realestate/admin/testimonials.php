<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../functions/functions.php';

session_start();
requireAdmin();

$db = db();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf)) {
        $errors[] = 'Invalid security token.';
    } else {
        $action   = $_POST['form_action'] ?? '';
        $id       = (int)($_POST['id'] ?? 0);
        $name        = sanitize($_POST['name'] ?? '');
        $review      = sanitize($_POST['review'] ?? '');
        $rating      = min(5, max(1, (int)($_POST['rating'] ?? 5)));
        $designation = sanitize($_POST['designation'] ?? '');
        $property    = sanitize($_POST['property_bought'] ?? '');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $status   = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';

        if (!$name)   $errors[] = 'Client name is required.';
        if (!$review) $errors[] = 'Review text is required.';

        if (empty($errors)) {
            $image_path = '';
            if ($id) {
                $ex = $db->prepare("SELECT image FROM testimonials WHERE id=?");
                $ex->execute([$id]);
                $row = $ex->fetch();
                $image_path = $row['image'] ?? '';
            }

            if (!empty($_FILES['image']['name'])) {
                $uploaded = uploadImage($_FILES['image'], 'testimonials/');
                if ($uploaded['success']) {
                    if ($image_path) {
                        $old = UPLOAD_DIR . 'testimonials/' . basename($image_path);
                        if (file_exists($old)) unlink($old);
                    }
                    $image_path = $uploaded['path'];
                } else {
                    $errors[] = $uploaded['message'];
                }
            }

            if (empty($errors)) {
                if ($action === 'add') {
                    $stmt = $db->prepare("INSERT INTO testimonials (name, designation, review, rating, property_bought, image, featured, status, created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
                    $stmt->execute([$name, $designation, $review, $rating, $property, $image_path, $featured, $status]);
                    $success = "Testimonial from <strong>$name</strong> added!";
                } elseif ($action === 'edit' && $id) {
                    $stmt = $db->prepare("UPDATE testimonials SET name=?,designation=?,review=?,rating=?,property_bought=?,image=?,featured=?,status=? WHERE id=?");
                    $stmt->execute([$name, $designation, $review, $rating, $property, $image_path, $featured, $status, $id]);
                    $success = "Testimonial from <strong>$name</strong> updated!";
                }
            }
        }
    }
}

$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = ADMIN_PER_PAGE;
$offset = ($page - 1) * $limit;
$filter_status = sanitize($_GET['status'] ?? '');

$where = '1=1'; $params = [];
if ($filter_status) { $where .= ' AND status=?'; $params[] = $filter_status; }

$total = $db->prepare("SELECT COUNT(*) FROM testimonials WHERE $where");
$total->execute($params);
$total = $total->fetchColumn();
$total_pages = ceil($total / $limit);

$stmt = $db->prepare("SELECT * FROM testimonials WHERE $where ORDER BY featured DESC, id DESC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$limit, $offset]));
$testimonials = $stmt->fetchAll();

$edit_t = null;
if (isset($_GET['edit'])) {
    $es = $db->prepare("SELECT * FROM testimonials WHERE id=?");
    $es->execute([(int)$_GET['edit']]);
    $edit_t = $es->fetch();
}

$csrf_token = generateCsrfToken();
$page_title = 'Testimonials';
require_once 'layout-header.php';
?>

<div class="admin-content">
  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1"><i class="fas fa-star me-2 text-gold"></i>Testimonials</h4>
      <p class="text-muted mb-0">Manage client reviews & testimonials</p>
    </div>
    <button class="btn btn-gold" onclick="toggleForm()">
      <i class="fas fa-plus me-2"></i>Add Testimonial
    </button>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= implode('<br>', $errors) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

  <!-- Form Panel -->
  <div class="card mb-4" id="formPanel" style="<?= ($edit_t || !empty($errors)) ? '' : 'display:none' ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fas fa-<?= $edit_t ? 'edit' : 'star' ?> me-2"></i><?= $edit_t ? 'Edit Testimonial' : 'Add New Testimonial' ?></h5>
      <button type="button" class="btn-close" onclick="toggleForm(false)"></button>
    </div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="form_action" value="<?= $edit_t ? 'edit' : 'add' ?>">
        <?php if ($edit_t): ?><input type="hidden" name="id" value="<?= $edit_t['id'] ?>"><?php endif; ?>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Client Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($edit_t['name'] ?? '') ?>" placeholder="e.g. Priya Sharma">
          </div>
          <div class="col-md-3">
            <label class="form-label">Rating <span class="text-danger">*</span></label>
            <select name="rating" class="form-select">
              <?php for ($r = 5; $r >= 1; $r--): ?>
                <option value="<?= $r ?>" <?= ($edit_t['rating'] ?? 5) == $r ? 'selected' : '' ?>><?= $r ?> Star<?= $r > 1 ? 's' : '' ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="active" <?= ($edit_t['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= ($edit_t['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Designation / Role</label>
            <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($edit_t['designation'] ?? '') ?>" placeholder="e.g. Software Engineer, Infosys">
          </div>
          <div class="col-md-6">
            <label class="form-label">Property Purchased</label>
            <input type="text" name="property_bought" class="form-control" value="<?= htmlspecialchars($edit_t['property_bought'] ?? '') ?>" placeholder="e.g. 3 BHK Apartment, Whitefield">
          </div>
          <div class="col-12">
            <label class="form-label">Review <span class="text-danger">*</span></label>
            <textarea name="review" class="form-control" rows="4" required placeholder="Write the client review here..."><?= htmlspecialchars($edit_t['review'] ?? '') ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Client Photo</label>
            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImg(this)">
            <div class="form-text">Optional. JPG/PNG, max 2MB</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Featured</label><br>
            <div class="form-check form-switch mt-2">
              <input class="form-check-input" type="checkbox" name="featured" id="featured" <?= ($edit_t['featured'] ?? 0) ? 'checked' : '' ?>>
              <label class="form-check-label" for="featured">Show on homepage</label>
            </div>
          </div>

          <?php if ($edit_t && $edit_t['image']): ?>
          <div class="col-12">
            <img src="<?= SITE_URL . '/' . $edit_t['image'] ?>" style="height:70px;width:70px;object-fit:cover;border-radius:50%;border:2px solid var(--gold)" alt="">
          </div>
          <?php endif; ?>
          <div id="imgPreviewWrap" style="display:none" class="col-12">
            <img id="imgPreview" src="" style="height:70px;width:70px;object-fit:cover;border-radius:50%;border:2px solid var(--gold)" alt="">
          </div>

          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-gold"><i class="fas fa-save me-2"></i><?= $edit_t ? 'Update' : 'Add Testimonial' ?></button>
            <a href="testimonials.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Filter -->
  <div class="card mb-4">
    <div class="card-body py-3">
      <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
          <select name="status" class="form-select">
            <option value="">All Status</option>
            <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $filter_status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-maroon w-100"><i class="fas fa-filter me-1"></i>Filter</button>
        </div>
        <div class="col-auto ms-auto text-muted small">Total: <strong><?= $total ?></strong> testimonials</div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th style="width:60px">#</th>
              <th>Client</th>
              <th>Rating</th>
              <th>Review</th>
              <th>Property</th>
              <th>Featured</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($testimonials)): ?>
              <tr><td colspan="8" class="text-center py-4 text-muted">No testimonials found.</td></tr>
            <?php else: foreach ($testimonials as $t): ?>
            <tr>
              <td><?= $t['id'] ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <?php
                    $tsrc = $t['image']
                        ? SITE_URL . '/' . $t['image']
                        : SITE_URL . '/assets/images/placeholder-avatar.svg';
                  ?>
                  <img src="<?= $tsrc ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover" alt="">
                  <div>
                    <div class="fw-semibold"><?= htmlspecialchars($t['name']) ?></div>
                    <?php if ($t['designation']): ?><small class="text-muted"><?= htmlspecialchars($t['designation']) ?></small><?php endif; ?>
                  </div>
                </div>
              </td>
              <td>
                <div class="text-warning">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star<?= $i > $t['rating'] ? '-o" style="color:#ddd' : '' ?>"></i>
                  <?php endfor; ?>
                </div>
              </td>
              <td style="max-width:240px">
                <span class="text-muted small"><?= htmlspecialchars(substr($t['review'], 0, 80)) ?>...</span>
              </td>
              <td><small><?= htmlspecialchars($t['property_bought'] ?? '—') ?></small></td>
              <td>
                <span class="badge <?= $t['featured'] ? 'bg-success' : 'bg-secondary' ?>">
                  <?= $t['featured'] ? 'Featured' : 'No' ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $t['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                  <?= ucfirst($t['status']) ?>
                </span>
              </td>
              <td class="text-end">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="?edit=<?= $t['id'] ?>" class="btn btn-sm btn-outline-maroon" title="Edit"><i class="fas fa-edit"></i></a>
                  <button class="btn btn-sm btn-outline-warning" onclick="toggleStatus(<?= $t['id'] ?>, 'testimonial', this)" title="Toggle Status">
                    <i class="fas fa-<?= $t['status'] === 'active' ? 'eye-slash' : 'eye' ?>"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $t['id'] ?>, 'testimonial', '<?= addslashes($t['name']) ?>')" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php if ($total_pages > 1): ?>
  <nav class="mt-4">
    <ul class="pagination justify-content-center">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filter_status) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
  <?php endif; ?>
</div>

<script>
function toggleForm(show) {
    const p = document.getElementById('formPanel');
    if (show === false) { p.style.display = 'none'; return; }
    p.style.display = p.style.display === 'none' ? '' : 'none';
    if (p.style.display !== 'none') p.scrollIntoView({behavior:'smooth',block:'start'});
}
function previewImg(input) {
    if (input.files && input.files[0]) {
        const r = new FileReader();
        r.onload = e => {
            document.getElementById('imgPreview').src = e.target.result;
            document.getElementById('imgPreviewWrap').style.display = '';
        };
        r.readAsDataURL(input.files[0]);
    }
}
function toggleStatus(id, type, btn) {
    if (!confirm('Toggle status?')) return;
    const fd = new FormData();
    fd.append('action', 'toggle_status');
    fd.append('id', id);
    fd.append('type', type);
    fd.append('csrf_token', '<?= generateCsrfToken() ?>');
    fetch('ajax.php', {method:'POST', body:fd})
        .then(r=>r.json()).then(d => { if(d.success) location.reload(); else alert(d.message); });
}
</script>

<?php require_once 'layout-footer.php'; ?>
