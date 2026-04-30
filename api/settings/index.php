<?php
// api/settings/index.php
// GET  — public, returns all settings as object
// POST — admin only, update settings (whitelisted keys, per-key validation)

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/util.php';

cors_headers();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    json_response(fetch_settings());
}

if ($method === 'POST') {
    $admin = require_admin_write();
    $b = get_json_body();

    // Per-key validators. Each returns the cleaned value or null to reject.
    $validators = [
        'name'         => fn($v) => trim_max($v, 255),
        'role'         => fn($v) => trim_max($v, 255),
        'email'        => fn($v) => validate_email_field($v),
        'github'       => fn($v) => trim_url_or_empty($v),
        'linkedin'     => fn($v) => trim_url_or_empty($v),
        'location'     => fn($v) => trim_max($v, 255),
        'tagline'      => fn($v) => trim_max($v, 500),
        'years_exp'    => fn($v) => trim_max($v, 32),
        'bio'          => fn($v) => trim_max($v, 5000),
        'ticker_items' => fn($v) => trim_max($v, 2000),
    ];

    $stmt = db()->prepare('INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');
    foreach ($validators as $key => $validate) {
        if (!array_key_exists($key, $b)) continue;
        $clean = $validate($b[$key]);
        if ($clean === null) {
            json_response(['error' => "Invalid value for '{$key}'"], 422);
        }
        $stmt->execute([$key, $clean]);
    }
    audit_log('settings.update', $admin['id']);
    json_response(fetch_settings());
}

json_response(['error' => 'Method not allowed'], 405);

// ── per-key validators ────────────────────────────────────────────────
function trim_max($v, int $max): ?string {
    if (!is_string($v) && !is_int($v) && !is_float($v)) return null;
    $v = trim((string)$v);
    if (strlen($v) > $max) return null;
    return $v;
}

function trim_url_or_empty($v): ?string {
    $v = trim_max($v, 500);
    if ($v === null) return null;
    if ($v === '') return '';
    return filter_var($v, FILTER_VALIDATE_URL) ? $v : null;
}

function validate_email_field($v): ?string {
    $v = trim_max($v, 255);
    if ($v === null) return null;
    if ($v === '') return '';
    return filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : null;
}
