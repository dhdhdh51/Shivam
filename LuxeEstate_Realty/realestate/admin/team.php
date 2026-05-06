<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../functions/functions.php';

session_start();
requireAdmin();

$db = db();
$errors = [];
$success = '';

// ── Handle form submission ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validateCSRF($csrf)) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['form_action'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);
        $name   = sanitize($_POST['name'] ?? '');
        $role   = sanitize($_POST['role'] ?? '');
        $bio    = sanitize($_POST['bio'] ?? '');
        $phone  = sanitize($_POST['phone'] ?? '');
        $email  = sanitize($_POST['email'] ?? '');
        $sort   = (int)($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';

        if (!$name) $errors[] = 'Name is required.';
        if (!$role) $errors[] = 'Role/designation is required.';

        if (empty($errors)) {
            // Handle image upload
            $image_path = '';
            if ($id) {
                $existing = $db->prepare("SELECT image FROM team_members WHERE id=?");
                $existing->execute([$id]);
                $row = $existing->fetch();
                $image_path = $row['image'] ?? '';
            }

            if (!empty($_FILES['image']['name'])) {
                $uploaded = uploadImage($_FILES['image'], 'team/');
                if ($uploaded['success']) {
                    // Delete old image
                    if ($image_path) {
                        $old = UPLOAD_DIR . 'team/' . basename($image_path);
                        if (file_exists($old)) unlink($old);
                    }
                    $image_path = $uploaded['path'];
                } else {
                    $errors[] = $uploaded['message'];
                }
            }

            if (empty($errors)) {
                if ($action === 'add') {
                    $stmt = $db->prepare("INSERT INTO team_members (name, role, bio, phone, email, image, sort_order, status, created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
                    $stmt->execute([$name, $role, $bio, $phone, $email, $image_path, $sort, $status]);
                    $success = "Team member <strong>$name</strong> added successfully!";
                } elseif ($action === 'edit' && $id) {
                    $stmt = $db->prepare("UPDATE team_members SET name=?,role=?,bio=?,phone=?,email=?,image=?,sort_order=?,status=? WHERE id=?");
                    $stmt->execute([$name, $role, $bio, $phone, $email, $image_path, $sort, $status, $id]);
                    $success = "Team member <strong>$name</strong> updated successfully!";
                }
            }
        }
    }
}

// ── Fetch team members ──
$search = sanitize($_GET['search'] ?? '');
$filter_status = sanitize($_GET['status'] ?? '');
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = ADMIN_PER_PAGE;
$offset = ($page - 1) * $limit;

$where = '1=1';
$params = [];
if ($search) { $where .= ' AND (name LIKE ? OR role LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($filter_status) { $where .= ' AND status = ?'; $params[] = $filter_status; }

$total_stmt = $db->prepare("SELECT COUNT(*) FROM team_members WHERE $where");
$total_stmt->execute($params);
$total = $total_stmt->fetchColumn();
$total_pages = ceil($total / $limit);

$params_paged = array_merge($params, [$limit, $offset]);
$members_stmt = $db->prepare("SELECT * FROM team_members WHERE $where ORDER BY sort_order ASC, id DESC LIMIT ? OFFSET ?");
$members_stmt->execute($params_paged);
$members = $members_stmt->fetchAll();

// Edit mode
$edit_member = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_stmt = $db->prepare("SELECT * FROM team_members WHERE id=?");
    $edit_stmt->execute([$edit_id]);
    $edit_member = $edit_stmt->fetch();
}

$csrf_token = generateCSRF();
$page_title = 'Team Management';
require_once 'layout-header.php';
?>

