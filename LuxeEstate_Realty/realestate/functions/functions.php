<?php
require_once __DIR__ . '/../config/database.php';

// =====================================================
// Utility Functions
// =====================================================

function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function generateSlug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function uniqueSlug(string $slug, string $table, int $excludeId = 0): string {
    $pdo = db();
    $original = $slug;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = ?";
        $params = [$slug];
        if ($excludeId > 0) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if (!$stmt->fetch()) break;
        $slug = $original . '-' . $i++;
    }
    return $slug;
}

function formatPrice(float $price, string $symbol = '₹'): string {
    if ($price >= 10000000) {
        return $symbol . number_format($price / 10000000, 2) . ' Cr';
    } elseif ($price >= 100000) {
        return $symbol . number_format($price / 100000, 2) . ' L';
    }
    return $symbol . number_format($price, 0);
}

function timeAgo(string $datetime): string {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time / 60) . ' mins ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    if ($time < 604800) return floor($time / 86400) . ' days ago';
    return date('d M Y', strtotime($datetime));
}

function redirect(string $url): void {
    header("Location: $url");
    exit();
}

function isAjax(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function getClientIP(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            return explode(',', $_SERVER[$key])[0];
        }
    }
    return '0.0.0.0';
}

// =====================================================
// Settings Functions
// =====================================================
function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    
    $stmt = db()->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    $cache[$key] = $row ? ($row['setting_value'] ?? $default) : $default;
    return $cache[$key];
}

function getAllSettings(): array {
    $stmt = db()->query("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function updateSetting(string $key, string $value): void {
    $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                           ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
    $stmt->execute([$key, $value, $value]);
}

// =====================================================
// CSRF Protection
// =====================================================
function generateCSRF(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_expire'] = time() + CSRF_TOKEN_EXPIRE;
    return $token;
}

function validateCSRF(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_expire'])) return false;
    if ($_SESSION['csrf_expire'] < time()) { unset($_SESSION['csrf_token']); return false; }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// =====================================================
// Image Upload
// =====================================================
function uploadImage(array $file, string $folder = 'properties'): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error: ' . $file['error']];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large. Max 5MB allowed.'];
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Only JPEG, PNG, WebP allowed.'];
    }
    
    $ext = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'jpg'
    };
    
    $uploadDir = UPLOAD_DIR . $folder . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    
    $filename = uniqid('img_', true) . '.' . $ext;
    $path = $uploadDir . $filename;
    
    // Compress and resize
    if (function_exists('imagecreatefromjpeg')) {
        compressImage($file['tmp_name'], $path, $mime);
    } else {
        move_uploaded_file($file['tmp_name'], $path);
    }
    
    return ['success' => true, 'path' => 'uploads/' . $folder . '/' . $filename, 'filename' => $filename];
}

function compressImage(string $source, string $dest, string $mime): bool {
    $quality = 85;
    $image = match($mime) {
        'image/jpeg' => imagecreatefromjpeg($source),
        'image/png' => imagecreatefrompng($source),
        'image/webp' => imagecreatefromwebp($source),
        default => null
    };
    if (!$image) { return move_uploaded_file($source, $dest); }
    
    $w = imagesx($image); $h = imagesy($image);
    $maxW = 1200; $maxH = 900;
    if ($w > $maxW || $h > $maxH) {
        $ratio = min($maxW / $w, $maxH / $h);
        $nW = intval($w * $ratio); $nH = intval($h * $ratio);
        $resized = imagecreatetruecolor($nW, $nH);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $nW, $nH, $w, $h);
        imagedestroy($image); $image = $resized;
    }
    
    $result = match($mime) {
        'image/png' => imagepng($image, $dest, 8),
        'image/webp' => imagewebp($image, $dest, $quality),
        default => imagejpeg($image, $dest, $quality)
    };
    imagedestroy($image);
    return $result;
}

function deleteImage(string $path): void {
    $fullPath = SITE_ROOT . '/' . ltrim($path, '/');
    if (file_exists($fullPath)) unlink($fullPath);
}

