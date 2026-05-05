        </div><!-- /.admin-content -->

        <footer style="padding:16px 28px;border-top:1px solid var(--beige-dark);font-size:12px;color:var(--text-muted);display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
            <span>&copy; <?= date('Y') ?> <?= htmlspecialchars(getSetting('site_name', 'LuxeEstate Realty')) ?> — All rights reserved.</span>
            <span>Admin Panel v1.0</span>
        </footer>
    </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<script>
// Close sidebar on outside click (mobile)
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('adminSidebar');
    if (sidebar && sidebar.classList.contains('open') &&
        !sidebar.contains(e.target) &&
        !e.target.closest('.menu-toggle')) {
        sidebar.classList.remove('open');
    }
});

// Alert auto-dismiss
document.querySelectorAll('.alert').forEach(a => {
    setTimeout(() => a.style.transition = 'opacity .4s', 3500);
    setTimeout(() => a.style.opacity = '0', 3900);
    setTimeout(() => a.remove(), 4300);
});

// Confirm deletes
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm(this.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
});

// Toggle status via AJAX
document.querySelectorAll('.status-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        const id     = this.dataset.id;
        const type   = this.dataset.type;
        const field  = this.dataset.field || 'status';
        const val    = this.dataset.value;
        const newVal = val === 'active' ? 'inactive' : val === '1' ? '0' : val === '0' ? '1' : 'inactive';
        const self   = this;
        fetch('<?= ADMIN_URL ?>/ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ action:'toggle_status', id, type, field, value:newVal,
                csrf_token: document.querySelector('meta[name=csrf]')?.content || '' })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                self.dataset.value = newVal;
                const badge = document.getElementById('status-badge-' + id);
                if (badge) {
                    if (newVal === 'active' || newVal === '1') {
                        badge.className = 'badge badge-green'; badge.textContent = 'Active';
                    } else {
                        badge.className = 'badge badge-gray'; badge.textContent = 'Inactive';
                    }
                }
                self.innerHTML = (newVal === 'active' || newVal === '1')
                    ? '<i class="fas fa-toggle-on" style="color:var(--success);font-size:18px"></i>'
                    : '<i class="fas fa-toggle-off" style="color:var(--text-muted);font-size:18px"></i>';
            }
        });
    });
});
</script>
</body>
</html>