<div class="admin-content">
  <!-- Page Header -->
  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1"><i class="fas fa-users me-2 text-gold"></i>Team Management</h4>
      <p class="text-muted mb-0">Manage your team members displayed on the website</p>
    </div>
    <button class="btn btn-gold" onclick="toggleAddForm()">
      <i class="fas fa-plus me-2"></i>Add Member
    </button>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= implode('<br>', $errors) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

  <!-- Add/Edit Form Panel -->
  <div class="card mb-4" id="memberFormPanel" style="<?= ($edit_member || !empty($errors)) ? '' : 'display:none' ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fas fa-<?= $edit_member ? 'edit' : 'user-plus' ?> me-2"></i><?= $edit_member ? 'Edit Member' : 'Add New Member' ?></h5>
      <button type="button" class="btn-close" onclick="toggleAddForm(false)"></button>
    </div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="form_action" value="<?= $edit_member ? 'edit' : 'add' ?>">
        <?php if ($edit_member): ?>
          <input type="hidden" name="id" value="<?= $edit_member['id'] ?>">
        <?php endif; ?>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($edit_member['name'] ?? ($_POST['name'] ?? '')) ?>" placeholder="e.g. Rahul Sharma">
          </div>
          <div class="col-md-6">
            <label class="form-label">Role / Designation <span class="text-danger">*</span></label>
            <input type="text" name="role" class="form-control" required value="<?= htmlspecialchars($edit_member['role'] ?? ($_POST['role'] ?? '')) ?>" placeholder="e.g. Senior Property Consultant">
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($edit_member['phone'] ?? ($_POST['phone'] ?? '')) ?>" placeholder="+91 98765 43210">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($edit_member['email'] ?? ($_POST['email'] ?? '')) ?>" placeholder="member@realty.com">
          </div>
          <div class="col-12">
            <label class="form-label">Bio / Description</label>
            <textarea name="bio" class="form-control" rows="3" placeholder="Brief description about this team member..."><?= htmlspecialchars($edit_member['bio'] ?? ($_POST['bio'] ?? '')) ?></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label">Photo</label>
            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewMemberImage(this)">
            <div class="form-text">JPG/PNG, max 5MB</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="<?= $edit_member['sort_order'] ?? ($_POST['sort_order'] ?? 0) ?>" min="0" placeholder="0">
            <div class="form-text">Lower = appears first</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="active" <?= ($edit_member['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= ($edit_member['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>

          <?php if ($edit_member && $edit_member['image']): ?>
          <div class="col-12">
            <label class="form-label">Current Photo</label><br>
            <img src="<?= SITE_URL . '/' . $edit_member['image'] ?>" alt="" style="height:100px;width:100px;object-fit:cover;border-radius:50%;border:3px solid var(--gold)">
          </div>
          <?php endif; ?>

          <div id="imagePreviewWrap" style="display:none" class="col-12">
            <label class="form-label">Preview</label><br>
            <img id="imagePreview" src="" alt="" style="height:100px;width:100px;object-fit:cover;border-radius:50%;border:3px solid var(--gold)">
          </div>

          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-gold"><i class="fas fa-save me-2"></i><?= $edit_member ? 'Update Member' : 'Add Member' ?></button>
            <a href="team.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Filters -->
  <div class="card mb-4">
    <div class="card-body py-3">
      <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" name="search" class="form-control" placeholder="Search by name or role..." value="<?= htmlspecialchars($search) ?>">
          </div>
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="">All Status</option>
            <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $filter_status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-maroon w-100"><i class="fas fa-filter me-1"></i>Filter</button>
        </div>
        <div class="col-md-2">
          <a href="team.php" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Team Grid -->
  <div class="row g-4">
    <?php if (empty($members)): ?>
      <div class="col-12">
        <div class="card text-center py-5">
          <div class="card-body">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No team members found</h5>
            <p class="text-muted">Add your first team member to get started.</p>
            <button class="btn btn-gold" onclick="toggleAddForm()"><i class="fas fa-plus me-2"></i>Add Member</button>
          </div>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($members as $member): ?>
      <div class="col-md-4 col-lg-3">
        <div class="card h-100 text-center team-card">
          <div class="card-body p-4">
            <!-- Photo -->
            <div class="mb-3 position-relative d-inline-block">
              <?php
                $img_src = $member['image']
                    ? SITE_URL . '/' . $member['image']
                    : SITE_URL . '/assets/images/placeholder-avatar.svg';
              ?>
              <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($member['name']) ?>"
                   style="width:90px;height:90px;object-fit:cover;border-radius:50%;border:3px solid var(--gold)">
              <!-- Status badge -->
              <span class="position-absolute bottom-0 end-0 badge rounded-pill <?= $member['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>" style="font-size:10px">
                <?= ucfirst($member['status']) ?>
              </span>
            </div>
            <h6 class="fw-bold mb-1"><?= htmlspecialchars($member['name']) ?></h6>
            <p class="text-gold small mb-2"><?= htmlspecialchars($member['role']) ?></p>
            <?php if ($member['bio']): ?>
              <p class="text-muted small mb-2" style="font-size:12px;line-height:1.4"><?= htmlspecialchars(substr($member['bio'], 0, 80)) ?><?= strlen($member['bio']) > 80 ? '...' : '' ?></p>
            <?php endif; ?>
            <?php if ($member['phone']): ?>
              <p class="small mb-1"><i class="fas fa-phone text-maroon me-1"></i><?= htmlspecialchars($member['phone']) ?></p>
            <?php endif; ?>
            <?php if ($member['email']): ?>
              <p class="small mb-2"><i class="fas fa-envelope text-maroon me-1"></i><?= htmlspecialchars($member['email']) ?></p>
            <?php endif; ?>
            <div class="mt-auto pt-2 border-top d-flex justify-content-center gap-2">
              <a href="?edit=<?= $member['id'] ?>" class="btn btn-sm btn-outline-maroon" title="Edit">
                <i class="fas fa-edit"></i>
              </a>
              <button class="btn btn-sm <?= $member['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                      onclick="toggleStatus(<?= $member['id'] ?>, 'team', this)"
                      title="Toggle Status">
                <i class="fas fa-<?= $member['status'] === 'active' ? 'eye-slash' : 'eye' ?>"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger"
                      onclick="confirmDelete(<?= $member['id'] ?>, 'team', '<?= addslashes($member['name']) ?>')"
                      title="Delete">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
  <nav class="mt-4">
    <ul class="pagination justify-content-center">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filter_status) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
  <?php endif; ?>
</div>

<style>
.team-card { transition: transform 0.2s, box-shadow 0.2s; }
.team-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(128,0,0,0.12) !important; }
</style>

<script>
function toggleAddForm(show) {
    const panel = document.getElementById('memberFormPanel');
    if (show === false) {
        panel.style.display = 'none';
    } else {
        panel.style.display = panel.style.display === 'none' ? '' : 'none';
        if (panel.style.display !== 'none') panel.scrollIntoView({behavior:'smooth', block:'start'});
    }
}
function previewMemberImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewWrap').style.display = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function toggleStatus(id, type, btn) {
    if (!confirm('Toggle status for this team member?')) return;
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('id', id);
    formData.append('type', type);
    formData.append('csrf_token', '<?= generateCSRF() ?>');
    fetch('ajax.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); else alert(data.message); });
}
</script>

<?php require_once 'layout-footer.php'; ?>
