<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/layout-header.php';

$stats      = getAdminStats();
$recentLeads = getRecentLeads(8);
$db          = db();

// Chart data - leads last 7 days
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date  = date('Y-m-d', strtotime("-$i days"));
    $count = (int)$db->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at)='$date'")->fetchColumn();
    $chartData[] = ['date' => date('d M', strtotime($date)), 'count' => $count];
}

// Top properties by views
$topProps = $db->query("SELECT title, views, type, city FROM properties WHERE status='active' ORDER BY views DESC LIMIT 5")->fetchAll();
?>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-card-icon maroon"><i class="fas fa-building"></i></div>
        <div>
            <div class="stat-card-value"><?= number_format($stats['total_properties']) ?></div>
            <div class="stat-card-label">Active Properties</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon gold"><i class="fas fa-users"></i></div>
        <div>
            <div class="stat-card-value"><?= number_format($stats['total_leads']) ?></div>
            <div class="stat-card-label">Total Leads</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="fas fa-fire"></i></div>
        <div>
            <div class="stat-card-value"><?= number_format($stats['new_leads']) ?></div>
            <div class="stat-card-label">New Leads Today</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="fas fa-eye"></i></div>
        <div>
            <div class="stat-card-value"><?= number_format($stats['total_views'] ?? 0) ?></div>
            <div class="stat-card-label">Total Page Views</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon orange"><i class="fas fa-newspaper"></i></div>
        <div>
            <div class="stat-card-value"><?= number_format($stats['total_blogs'] ?? 0) ?></div>
            <div class="stat-card-label">Blog Posts</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;margin-bottom:20px" class="dashboard-grid">

    <!-- Lead Chart -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-chart-bar" style="color:var(--maroon)"></i> Leads — Last 7 Days</span>
            <a href="<?= ADMIN_URL ?>/leads.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="card-body">
            <canvas id="leadChart" height="100"></canvas>
        </div>
    </div>

    <!-- Top Properties -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-trophy" style="color:var(--gold)"></i> Top Properties</span>
        </div>
        <div class="card-body" style="padding:12px">
            <?php foreach ($topProps as $i => $tp): ?>
            <div style="display:flex;align-items:center;gap:10px;padding:8px;border-radius:8px;<?= $i % 2 ? '' : 'background:var(--beige-light)' ?>">
                <div style="width:24px;height:24px;border-radius:50%;background:var(--gold-gradient);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--maroon-dark);flex-shrink:0"><?= $i+1 ?></div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:12.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($tp['title']) ?></div>
                    <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($tp['city']) ?> · <?= htmlspecialchars($tp['type']) ?></div>
                </div>
                <div style="font-size:12px;color:var(--text-muted);flex-shrink:0"><i class="fas fa-eye"></i> <?= number_format($tp['views']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Leads Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-bell" style="color:var(--maroon)"></i> Recent Enquiries</span>
        <div style="display:flex;gap:8px">
            <a href="<?= ADMIN_URL ?>/leads.php?status=new" class="btn btn-sm btn-gold">
                <i class="fas fa-fire"></i> New Leads <?php if ($stats['new_leads']): ?>(<?= $stats['new_leads'] ?>)<?php endif; ?>
            </a>
            <a href="<?= ADMIN_URL ?>/leads.php" class="btn btn-sm btn-outline">View All</a>
        </div>
    </div>
    <div class="table-wrap">
        <?php if (empty($recentLeads)): ?>
        <div style="text-align:center;padding:40px;color:var(--text-muted)">
            <i class="fas fa-inbox" style="font-size:32px;margin-bottom:10px;opacity:.4;display:block"></i>
            No leads yet. They'll appear here when visitors submit enquiries.
        </div>
        <?php else: ?>
        <table>
            <thead><tr>
                <th>#</th><th>Name</th><th>Phone</th><th>Budget</th>
                <th>Source</th><th>Status</th><th>Tag</th><th>Date</th><th>Action</th>
            </tr></thead>
            <tbody>
            <?php foreach ($recentLeads as $lead): ?>
            <tr>
                <td style="color:var(--text-muted);font-size:12px">#<?= $lead['id'] ?></td>
                <td>
                    <div style="font-weight:600"><?= htmlspecialchars($lead['name']) ?></div>
                    <?php if ($lead['email']): ?>
                    <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($lead['email']) ?></div>
                    <?php endif; ?>
                </td>
                <td><a href="tel:<?= htmlspecialchars($lead['phone']) ?>" style="color:var(--maroon);font-weight:600"><?= htmlspecialchars($lead['phone']) ?></a></td>
                <td><?= $lead['budget'] ? htmlspecialchars($lead['budget']) : '<span style="color:var(--text-muted)">—</span>' ?></td>
                <td><span style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($lead['source'] ?? 'website') ?></span></td>
                <td>
                    <?php
                    $statusClass = ['new'=>'badge-blue','contacted'=>'badge-yellow','closed'=>'badge-green','lost'=>'badge-gray'][$lead['status']] ?? 'badge-gray';
                    ?>
                    <span class="badge <?= $statusClass ?>"><?= ucfirst($lead['status']) ?></span>
                </td>
                <td>
                    <?php if ($lead['tag']): ?>
                    <?php $tagClass = ['hot'=>'badge-red','warm'=>'badge-orange','cold'=>'badge-blue'][$lead['tag']] ?? 'badge-gray'; ?>
                    <span class="badge <?= $tagClass ?>"><?= ucfirst($lead['tag']) ?></span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted)"><?= timeAgo($lead['created_at']) ?></td>
                <td>
                    <a href="<?= ADMIN_URL ?>/leads.php?id=<?= $lead['id'] ?>" class="btn btn-sm btn-outline btn-icon" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const data = <?= json_encode($chartData) ?>;
    const ctx  = document.getElementById('leadChart')?.getContext('2d');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.date),
            datasets: [{
                label: 'Leads',
                data: data.map(d => d.count),
                backgroundColor: 'rgba(128,0,0,.15)',
                borderColor: '#800000',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero:true, ticks: { stepSize:1 }, grid: { color:'rgba(0,0,0,.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
})();
</script>

<style>
@media(max-width:768px) { .dashboard-grid { grid-template-columns:1fr !important; } }
</style>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
