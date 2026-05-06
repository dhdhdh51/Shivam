<?php
/**
 * LuxeEstate Realty - AJAX Handler
 * Handles: lead submission, property filtering, quick enquiry, newsletter
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/functions.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']); exit;
}

$action = sanitize($_POST['action'] ?? '');

switch ($action) {
    case 'submit_lead':
        handleLeadSubmission();
        break;

    case 'filter_properties':
        handlePropertyFilter();
        break;

    case 'quick_enquiry':
        handleQuickEnquiry();
        break;

    case 'contact_message':
        handleContactMessage();
        break;

    default:
        jsonResponse(false, 'Unknown action.');
}

// ─────────────────────────────────────────────
// Lead Submission
// ─────────────────────────────────────────────
function handleLeadSubmission(): void
{
    // CSRF
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh and try again.']); exit;
    }

    $name       = sanitize($_POST['name'] ?? '');
    $phone      = sanitize($_POST['phone'] ?? '');
    $email      = sanitize($_POST['email'] ?? '');
    $budget     = sanitize($_POST['budget'] ?? '');
    $message    = sanitize($_POST['message'] ?? '');
    $propertyId = (int)($_POST['property_id'] ?? 0);
    $source     = sanitize($_POST['source'] ?? 'website');

    if (empty($name) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Name and phone are required.']); exit;
    }
    if (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number.']); exit;
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']); exit;
    }

    $result = submitLead([
        'name' => $name, 'phone' => $phone, 'email' => $email, 'budget' => $budget,
        'message' => $message, 'property_id' => $propertyId, 'source' => $source,
    ]);

    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => 'Thank you! Our team will contact you shortly.']); exit;
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]); exit;
    }
}

// ─────────────────────────────────────────────
// Property Filter (AJAX)
// ─────────────────────────────────────────────
function handlePropertyFilter(): void
{
    $filters = [
        'city'     => sanitize($_POST['city'] ?? ''),
        'type'     => sanitize($_POST['type'] ?? ''),
        'bhk'      => sanitize($_POST['bhk'] ?? ''),
        'min_price'=> (float)($_POST['min_price'] ?? 0),
        'max_price'=> (float)($_POST['max_price'] ?? 0),
        'sort'     => sanitize($_POST['sort'] ?? 'newest'),
        'search'   => sanitize($_POST['search'] ?? ''),
        'status'   => 'active',
    ];

    $page   = max(1, (int)($_POST['page'] ?? 1));
    $result = getProperties($filters, $page);
    $total = $result['total'];
    $pages = $result['pages'];
    $props = $result['properties'];

    ob_start();
    if (empty($props)) {
        echo '<div class="no-results">
            <div class="no-results-icon"><i class="fas fa-search"></i></div>
            <h3>No Properties Found</h3>
            <p>Try adjusting your filters to find your perfect property.</p>
        </div>';
    } else {
        foreach ($props as $property) {
            include __DIR__ . '/includes/property-card.php';
        }
    }
    $html = ob_get_clean();

    // Pagination HTML
    $paginationHtml = '';
    if ($pages > 1) {
        $paginationHtml = buildPaginationHtml($page, $pages);
    }

    echo json_encode([
        'success'    => true,
        'html'       => $html,
        'pagination' => $paginationHtml,
        'total'      => $total,
        'page'       => $page,
        'pages'      => $pages,
    ]);
    exit;
}

// ─────────────────────────────────────────────
// Quick Enquiry (Property Detail Page)
// ─────────────────────────────────────────────
function handleQuickEnquiry(): void
{
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Security token expired.']); exit;
    }

    $name       = sanitize($_POST['name'] ?? '');
    $phone      = sanitize($_POST['phone'] ?? '');
    $email      = sanitize($_POST['email'] ?? '');
    $message    = sanitize($_POST['message'] ?? '');
    $propertyId = (int)($_POST['property_id'] ?? 0);

    if (empty($name) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Name and phone number are required.']); exit;
    }

    $result = submitLead([
        'name' => $name, 'phone' => $phone, 'email' => $email,
        'message' => $message, 'property_id' => $propertyId, 'source' => 'property_page',
    ]);

    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => "Enquiry sent! We'll get back to you very soon."]); exit;
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]); exit;
    }
}

// ─────────────────────────────────────────────
// Contact Page Message
// ─────────────────────────────────────────────
function handleContactMessage(): void
{
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Security token expired.']); exit;
    }

    $name    = sanitize($_POST['name'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? 'General Enquiry');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($phone) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Name, phone, and message are required.']); exit;
    }

    $result = submitLead([
        'name' => $name, 'phone' => $phone, 'email' => $email,
        'message' => "Subject: $subject\n\n$message", 'source' => 'contact_page',
    ]);

    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => "Your message has been sent! We'll respond within 24 hours."]); exit;
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]); exit;
    }
}

// ─────────────────────────────────────────────
// Pagination HTML Builder
// ─────────────────────────────────────────────
function buildPaginationHtml(int $current, int $total): string
{
    $html  = '<div class="pagination">';
    $range = 2;

    if ($current > 1) {
        $html .= '<button class="page-btn prev-btn" data-page="' . ($current - 1) . '"><i class="fas fa-chevron-left"></i></button>';
    }

    for ($i = 1; $i <= $total; $i++) {
        if ($i === 1 || $i === $total || ($i >= $current - $range && $i <= $current + $range)) {
            $active = ($i === $current) ? ' active' : '';
            $html  .= "<button class=\"page-btn{$active}\" data-page=\"{$i}\">{$i}</button>";
        } elseif ($i === $current - $range - 1 || $i === $current + $range + 1) {
            $html .= '<span class="page-dots">…</span>';
        }
    }

    if ($current < $total) {
        $html .= '<button class="page-btn next-btn" data-page="' . ($current + 1) . '"><i class="fas fa-chevron-right"></i></button>';
    }

    $html .= '</div>';
    return $html;
}
