<?php
$pageTitle  = 'Blog Posts';
$activePage = 'blogs';
require_once __DIR__ . '/layout-header.php';

$db   = db();
$csrf = generateCSRF();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) { die('Invalid token'); }
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'delete') {
        $id   = (int)$_POST['id'];
        $img  = $db->prepare("SELECT image FROM blogs WHERE id=?"); $img->execute([$id]);
        $path = $img->fetchColumn();
        if ($path) deleteImage($path);
        $db->prepare("DELETE FROM blogs WHERE id=?")->execute([$id]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Post deleted.'];
        header('Location: ' . ADMIN_URL . '/blogs.php'); exit;
    }

    if (in_array($action, ['add_blog','update_blog'])) {
        $d = [
            'title'    => sanitize($_POST['title'] ?? ''),
            'slug'     => sanitize($_POST['slug'] ?? ''),
            'category' => sanitize($_POST['category'] ?? ''),
            'excerpt'  => sanitize($_POST['excerpt'] ?? ''),
            'content'  => $_POST['content'] ?? '',   // Allow HTML from editor
            'tags'     => sanitize($_POST['tags'] ?? ''),
            'status'   => sanitize($_POST['status'] ?? 'draft'),
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'meta_title' => sanitize($_POST['meta_title'] ?? ''),
            'meta_desc'  => sanitize($_POST['meta_desc'] ?? ''),
        ];
        if (empty($d['slug'])) $d['slug'] = generateSlug($d['title']);
        $editId = (int)($_POST['edit_id'] ?? 0);
        $d['slug'] = uniqueSlug($d['slug'], 'blogs', $editId ?: null);

        // Image upload
        $imgPath = sanitize($_POST['existing_image'] ?? '');
        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === 0) {
            $res = uploadImage($_FILES['image'], 'blogs');
            if ($res['success']) {
                if ($imgPath) deleteImage($imgPath);
                $imgPath = $res['path'];
            }
        }
        $d['image'] = $imgPath;

        if ($editId) {
            $sql = "UPDATE blogs SET title=:title,slug=:slug,category=:category,excerpt=:excerpt,content=:content,
                tags=:tags,status=:status,featured=:featured,image=:image,meta_title=:meta_title,meta_desc=:meta_desc,
                updated_at=NOW() WHERE id=:id";
            $d[':id'] = $editId;
        } else {
            $d['author_id'] = $_SESSION['admin_id'] ?? 1;
            $sql = "INSERT INTO blogs (title,slug,category,excerpt,content,tags,status,featured,image,meta_title,meta_desc,author_id,created_at)
                VALUES (:title,:slug,:category,:excerpt,:content,:tags,:status,:featured,:image,:meta_title,:meta_desc,:author_id,NOW())";
        }
        $db->prepare($sql)->execute($d);
        $_SESSION['flash'] = ['type'=>'success','msg'=>$editId?'Post updated.':'Post published.'];
        header('Location: ' . ADMIN_URL . '/blogs.php'); exit;
    }
}

// Edit mode
$editBlog = null;
if (isset($_GET['edit'])) {
    $s = $db->prepare("SELECT * FROM blogs WHERE id=?"); $s->execute([(int)$_GET['edit']]);
    $editBlog = $s->fetch();
}

$page    = max(1,(int)($_GET['page']??1));
$perPage = ADMIN_PER_PAGE;
$offset  = ($page-1)*$perPage;
$total   = (int)$db->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
$pages   = ceil($total/$perPage);
$blogs   = $db->query("SELECT b.*,u.name AS author FROM blogs b LEFT JOIN users u ON b.author_id=u.id ORDER BY b.created_at DESC LIMIT $perPage OFFSET $offset")->fetchAll();

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>

<div class="page-header">
    <div><h1><i class="fas fa-newspaper" style="color:var(--maroon)"></i> Blog Posts</h1></div>
    <button onclick="toggleForm()" class="btn btn-gold" id="addBlogBtn">
        <i class="fas fa-plus"></i> <?= $editBlog ? 'Edit Post' : 'New Post' ?>
    </button>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- Add/Edit Form -->
