<?php
/**
 * Real-time visitor tracking endpoint.
 * Called by JS every 30 seconds to ping presence and return active count.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store');

$file = sys_get_temp_dir() . '/luxe_visitors.json';
$now  = time();
$ttl  = 300; // 5 minutes — visitor considered gone after this

$visitors = [];
if (file_exists($file)) {
    $raw = @file_get_contents($file);
    if ($raw) $visitors = json_decode($raw, true) ?: [];
}

// Update / add this visitor's last-seen timestamp
$sid = session_id();
if ($sid) $visitors[$sid] = $now;

// Remove stale entries
$visitors = array_filter($visitors, fn($ts) => ($now - $ts) < $ttl);

// Save (suppress error if /tmp not writable — graceful degradation)
@file_put_contents($file, json_encode($visitors), LOCK_EX);

$count = max(1, count($visitors));

echo json_encode(['count' => $count]);