// =====================================================
// Property Functions
// =====================================================
function getFeaturedProperties(int $limit = 6): array {
    $stmt = db()->prepare("
        SELECT p.*, pi.image_path AS primary_image 
        FROM properties p
        LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
        WHERE p.status = 'active' AND p.featured = 1
        ORDER BY p.created_at DESC LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getTrendingProperties(int $limit = 4): array {
    $stmt = db()->prepare("
        SELECT p.*, pi.image_path AS primary_image 
        FROM properties p
        LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
        WHERE p.status = 'active' AND p.trending = 1
        ORDER BY p.views DESC LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getPropertyBySlug(string $slug): ?array {
    $stmt = db()->prepare("SELECT * FROM properties WHERE slug = ? AND status != 'inactive'");
    $stmt->execute([$slug]);
    $prop = $stmt->fetch();
    if (!$prop) return null;
    
    // Increment views
    db()->prepare("UPDATE properties SET views = views + 1 WHERE id = ?")->execute([$prop['id']]);
    
    // Get images
    $imgStmt = db()->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC");
    $imgStmt->execute([$prop['id']]);
    $prop['images'] = $imgStmt->fetchAll();
    $prop['amenities'] = $prop['amenities'] ? json_decode($prop['amenities'], true) : [];
    $prop['nearby'] = $prop['nearby'] ? json_decode($prop['nearby'], true) : [];
    return $prop;
}

function getProperties(array $filters = [], int $page = 1): array {
    $perPage = (int)getSetting('properties_per_page', PROPERTIES_PER_PAGE);
    $offset = ($page - 1) * $perPage;
    
    $where = ["p.status = 'active'"];
    $params = [];
    
    if (!empty($filters['city'])) { $where[] = "p.city = ?"; $params[] = $filters['city']; }
    if (!empty($filters['type'])) { $where[] = "p.type = ?"; $params[] = $filters['type']; }
    if (!empty($filters['bhk'])) { $where[] = "p.bhk = ?"; $params[] = (int)$filters['bhk']; }
    if (!empty($filters['min_price'])) { $where[] = "p.price >= ?"; $params[] = (float)$filters['min_price']; }
    if (!empty($filters['max_price'])) { $where[] = "p.price <= ?"; $params[] = (float)$filters['max_price']; }
    if (!empty($filters['search'])) {
        $where[] = "(p.title LIKE ? OR p.location LIKE ? OR p.city LIKE ?)";
        $s = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$s, $s, $s]);
    }
    
    $whereSQL = implode(' AND ', $where);
    $sort = match($filters['sort'] ?? 'newest') {
        'price_asc' => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'popular' => 'p.views DESC',
        default => 'p.created_at DESC'
    };
    
    $countStmt = db()->prepare("SELECT COUNT(*) FROM properties p WHERE $whereSQL");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    
    $params[] = $perPage;
    $params[] = $offset;
    
    $stmt = db()->prepare("
        SELECT p.*, pi.image_path AS primary_image 
        FROM properties p
        LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
        WHERE $whereSQL ORDER BY $sort LIMIT ? OFFSET ?
    ");
    $stmt->execute($params);
    
    return [
        'properties' => $stmt->fetchAll(),
        'total' => $total,
        'pages' => ceil($total / $perPage),
        'current_page' => $page,
        'per_page' => $perPage
    ];
}

function getCities(): array {
    $stmt = db()->query("SELECT DISTINCT city FROM properties WHERE status = 'active' ORDER BY city");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// =====================================================
// Lead Functions  
// =====================================================
function submitLead(array $data): array {
    if (empty($data['name']) || empty($data['phone'])) {
        return ['success' => false, 'message' => 'Name and phone are required.'];
    }
    if (!preg_match('/^[6-9]\d{9}$/', preg_replace('/[\s\-\+]/', '', $data['phone']))) {
        return ['success' => false, 'message' => 'Please enter a valid Indian mobile number.'];
    }
    
    // Check duplicate (same phone + 1 hour)
    $stmt = db()->prepare("SELECT id FROM leads WHERE phone = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$data['phone']]);
    if ($stmt->fetch()) {
        return ['success' => true, 'message' => 'Our team will contact you shortly!'];
    }
    
    $insert = db()->prepare("
        INSERT INTO leads (name, phone, email, budget, property_id, property_name, message, source, ip_address, user_agent, utm_source, utm_medium, utm_campaign)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert->execute([
        sanitize($data['name']),
        sanitize($data['phone']),
        sanitize($data['email'] ?? ''),
        sanitize($data['budget'] ?? ''),
        (int)($data['property_id'] ?? 0) ?: null,
        sanitize($data['property_name'] ?? ''),
        sanitize($data['message'] ?? ''),
        sanitize($data['source'] ?? 'website'),
        getClientIP(),
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        sanitize($_GET['utm_source'] ?? ''),
        sanitize($_GET['utm_medium'] ?? ''),
        sanitize($_GET['utm_campaign'] ?? ''),
    ]);
    
    return ['success' => true, 'message' => 'Thank you! Our team will contact you within 24 hours.'];
}

// =====================================================
// Blog Functions
// =====================================================
function getFeaturedBlogs(int $limit = 3): array {
    $stmt = db()->prepare("
        SELECT b.*, u.name AS author_name FROM blogs b
        LEFT JOIN users u ON u.id = b.author_id
        WHERE b.status = 'published'
        ORDER BY b.featured DESC, b.created_at DESC LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getBlogBySlug(string $slug): ?array {
    $stmt = db()->prepare("
        SELECT b.*, u.name AS author_name FROM blogs b
        LEFT JOIN users u ON u.id = b.author_id
        WHERE b.slug = ? AND b.status = 'published'
    ");
    $stmt->execute([$slug]);
    $blog = $stmt->fetch();
    if ($blog) db()->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?")->execute([$blog['id']]);
    return $blog ?: null;
}

// =====================================================
// Testimonials
// =====================================================
function getTestimonials(int $limit = 6): array {
    $stmt = db()->prepare("SELECT * FROM testimonials WHERE status = 'active' ORDER BY sort_order ASC, id DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// =====================================================
// Team
// =====================================================
function getTeamMembers(int $limit = 4): array {
    $stmt = db()->prepare("SELECT * FROM team_members WHERE status = 'active' ORDER BY sort_order ASC, id ASC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// =====================================================
// Stats
// =====================================================
function getSiteStats(): array {
    return [
        'properties' => getSetting('stats_properties', '500'),
        'clients' => getSetting('stats_clients', '1200'),
        'cities' => getSetting('stats_cities', '25'),
        'years' => getSetting('stats_years', '15'),
    ];
}

// =====================================================
// Admin Auth Functions
// =====================================================
function adminLogin(string $email, string $password): array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $stmt = db()->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([sanitize($email)]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_name'] = $user['name'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['admin_login_time'] = time();
    
    db()->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
    session_regenerate_id(true);
    
    return ['success' => true, 'redirect' => ADMIN_URL . '/dashboard.php'];
}

function isAdminLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['admin_id'])) return false;
    if (time() - ($_SESSION['admin_login_time'] ?? 0) > SESSION_EXPIRE) {
        session_destroy();
        return false;
    }
    return true;
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        redirect(ADMIN_URL . '/login.php');
    }
}

function adminLogout(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_unset();
    session_destroy();
    redirect(ADMIN_URL . '/login.php');
}

function getAdminStats(): array {
    $pdo = db();
    return [
        'total_properties' => $pdo->query("SELECT COUNT(*) FROM properties WHERE status != 'inactive'")->fetchColumn(),
        'active_properties' => $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'active'")->fetchColumn(),
        'total_leads' => $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn(),
        'new_leads' => $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetchColumn(),
        'hot_leads' => $pdo->query("SELECT COUNT(*) FROM leads WHERE tag = 'hot'")->fetchColumn(),
        'total_blogs' => $pdo->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn(),
        'total_enquiries' => $pdo->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
    ];
}

function getRecentLeads(int $limit = 10): array {
    $stmt = db()->prepare("
        SELECT l.*, p.title AS property_title 
        FROM leads l
        LEFT JOIN properties p ON p.id = l.property_id
        ORDER BY l.created_at DESC LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function exportLeadsCSV(array $leads): void {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="leads_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Name', 'Phone', 'Email', 'Budget', 'Property', 'Message', 'Status', 'Tag', 'Source', 'Date']);
    foreach ($leads as $l) {
        fputcsv($out, [$l['id'], $l['name'], $l['phone'], $l['email'] ?? '', $l['budget'] ?? '', $l['property_name'] ?? '', $l['message'] ?? '', $l['status'], $l['tag'], $l['source'], $l['created_at']]);
    }
    fclose($out);
    exit();
}