<div id="blogFormWrap" style="display:<?= $editBlog ? 'block' : 'none' ?>;margin-bottom:20px">
    <div class="card">
        <div class="card-header"><span class="card-title"><?= $editBlog ? 'Edit: '.htmlspecialchars($editBlog['title']) : 'New Blog Post' ?></span>
            <button type="button" onclick="toggleForm(false)" class="btn btn-sm btn-gray"><i class="fas fa-times"></i></button>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="<?= $editBlog ? 'update_blog' : 'add_blog' ?>">
                <?php if ($editBlog): ?><input type="hidden" name="edit_id" value="<?= $editBlog['id'] ?>"><?php endif; ?>
                <?php if ($editBlog && $editBlog['image']): ?><input type="hidden" name="existing_image" value="<?= htmlspecialchars($editBlog['image']) ?>"><?php endif; ?>

                <div class="form-grid" style="margin-bottom:14px">
                    <div class="form-group form-full">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" id="blogTitle" value="<?= htmlspecialchars($editBlog['title']??'') ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="blogSlug" value="<?= htmlspecialchars($editBlog['slug']??'') ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" value="<?= htmlspecialchars($editBlog['category']??'') ?>"
                               list="blogCats" class="form-control" placeholder="e.g. Buying Guide">
                        <datalist id="blogCats">
                            <?php $cats=$db->query("SELECT DISTINCT category FROM blogs WHERE category!='' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($cats as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Featured Image</label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="form-control">
                        <?php if ($editBlog && $editBlog['image']): ?>
                        <div style="margin-top:8px"><img src="<?= UPLOAD_URL.htmlspecialchars($editBlog['image']) ?>" style="height:60px;border-radius:6px;object-fit:cover"></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group form-full">
                        <label class="form-label">Excerpt</label>
                        <input type="text" name="excerpt" value="<?= htmlspecialchars($editBlog['excerpt']??'') ?>" class="form-control" placeholder="Short summary (used in blog cards)">
                    </div>
                    <div class="form-group form-full">
                        <label class="form-label">Content</label>
                        <textarea name="content" id="blogContent" rows="12" class="form-control"><?= htmlspecialchars($editBlog['content']??'') ?></textarea>
                        <span class="form-hint">Basic HTML is supported (p, h2, h3, strong, em, ul, ol, li, a)</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tags (comma separated)</label>
                        <input type="text" name="tags" value="<?= htmlspecialchars($editBlog['tags']??'') ?>" class="form-control" placeholder="buying, investment, tips">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="published" <?= ($editBlog['status']??'')==='published'?'selected':'' ?>>Published</option>
                            <option value="draft"     <?= ($editBlog['status']??'draft')==='draft'?'selected':'' ?>>Draft</option>
                        </select>
                    </div>
                    <div class="form-group" style="justify-content:flex-end;padding-top:24px">
                        <label class="form-check">
                            <input type="checkbox" name="featured" <?= !empty($editBlog['featured'])?'checked':'' ?>>
                            <span class="form-check-label"><i class="fas fa-star" style="color:var(--gold)"></i> Feature on Homepage</span>
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> <?= $editBlog ? 'Update Post' : 'Publish Post' ?></button>
                <button type="button" onclick="toggleForm(false)" class="btn btn-gray" style="margin-left:8px">Cancel</button>
            </form>
        </div>
    </div>
</div>

<!-- Blog List -->
<div class="card">
    <div class="table-wrap">
        <?php if (empty($blogs)): ?>
        <div style="text-align:center;padding:60px;color:var(--text-muted)">
            <i class="fas fa-newspaper" style="font-size:36px;opacity:.3;display:block;margin-bottom:12px"></i>
            No blog posts yet. <button onclick="toggleForm()" class="btn btn-sm btn-gold" style="margin-left:8px">Write First Post</button>
        </div>
        <?php else: ?>
        <table>
            <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Views</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($blogs as $b): ?>
            <tr>
                <td><?php if ($b['image']): ?><img src="<?= UPLOAD_URL.htmlspecialchars($b['image']) ?>" class="table-img"><?php else: ?><div class="table-no-img"><i class="fas fa-image"></i></div><?php endif; ?></td>
                <td>
                    <div style="font-weight:600"><?= htmlspecialchars($b['title']) ?></div>
                    <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($b['slug']) ?></div>
                    <?php if ($b['featured']): ?><span class="badge badge-gold" style="margin-top:3px"><i class="fas fa-star"></i> Featured</span><?php endif; ?>
                </td>
                <td><?= $b['category'] ? '<span class="badge badge-maroon">'.htmlspecialchars($b['category']).'</span>' : '—' ?></td>
                <td style="font-size:12px"><?= htmlspecialchars($b['author'] ?? 'Admin') ?></td>
                <td><span class="badge <?= $b['status']==='published'?'badge-green':'badge-gray' ?>"><?= ucfirst($b['status']) ?></span></td>
                <td style="font-size:12px;color:var(--text-muted)"><?= number_format($b['views']??0) ?></td>
                <td style="font-size:12px;color:var(--text-muted)"><?= date('d M Y', strtotime($b['created_at'])) ?></td>
                <td>
                    <div style="display:flex;gap:4px">
                        <a href="<?= SITE_URL ?>/blog/<?= htmlspecialchars($b['slug']) ?>" target="_blank" class="btn btn-sm btn-icon btn-gray"><i class="fas fa-eye"></i></a>
                        <a href="?edit=<?= $b['id'] ?>#blogFormWrap" class="btn btn-sm btn-icon btn-outline" onclick="toggleForm(true)"><i class="fas fa-edit"></i></a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this post?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <button type="submit" class="btn btn-sm btn-icon btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($pages > 1): ?>
        <div class="pagination-bar">
            <?php for ($i=1;$i<=$pages;$i++): ?><a href="?page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleForm(show) {
    const wrap = document.getElementById('blogFormWrap');
    if (show === undefined) wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
    else wrap.style.display = show ? 'block' : 'none';
    if (wrap.style.display === 'block') wrap.scrollIntoView({behavior:'smooth'});
}
document.getElementById('blogTitle')?.addEventListener('input', function() {
    const s = document.getElementById('blogSlug');
    if (!s.dataset.m) s.value = this.value.toLowerCase().replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-');
});
document.getElementById('blogSlug')?.addEventListener('input', function() { this.dataset.m = this.value ? '1' : ''; });
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
