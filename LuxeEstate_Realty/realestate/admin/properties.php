<?php
$pageTitle  = 'Properties';
$activePage = 'properties';
require_once __DIR__ . '/layout-header.php';

$db = db();

// Filters
$search = sanitize($_GET['search'] ?? '');
$status = sanitize($_GET['status'] ?? '');
$type   = sanitize($_GET['type'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_PER_PAGE;
$offset  = ($page - 1) * $perPage;

$where  = [];
$params = [];
if ($search) { $where[] = '(title LIKE :s OR city LIKE :s2 OR location LIKE :s3)'; $params[':s'] = $params[':s2'] = $params[':s3'] = "%$search%"; }
if ($status) { $where[] = 'status = :st'; $params[':st'] = $status; }
if ($type)   { $where[] = 'type = :t';   $params[':t']  = $type; }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = (int)$db->prepare("SELECT COUNT(*) FROM properties $whereSQL")->execute($params) ? $db->prepare("SELECT COUNT(*) FROM properties $whereSQL")->execute($params) || true : 0;
$cStmt = $db->prepare("SELECT COUNT(*) FROM properties $whereSQL");
$cStmt->execute($params); $total = (int)$cStmt->fetchColumn();
$pages = ceil($total / $perPage);

$stmt = $db->prepare("SELECT p.*, (SELECT image_path FROM property_images WHERE property_id=p.id AND is_primary=1 LIMIT 1) AS thumb
    FROM properties p $whereSQL ORDER BY p.created_at DESC LIMIT :lim OFFSET :off");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$properties = $stmt->fetchAll();

$types = $db->query("SELECT DISTINCT type FROM properties ORDER BY type")->fetchAll(PDO::FETCH_COLUMN);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (validateCSRF($_POST['csrf_token'] ?? '')) {
        $id   = (int)($_POST['id'] ?? 0);
        $imgs = $db->prepare("SELECT image_path FROM property_images WHERE property_id=?");
        $imgs->execute([$id]); 
        foreach ($imgs->fetchAll(PDO::FETCH_COLUMN) as $img) {
            $f = UPLOAD_DIR . $img;
            if (file_exists($f)) unlink($f);
        }
        $db->prepare("DELETE FROM property_images WHERE property_id=?")->execute([$id]);
        $db->prepare("DELETE FROM properties WHERE id=?")->execute([$id]);
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Property deleted successfully.'];
    }
    header('Location: ' . ADMIN_URL . '/properties.php'); exit;
}

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$csrf  = generateCSRF();
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-building" style="color:var(--maroon)"></i> Properties</h1>
        <p>Manage all your property listings</p>
    </div>
    <a href="<?= ADMIN_URL ?>/property-add.php" class="btn btn-gold">
        <i class="fas fa-plus"></i> Add New Property
    </a>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card" style="margin-bottom:18px">
    <div class="card-body" style="padding:14px 20px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by title, city..." class="form-control" style="max-width:240px;padding:8px 12px">
            <select name="status" class="form-control" style="max-width:160px;padding:8px 12px">
                <option value="">All Status</option>
                <option value="active" <?= $status==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= $status==='inactive'?'selected':'' ?>>Inactive</option>
                <option value="sold" <?= $status==='sold'?'selected':'' ?>>Sold</option>
            </select>
            <select name="type" class="form-control" style="max-width:160px;padding:8px 12px">
                <option value="">All Types</option>
                <?php foreach ($types as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= $type===$t?'selected':'' ?>><?= htmlspecialchars($t) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search || $status || $type): ?>
            <a href="<?= ADMIN_URL ?>/properties.php" class="btn btn-gray">Clear</a>
            <?php endif; ?>
            <span style="margin-left:auto;font-size:13px;color:var(--text-muted)"><?= $total ?> properties found</span>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <?php if (empty($properties)): ?>
        <div style="text-align:center;padding:60px;color:var(--text-muted)">
            <i class="fas fa-building" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px"></i>
            <p>No properties found. <a href="<?= ADMIN_URL ?>/property-add.php" style="color:var(--maroon)">Add your first property.</a></p>
        </div>
        <?php else: ?>
        <table>
            <thead><tr>
                <th style="width:50px"></th>
                <th>Property</th><th>Price</th><th>Type</th><th>City</th>
                <th>Status</th><th>Featured</th><th>Views</th><th>Added</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($properties as $p): ?>
            <tr>
                <td>
                    <?php if ($p['thumb']): ?>
                    <img src="<?= UPLOAD_URL . htmlspecialchars($p['thumb']) ?>" class="table-img" alt="">
                    <?php else: ?>
                    <div class="table-no-img"><i class="fas fa-image"></i></div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-weight:600;font-size:13.5px"><?= htmlspecialchars($p['title']) ?></div>
                    <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($p['slug']) ?></div>
                </td>
                <td style="font-weight:700;color:var(--maroon)"><?= formatPrice($p['price']) ?></td>
                <td><span class="badge badge-gold"><?= htmlspecialchars($p['type']) ?></span></td>
                <td><?= htmlspecialchars($p['city']) ?></td>
                <td>
                    <button class="btn btn-sm btn-icon status-toggle" style="background:none;border:none;cursor:pointer"
                        data-id="<?= $p['id'] ?>" data-type="properties" data-field="status"
                        data-value="<?= $p['status'] ?>" title="Toggle Status">
                        <?php if ($p['status'] === 'active'): ?>
                        <i class="fas fa-toggle-on" style="color:var(--success);font-size:18px"></i>
                        <?php else: ?>
                        <i class="fas fa-toggle-off" style="color:var(--text-muted);font-size:18px"></i>
                        <?php endif; ?>
                    </button>
                    <span id="status-badge-<?= $p['id'] ?>" class="badge <?= $p['status']==='active'?'badge-green':'badge-gray' ?>">
                        <?= ucfirst($p['status']) ?>
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-icon status-toggle" style="background:none;border:none;cursor:pointer"
                        data-id="<?= $p['id'] ?>" data-type="properties" data-field="featured"
                        data-value="<?= $p['featured'] ?>">
                        <i class="fas fa-star" style="color:<?= $p['featured'] ? 'var(--gold)' : 'var(--beige-dark)' ?>;font-size:16px"></i>
                    </button>
                </td>
                <td style="font-size:12px;color:var(--text-muted)"><?= number_format($p['views']) ?></td>
                <td style="font-size:12px;color:var(--text-muted)"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="<?= SITE_URL ?>/property/<?= htmlspecialchars($p['slug']) ?>" target="_blank"
                           class="btn btn-sm btn-icon btn-gray" title="Preview"><i class="fas fa-eye"></i></a>
                        <a href="<?= ADMIN_URL ?>/property-add.php?edit=<?= $p['id'] ?>"
                           class="btn btn-sm btn-icon btn-outline" title="Edit"><i class="fas fa-edit"></i></a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this property?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <button type="submit" class="btn btn-sm btn-icon btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <div class="pagination-bar">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&type=<?= urlencode($type) ?>"
               class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<meta name="csrf" content="<?= generateCSRF() ?>">
<?php require_once __DIR__ . '/layout-footer.php'; ?>
