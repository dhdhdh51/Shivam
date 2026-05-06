<?php
$isEdit    = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$pageTitle  = $isEdit ? 'Edit Property' : 'Add Property';
$activePage = 'property-add';
require_once __DIR__ . '/layout-header.php';

$db     = db();
$errors = [];
$prop   = [
    'title'=>'','slug'=>'','price'=>'','location'=>'','city'=>'','type'=>'Apartment',
    'bhk'=>'','area_sqft'=>'','description'=>'','amenities'=>'[]','nearby'=>'[]',
    'possession'=>'','rera_number'=>'','featured'=>0,'trending'=>0,'status'=>'active',
    'meta_title'=>'','meta_description'=>'',
];
$existingImages = [];

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM properties WHERE id=?");
    $stmt->execute([$isEdit]); $prop = $stmt->fetch() ?: $prop;
    $imgStmt = $db->prepare("SELECT * FROM property_images WHERE property_id=? ORDER BY is_primary DESC, sort_order ASC");
    $imgStmt->execute([$isEdit]); $existingImages = $imgStmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired.';
    } else {
        $d = [
            'title'      => sanitize($_POST['title'] ?? ''),
            'slug'       => sanitize($_POST['slug'] ?? ''),
            'price'      => (float)($_POST['price'] ?? 0),
            'location'   => sanitize($_POST['location'] ?? ''),
            'city'       => sanitize($_POST['city'] ?? ''),
            'type'       => sanitize($_POST['type'] ?? 'Apartment'),
            'bhk'             => sanitize($_POST['bhk'] ?? ''),
            'area_sqft'       => (int)($_POST['area_sqft'] ?? 0),
            'description'     => sanitize($_POST['description'] ?? ''),
            'possession'      => sanitize($_POST['possession'] ?? ''),
            'rera_number'     => sanitize($_POST['rera_number'] ?? ''),
            'featured'        => isset($_POST['featured']) ? 1 : 0,
            'trending'        => isset($_POST['trending']) ? 1 : 0,
            'status'          => sanitize($_POST['status'] ?? 'active'),
            'meta_title'      => sanitize($_POST['meta_title'] ?? ''),
            'meta_description'=> sanitize($_POST['meta_description'] ?? ''),
        ];

        // Parse amenities + nearby
        $amenitiesRaw = array_filter(array_map('trim', explode("\n", $_POST['amenities'] ?? '')));
        $nearbyRaw    = array_filter(array_map('trim', explode("\n", $_POST['nearby'] ?? '')));
        $d['amenities'] = json_encode(array_values($amenitiesRaw));
        $d['nearby']    = json_encode(array_values($nearbyRaw));

        if (empty($d['title'])) $errors[] = 'Title is required.';
        if ($d['price'] <= 0)   $errors[] = 'Valid price is required.';
        if (empty($d['city']))  $errors[] = 'City is required.';

        // Slug uniqueness
        if (empty($d['slug'])) $d['slug'] = generateSlug($d['title']);
        $d['slug'] = uniqueSlug($d['slug'], 'properties', $isEdit ?: null);

        if (empty($errors)) {
            if ($isEdit) {
                $sql = "UPDATE properties SET title=:title,slug=:slug,price=:price,location=:location,city=:city,
                    type=:type,bhk=:bhk,area_sqft=:area_sqft,description=:description,amenities=:amenities,nearby=:nearby,
                    possession=:possession,rera_number=:rera_number,featured=:featured,trending=:trending,status=:status,
                    meta_title=:meta_title,meta_description=:meta_description WHERE id=:id";
                $d[':id'] = $isEdit;
                $db->prepare($sql)->execute($d);
                $propId = $isEdit;
            } else {
                $sql = "INSERT INTO properties (title,slug,price,location,city,type,bhk,area_sqft,description,amenities,
                    nearby,possession,rera_number,featured,trending,status,meta_title,meta_description,created_at)
                    VALUES (:title,:slug,:price,:location,:city,:type,:bhk,:area_sqft,:description,:amenities,:nearby,
                    :possession,:rera_number,:featured,:trending,:status,:meta_title,:meta_description,NOW())";
                $db->prepare($sql)->execute($d);
                $propId = (int)$db->lastInsertId();
            }

            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $primarySet = !empty($existingImages) && $isEdit;
                foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
                    if (!$tmpName || $_FILES['images']['error'][$i] !== 0) continue;
                    $fakefile = ['name' => $_FILES['images']['name'][$i], 'tmp_name' => $tmpName,
                                 'size' => $_FILES['images']['size'][$i], 'type' => $_FILES['images']['type'][$i]];
                    $result = uploadImage($fakefile, 'properties');
                    if ($result['success']) {
                        $isPrimary = (!$primarySet && $i === 0) ? 1 : 0;
                        $db->prepare("INSERT INTO property_images (property_id,image_path,is_primary,sort_order) VALUES (?,?,?,?)")
                           ->execute([$propId, $result['path'], $isPrimary, $i]);
                        $primarySet = true;
                    }
                }
            }

            // Handle primary image change
            if (!empty($_POST['primary_image'])) {
                $db->prepare("UPDATE property_images SET is_primary=0 WHERE property_id=?")->execute([$propId]);
                $db->prepare("UPDATE property_images SET is_primary=1 WHERE id=? AND property_id=?")
                   ->execute([(int)$_POST['primary_image'], $propId]);
            }

            // Handle image deletions
            if (!empty($_POST['delete_images'])) {
                foreach ((array)$_POST['delete_images'] as $imgId) {
                    $imgPath = $db->prepare("SELECT image_path FROM property_images WHERE id=? AND property_id=?");
                    $imgPath->execute([(int)$imgId, $propId]); $path = $imgPath->fetchColumn();
                    if ($path) { deleteImage($path); $db->prepare("DELETE FROM property_images WHERE id=?")->execute([(int)$imgId]); }
                }
            }

            $_SESSION['flash'] = ['type'=>'success','msg'=>$isEdit ? 'Property updated successfully!' : 'Property added successfully!'];
            header('Location: ' . ADMIN_URL . '/properties.php'); exit;
        }
    }
    $prop = array_merge($prop, $_POST);
}

