<?php
$pageTitle  = 'Leads & Enquiries';
$activePage = 'leads';
require_once __DIR__ . '/layout-header.php';

$db = db();

// Handle AJAX status/tag update (non-AJAX fallback POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    if (validateCSRF($_POST['csrf_token'] ?? '')) {
        if ($action === 'update_lead') {
            $id     = (int)($_POST['id'] ?? 0);
            $status = sanitize($_POST['status'] ?? '');
            $tag    = sanitize($_POST['tag'] ?? '');
            $notes  = sanitize($_POST['notes'] ?? '');
            $db->prepare("UPDATE leads SET status=?,tag=?,notes=? WHERE id=?")->execute([$status,$tag,$notes,$id]);
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Lead updated.'];
        }
        if ($action === 'delete_lead') {
            $db->prepare("DELETE FROM leads WHERE id=?")->execute([(int)($_POST['id'] ?? 0)]);
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Lead deleted.'];
        }
    }
    header('Location: ' . ADMIN_URL . '/leads.php' . (isset($_GET['id']) ? '' : '') ); exit;
}

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    exportLeadsCSV();
    exit;
}

// Single lead view
$viewId   = (int)($_GET['id'] ?? 0);
$viewLead = null;
if ($viewId) {
    $s = $db->prepare("SELECT l.*, p.title AS property_title, p.slug AS property_slug
        FROM leads l LEFT JOIN properties p ON l.property_id = p.id WHERE l.id=?");
    $s->execute([$viewId]); $viewLead = $s->fetch();
}

// Filters
$status  = sanitize($_GET['status'] ?? '');
$tag     = sanitize($_GET['tag'] ?? '');
$search  = sanitize($_GET['search'] ?? '');
$page    = max(1,(int)($_GET['page'] ?? 1));
$perPage = ADMIN_PER_PAGE;
$offset  = ($page - 1) * $perPage;

$where  = [];
$params = [];
if ($status) { $where[] = 'l.status=:st';  $params[':st']  = $status; }
if ($tag)    { $where[] = 'l.tag=:tag';    $params[':tag'] = $tag; }
if ($search) { $where[] = '(l.name LIKE :s OR l.phone LIKE :s2 OR l.email LIKE :s3)';
               $params[':s'] = $params[':s2'] = $params[':s3'] = "%$search%"; }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$cStmt = $db->prepare("SELECT COUNT(*) FROM leads l $whereSQL");
$cStmt->execute($params); $total = (int)$cStmt->fetchColumn();
$pages = ceil($total / $perPage);

$stmt = $db->prepare("SELECT l.*, p.title AS property_title FROM leads l
    LEFT JOIN properties p ON l.property_id = p.id
    $whereSQL ORDER BY l.created_at DESC LIMIT :lim OFFSET :off");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
$stmt->execute();
$leads = $stmt->fetchAll();

// Stats
$stats = [
    'total'     => $db->query("SELECT COUNT(*) FROM leads")->fetchColumn(),
    'new'       => $db->query("SELECT COUNT(*) FROM leads WHERE status='new'")->fetchColumn(),
    'hot'       => $db->query("SELECT COUNT(*) FROM leads WHERE tag='hot'")->fetchColumn(),
    'contacted' => $db->query("SELECT COUNT(*) FROM leads WHERE status='contacted'")->fetchColumn(),
];

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$csrf  = generateCSRF();
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-users" style="color:var(--maroon)"></i> Leads & Enquiries</h1>
        <p>Manage, tag, and follow up with all your leads</p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="?export=csv<?= $status?'&status='.$status:'' ?><?= $tag?'&tag='.$tag:'' ?>"
           class="btn btn-outline"><i class="fas fa-download"></i> Export CSV</a>
    </div>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-row" style="margin-bottom:18px">
    <div class="stat-card" style="cursor:pointer" onclick="location='?'">
        <div class="stat-card-icon blue"><i class="fas fa-users"></i></div>
        <div><div class="stat-card-value"><?= $stats['total'] ?></div><div class="stat-card-label">Total Leads</div></div>
    </div>
    <div class="stat-card" style="cursor:pointer" onclick="location='?status=new'">
        <div class="stat-card-icon maroon"><i class="fas fa-bell"></i></div>
        <div><div class="stat-card-value"><?= $stats['new'] ?></div><div class="stat-card-label">New / Unread</div></div>
    </div>
    <div class="stat-card" style="cursor:pointer" onclick="location='?tag=hot'">
        <div class="stat-card-icon" style="background:rgba(220,38,38,.1);color:#DC2626"><i class="fas fa-fire"></i></div>
        <div><div class="stat-card-value"><?= $stats['hot'] ?></div><div class="stat-card-label">Hot Leads</div></div>
    </div>
    <div class="stat-card" style="cursor:pointer" onclick="location='?status=contacted'">
        <div class="stat-card-icon green"><i class="fas fa-phone-alt"></i></div>
        <div><div class="stat-card-value"><?= $stats['contacted'] ?></div><div class="stat-card-label">Contacted</div></div>
    </div>
</div>

<!-- Single Lead View Modal -->
<?php if ($viewLead): ?>
<div id="leadModal" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;display:flex;align-items:center;justify-content:center;padding:20px">
    <div style="background:white;border-radius:16px;max-width:600px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,.3)">
        <div style="padding:20px;border-bottom:1px solid var(--beige-dark);display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:17px;font-weight:700">Lead #<?= $viewLead['id'] ?></h3>
            <a href="<?= ADMIN_URL ?>/leads.php" class="btn btn-sm btn-gray"><i class="fas fa-times"></i></a>
        </div>
        <div style="padding:24px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px">
                <div><div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:4px">Name</div><strong><?= htmlspecialchars($viewLead['name']) ?></strong></div>
                <div><div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:4px">Phone</div><a href="tel:<?= htmlspecialchars($viewLead['phone']) ?>" style="color:var(--maroon);font-weight:600"><?= htmlspecialchars($viewLead['phone']) ?></a></div>
                <div><div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:4px">Email</div><?= $viewLead['email'] ? '<a href="mailto:'.htmlspecialchars($viewLead['email']).'" style="color:var(--maroon)">'.htmlspecialchars($viewLead['email']).'</a>' : '—' ?></div>
                <div><div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:4px">Budget</div><?= $viewLead['budget'] ? htmlspecialchars($viewLead['budget']) : '—' ?></div>
                <div><div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:4px">Source</div><?= htmlspecialchars($viewLead['source'] ?? 'website') ?></div>
                <div><div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:4px">Received</div><?= date('d M Y, h:i A', strtotime($viewLead['created_at'])) ?></div>
                <?php if ($viewLead['property_title']): ?>
                <div style="grid-column:1/-1"><div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:4px">Property Enquiry</div>
                <a href="<?= SITE_URL ?>/property/<?= htmlspecialchars($viewLead['property_slug']) ?>" target="_blank" style="color:var(--maroon)"><?= htmlspecialchars($viewLead['property_title']) ?></a></div>
                <?php endif; ?>
                <?php if ($viewLead['message']): ?>
                <div style="grid-column:1/-1"><div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:4px">Message</div>
                <div style="background:var(--beige-light);padding:12px;border-radius:8px;font-size:13.5px"><?= nl2br(htmlspecialchars($viewLead['message'])) ?></div></div>
                <?php endif; ?>
            </div>

            <!-- Update Form -->
            <form method="POST" style="border-top:1px solid var(--beige-dark);padding-top:18px">
                <input type="hidden" name="action" value="update_lead">
                <input type="hidden" name="id" value="<?= $viewLead['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <?php foreach (['new','contacted','interested','closed','lost'] as $s): ?>
                            <option value="<?= $s ?>" <?= $viewLead['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tag</label>
                        <select name="tag" class="form-control">
                            <option value="">None</option>
                            <?php foreach (['hot','warm','cold'] as $t): ?>
                            <option value="<?= $t ?>" <?= $viewLead['tag']===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:14px">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="Add follow-up notes..."><?= htmlspecialchars($viewLead['notes'] ?? '') ?></textarea>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Changes</button>
                    <a href="tel:<?= htmlspecialchars($viewLead['phone']) ?>" class="btn btn-primary"><i class="fas fa-phone"></i> Call</a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $viewLead['phone']) ?>" target="_blank" class="btn btn-success"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filter Bar -->
<div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding:14px 20px">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by name, phone..." class="form-control" style="max-width:220px;padding:8px 12px">
            <select name="status" class="form-control" style="max-width:160px;padding:8px 12px">
                <option value="">All Status</option>
                <?php foreach (['new','contacted','interested','closed','lost'] as $s): ?>
                <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="tag" class="form-control" style="max-width:140px;padding:8px 12px">
                <option value="">All Tags</option>
                <option value="hot"  <?= $tag==='hot' ?'selected':'' ?>>🔥 Hot</option>
                <option value="warm" <?= $tag==='warm'?'selected':'' ?>>🟡 Warm</option>
                <option value="cold" <?= $tag==='cold'?'selected':'' ?>>🧊 Cold</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search || $status || $tag): ?><a href="?" class="btn btn-gray">Clear</a><?php endif; ?>
            <span style="margin-left:auto;font-size:13px;color:var(--text-muted)"><?= $total ?> records</span>
        </form>
    </div>
</div>

<!-- Leads Table -->
<div class="card">
    <div class="table-wrap">
        <?php if (empty($leads)): ?>
        <div style="text-align:center;padding:60px;color:var(--text-muted)">
            <i class="fas fa-inbox" style="font-size:36px;opacity:.3;display:block;margin-bottom:12px"></i>
            No leads match your filters.
        </div>
        <?php else: ?>
        <table>
            <thead><tr>
                <th>#</th><th>Contact</th><th>Budget</th><th>Property</th>
                <th>Source</th><th>Status</th><th>Tag</th><th>Date</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($leads as $lead): ?>
            <tr <?= $lead['status']==='new' ? 'style="background:rgba(128,0,0,.03)"' : '' ?>>
                <td style="font-size:12px;color:var(--text-muted)"><?= $lead['id'] ?><?= $lead['status']==='new' ? ' <span style="color:var(--maroon);font-weight:700;font-size:10px">NEW</span>' : '' ?></td>
                <td>
                    <div style="font-weight:600"><?= htmlspecialchars($lead['name']) ?></div>
                    <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" style="font-size:12px;color:var(--maroon)"><?= htmlspecialchars($lead['phone']) ?></a>
                    <?php if ($lead['email']): ?>
                    <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($lead['email']) ?></div>
                    <?php endif; ?>
                </td>
                <td style="font-size:13px"><?= $lead['budget'] ?: '<span style="color:var(--text-muted)">—</span>' ?></td>
                <td style="font-size:12px;max-width:150px">
                    <?php if ($lead['property_title']): ?>
                    <span title="<?= htmlspecialchars($lead['property_title']) ?>" style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= htmlspecialchars(mb_substr($lead['property_title'], 0, 30)) ?>...
                    </span>
                    <?php else: ?><span style="color:var(--text-muted)">General</span><?php endif; ?>
                </td>
                <td style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($lead['source'] ?? 'website') ?></td>
                <td>
                    <?php $statusClass=['new'=>'badge-blue','contacted'=>'badge-yellow','interested'=>'badge-green','closed'=>'badge-maroon','lost'=>'badge-gray'][$lead['status']]??'badge-gray'; ?>
                    <span class="badge <?= $statusClass ?>"><?= ucfirst($lead['status']) ?></span>
                </td>
                <td>
                    <?php if ($lead['tag']): ?>
                    <?php $tc=['hot'=>'badge-red','warm'=>'badge-orange','cold'=>'badge-blue'][$lead['tag']]??'badge-gray'; ?>
                    <span class="badge <?= $tc ?>"><?= ucfirst($lead['tag']) ?></span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted);white-space:nowrap"><?= timeAgo($lead['created_at']) ?></td>
                <td>
                    <div style="display:flex;gap:4px">
                        <a href="?id=<?= $lead['id'] ?>" class="btn btn-sm btn-icon btn-outline" title="View & Edit"><i class="fas fa-edit"></i></a>
                        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="btn btn-sm btn-icon btn-primary" title="Call"><i class="fas fa-phone"></i></a>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $lead['phone']) ?>" target="_blank"
                           class="btn btn-sm btn-icon btn-success" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="delete_lead">
                            <input type="hidden" name="id" value="<?= $lead['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <button type="submit" class="btn btn-sm btn-icon btn-danger" title="Delete"
                                onclick="return confirm('Delete this lead?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($pages > 1): ?>
        <div class="pagination-bar">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&tag=<?= urlencode($tag) ?>&search=<?= urlencode($search) ?>"
               class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
