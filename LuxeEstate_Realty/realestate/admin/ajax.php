<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../functions/functions.php';

session_start();
requireAdmin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// CSRF check for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        jsonResponse(false, 'Invalid security token.');
    }
}

$db = db();

switch ($action) {

    // ── Toggle property/blog/team status ──
    case 'toggle_status':
        $id    = (int)($_POST['id'] ?? 0);
        $type  = $_POST['type'] ?? '';
        $field = $_POST['field'] ?? 'status';

        $tables = ['property' => 'properties', 'blog' => 'blogs', 'team' => 'team_members', 'testimonial' => 'testimonials'];
        if (!$id || !isset($tables[$type])) jsonResponse(false, 'Invalid request.');

        $table = $tables[$type];

        if ($field === 'featured') {
            $stmt = $db->prepare("SELECT featured FROM $table WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(false, 'Record not found.');
            $new = $row['featured'] ? 0 : 1;
            $db->prepare("UPDATE $table SET featured = ? WHERE id = ?")->execute([$new, $id]);
            jsonResponse(true, 'Updated.', ['new_value' => $new]);
        } else {
            $stmt = $db->prepare("SELECT status FROM $table WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(false, 'Record not found.');
            $new = ($row['status'] === 'active') ? 'inactive' : 'active';
            $db->prepare("UPDATE $table SET status = ? WHERE id = ?")->execute([$new, $id]);
            jsonResponse(true, 'Updated.', ['new_value' => $new]);
        }

    // ── Delete record ──
    case 'delete_record':
        $id   = (int)($_POST['id'] ?? 0);
        $type = $_POST['type'] ?? '';

        $map = [
            'property'    => ['table' => 'properties',    'img_field' => null],
            'blog'        => ['table' => 'blogs',         'img_field' => 'image'],
            'team'        => ['table' => 'team_members',  'img_field' => 'image'],
            'testimonial' => ['table' => 'testimonials',  'img_field' => 'image'],
            'lead'        => ['table' => 'leads',         'img_field' => null],
        ];

        if (!$id || !isset($map[$type])) jsonResponse(false, 'Invalid request.');
        $info = $map[$type];

        // Delete associated images
        if ($info['img_field']) {
            $stmt = $db->prepare("SELECT {$info['img_field']} FROM {$info['table']} WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row && $row[$info['img_field']]) {
                $path = UPLOAD_DIR . basename($row[$info['img_field']]);
                if (file_exists($path)) unlink($path);
            }
        }

        // If property, delete its images too
        if ($type === 'property') {
            $imgs = $db->prepare("SELECT image_path FROM property_images WHERE property_id = ?");
            $imgs->execute([$id]);
            foreach ($imgs->fetchAll() as $img) {
                $path = UPLOAD_DIR . 'properties/' . basename($img['image_path']);
                if (file_exists($path)) unlink($path);
            }
            $db->prepare("DELETE FROM property_images WHERE property_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM leads WHERE property_id = ?")->execute([$id]);
        }

        $db->prepare("DELETE FROM {$info['table']} WHERE id = ?")->execute([$id]);
        jsonResponse(true, ucfirst($type) . ' deleted successfully.');

    // ── Update lead ──
    case 'update_lead':
        $id     = (int)($_POST['id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        $tag    = sanitize($_POST['tag'] ?? '');
        $notes  = sanitize($_POST['notes'] ?? '');

        $allowed_status = ['new', 'contacted', 'closed'];
        $allowed_tag    = ['', 'hot', 'warm', 'cold'];
        if (!$id || !in_array($status, $allowed_status) || !in_array($tag, $allowed_tag)) {
            jsonResponse(false, 'Invalid data.');
        }

        $db->prepare("UPDATE leads SET status=?, tag=?, notes=?, updated_at=NOW() WHERE id=?")
           ->execute([$status, $tag ?: null, $notes, $id]);
        jsonResponse(true, 'Lead updated.');

    // ── Get lead detail ──
    case 'get_lead':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) jsonResponse(false, 'Invalid ID.');
        $stmt = $db->prepare("
            SELECT l.*, p.title as property_title
            FROM leads l
            LEFT JOIN properties p ON l.property_id = p.id
            WHERE l.id = ?
        ");
        $stmt->execute([$id]);
        $lead = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$lead) jsonResponse(false, 'Lead not found.');
        jsonResponse(true, 'OK', $lead);

    // ── Delete property image ──
    case 'delete_property_image':
        $img_id = (int)($_POST['img_id'] ?? 0);
        if (!$img_id) jsonResponse(false, 'Invalid.');
        $stmt = $db->prepare("SELECT * FROM property_images WHERE id = ?");
        $stmt->execute([$img_id]);
        $img = $stmt->fetch();
        if (!$img) jsonResponse(false, 'Image not found.');
        $path = UPLOAD_DIR . 'properties/' . basename($img['image_path']);
        if (file_exists($path)) unlink($path);
        $db->prepare("DELETE FROM property_images WHERE id = ?")->execute([$img_id]);
        // If it was primary, make another primary
        if ($img['is_primary']) {
            $next = $db->prepare("SELECT id FROM property_images WHERE property_id = ? LIMIT 1");
            $next->execute([$img['property_id']]);
            $n = $next->fetch();
            if ($n) $db->prepare("UPDATE property_images SET is_primary=1 WHERE id=?")->execute([$n['id']]);
        }
        jsonResponse(true, 'Image deleted.');

    // ── Set primary image ──
    case 'set_primary_image':
        $img_id     = (int)($_POST['img_id'] ?? 0);
        $prop_id    = (int)($_POST['property_id'] ?? 0);
        if (!$img_id || !$prop_id) jsonResponse(false, 'Invalid.');
        $db->prepare("UPDATE property_images SET is_primary=0 WHERE property_id=?")->execute([$prop_id]);
        $db->prepare("UPDATE property_images SET is_primary=1 WHERE id=?")->execute([$img_id]);
        jsonResponse(true, 'Primary image updated.');

    default:
        jsonResponse(false, 'Unknown action.');
}