$csrf = generateCSRF();
$amenitiesTxt = implode("\n", json_decode($prop['amenities'] ?? '[]', true) ?: []);
$nearbyTxt    = implode("\n", json_decode($prop['nearby'] ?? '[]', true) ?: []);

$propertyTypes = ['Apartment','Villa','Independent House','Plot','Commercial','Office','Shop','Warehouse','Farm House','Studio'];
$cities        = $db->query("SELECT DISTINCT city FROM properties WHERE city != '' ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?>" style="color:var(--maroon)"></i> <?= $pageTitle ?></h1>
        <p><?= $isEdit ? 'Update property details and images' : 'Add a new property listing' ?></p>
    </div>
    <a href="<?= ADMIN_URL ?>/properties.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i>
    <ul style="margin:0;padding-left:18px"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="propertyForm">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px" class="prop-form-grid">

        <!-- Main Fields -->
        <div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Basic Information</span></div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group form-full">
                            <label class="form-label">Property Title *</label>
                            <input type="text" name="title" id="titleField" value="<?= htmlspecialchars($prop['title']) ?>"
                                   placeholder="e.g. Luxury 3BHK Apartment in Indiranagar" class="form-control" required>
                        </div>
                        <div class="form-group form-full">
                            <label class="form-label">URL Slug</label>
                            <input type="text" name="slug" id="slugField" value="<?= htmlspecialchars($prop['slug']) ?>"
                                   placeholder="auto-generated-from-title" class="form-control">
                            <span class="form-hint">Leave blank to auto-generate from title</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Property Type *</label>
                            <select name="type" class="form-control">
                                <?php foreach ($propertyTypes as $t): ?>
                                <option value="<?= $t ?>" <?= $prop['type']===$t?'selected':'' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">BHK / Configuration</label>
                            <select name="bhk" class="form-control">
                                <option value="">Select</option>
                                <?php foreach (['1','1.5','2','2.5','3','3.5','4','4+','Studio','Duplex','Penthouse'] as $b): ?>
                                <option value="<?= $b ?>" <?= $prop['bhk']===$b?'selected':'' ?>><?= $b ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Price (₹) *</label>
                            <input type="number" name="price" value="<?= htmlspecialchars($prop['price']) ?>"
                                   placeholder="5000000" class="form-control" min="0" step="1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Area (sq.ft)</label>
                            <input type="number" name="area_sqft" value="<?= htmlspecialchars($prop['area_sqft'] ?? '') ?>"
                                   placeholder="1200" class="form-control" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" value="<?= htmlspecialchars($prop['city']) ?>"
                                   placeholder="e.g. Bangalore" class="form-control" list="cityList" required>
                            <datalist id="cityList">
                                <?php foreach ($cities as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Location / Area</label>
                            <input type="text" name="location" value="<?= htmlspecialchars($prop['location']) ?>"
                                   placeholder="e.g. Indiranagar, HSR Layout" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Possession Date</label>
                            <input type="text" name="possession" value="<?= htmlspecialchars($prop['possession'] ?? '') ?>"
                                   placeholder="e.g. Ready to Move / Dec 2025" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">RERA ID</label>
                            <input type="text" name="rera_number" value="<?= htmlspecialchars($prop['rera_number'] ?? '') ?>"
                                   placeholder="PRM/KA/RERA/..." class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Description</span></div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea name="description" rows="7" class="form-control"
                            placeholder="Write a detailed description of the property..."><?= htmlspecialchars($prop['description']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Amenities & Nearby Places</span></div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Amenities</label>
                            <textarea name="amenities" rows="8" class="form-control"
                                placeholder="One per line:&#10;Swimming Pool&#10;Gym&#10;Parking&#10;..."><?= htmlspecialchars($amenitiesTxt) ?></textarea>
                            <span class="form-hint">Enter one amenity per line</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nearby Places</label>
                            <textarea name="nearby" rows="8" class="form-control"
                                placeholder="One per line:&#10;Metro Station (0.5 km)&#10;Hospital (1 km)&#10;..."><?= htmlspecialchars($nearbyTxt) ?></textarea>
                            <span class="form-hint">Enter one place per line</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="card" style="margin-bottom:20px">
                <div class="card-header"><span class="card-title">Property Images</span></div>
                <div class="card-body">
                    <!-- Existing Images -->
                    <?php if (!empty($existingImages)): ?>
                    <div style="margin-bottom:16px">
                        <label class="form-label" style="margin-bottom:10px;display:block">Current Images</label>
                        <div id="existingImagesGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px">
                            <?php foreach ($existingImages as $img): ?>
                            <div style="position:relative;border-radius:8px;overflow:hidden;border:2px solid <?= $img['is_primary']?'var(--gold)':'var(--beige-dark)' ?>">
                                <img src="<?= UPLOAD_URL . htmlspecialchars($img['image_path']) ?>"
                                     style="width:100%;height:90px;object-fit:cover" alt="">
                                <?php if ($img['is_primary']): ?>
                                <div style="position:absolute;top:4px;left:4px;background:var(--gold);color:var(--maroon-dark);font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px">PRIMARY</div>
                                <?php endif; ?>
                                <div style="background:rgba(0,0,0,.6);padding:6px;display:flex;gap:4px;justify-content:center">
                                    <label style="cursor:pointer;color:white;font-size:11px;display:flex;align-items:center;gap:4px">
                                        <input type="radio" name="primary_image" value="<?= $img['id'] ?>"
                                               <?= $img['is_primary']?'checked':'' ?> style="accent-color:var(--gold)">
                                        Primary
                                    </label>
                                    <label style="cursor:pointer;color:#ff6b6b;font-size:11px;margin-left:6px">
                                        <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>">
                                        Delete
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Upload New -->
                    <div class="form-group">
                        <label class="form-label">Upload <?= $isEdit ? 'More ' : '' ?>Images</label>
                        <div id="imageDropzone" style="border:2px dashed var(--beige-dark);border-radius:10px;padding:30px;text-align:center;cursor:pointer;transition:all .2s" onclick="document.getElementById('imagesInput').click()">
                            <i class="fas fa-cloud-upload-alt" style="font-size:28px;color:var(--gold);margin-bottom:8px;display:block"></i>
                            <p style="font-size:13px;color:var(--text-muted)">Click or drag images here<br><span style="font-size:11px">JPG, PNG, WebP — Max 5MB each — Multiple allowed</span></p>
                        </div>
                        <input type="file" name="images[]" id="imagesInput" multiple accept="image/jpeg,image/png,image/webp" style="display:none">
                        <div id="imagePreviewGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;margin-top:10px"></div>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="card">
                <div class="card-header"><span class="card-title">SEO Settings</span></div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:14px">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" value="<?= htmlspecialchars($prop['meta_title'] ?? '') ?>"
                               placeholder="Leave blank to use property title" class="form-control" maxlength="70">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" rows="3" class="form-control" maxlength="160"
                            placeholder="Brief description for search engines (max 160 chars)"><?= htmlspecialchars($prop['meta_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="card" style="margin-bottom:16px">
                <div class="card-header"><span class="card-title">Publish</span></div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active"   <?= $prop['status']==='active'  ?'selected':'' ?>>Active (Published)</option>
                            <option value="inactive" <?= $prop['status']==='inactive'?'selected':'' ?>>Inactive (Draft)</option>
                            <option value="sold"     <?= $prop['status']==='sold'    ?'selected':'' ?>>Sold Out</option>
                        </select>
                    </div>
                    <div class="form-check" style="margin-bottom:12px">
                        <input type="checkbox" name="featured" id="featuredCb" <?= $prop['featured']?'checked':'' ?>>
                        <label for="featuredCb" class="form-check-label">
                            <i class="fas fa-star" style="color:var(--gold)"></i> Featured on Homepage
                        </label>
                    </div>
                    <div class="form-check" style="margin-bottom:16px">
                        <input type="checkbox" name="trending" id="trendingCb" <?= $prop['trending']?'checked':'' ?>>
                        <label for="trendingCb" class="form-check-label">
                            <i class="fas fa-fire" style="color:var(--danger)"></i> Mark as Trending
                        </label>
                    </div>
                    <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;padding:12px">
                        <i class="fas fa-save"></i> <?= $isEdit ? 'Update Property' : 'Publish Property' ?>
                    </button>
                    <?php if ($isEdit): ?>
                    <a href="<?= SITE_URL ?>/property/<?= htmlspecialchars($prop['slug']) ?>" target="_blank"
                       class="btn btn-outline" style="width:100%;justify-content:center;padding:10px;margin-top:8px">
                        <i class="fas fa-eye"></i> View on Website
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Auto-slug
document.getElementById('titleField')?.addEventListener('input', function() {
    const slugField = document.getElementById('slugField');
    if (!slugField.dataset.manual) {
        slugField.value = this.value.toLowerCase()
            .replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-').trim('-');
    }
});
document.getElementById('slugField')?.addEventListener('input', function() {
    this.dataset.manual = this.value ? '1' : '';
});

// Image preview
document.getElementById('imagesInput')?.addEventListener('change', function() {
    const grid = document.getElementById('imagePreviewGrid');
    grid.innerHTML = '';
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const d = document.createElement('div');
            d.style.cssText = 'border-radius:8px;overflow:hidden;border:1.5px solid var(--beige-dark)';
            d.innerHTML = `<img src="${e.target.result}" style="width:100%;height:80px;object-fit:cover">
                <div style="font-size:10px;padding:4px;text-align:center;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${file.name}</div>`;
            grid.appendChild(d);
        };
        reader.readAsDataURL(file);
    });
});

// Dropzone drag
const dz = document.getElementById('imageDropzone');
if (dz) {
    dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor='var(--maroon)'; dz.style.background='rgba(128,0,0,.04)'; });
    dz.addEventListener('dragleave', () => { dz.style.borderColor='var(--beige-dark)'; dz.style.background=''; });
    dz.addEventListener('drop', e => {
        e.preventDefault(); dz.style.borderColor='var(--beige-dark)'; dz.style.background='';
        document.getElementById('imagesInput').files = e.dataTransfer.files;
        document.getElementById('imagesInput').dispatchEvent(new Event('change'));
    });
}
</script>
<style>@media(max-width:768px){.prop-form-grid{grid-template-columns:1fr !important}}</style>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
