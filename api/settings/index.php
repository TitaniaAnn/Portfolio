<?php
// api/settings/index.php
// GET  — public, returns all settings as object
// POST — admin only, update settings

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

cors_headers();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = db()->query('SELECT `key`, `value` FROM settings')->fetchAll();
    $out  = [];
    foreach ($rows as $r) $out[$r['key']] = $r['value'];
    json_response($out);
}

if ($method === 'POST') {
    require_admin();
    $b = get_json_body();
    $allowed = ['name','role','email','github','linkedin','location','tagline','years_exp','bio','ticker_items'];
    $stmt = db()->prepare('INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');
    foreach ($allowed as $key) {
        if (array_key_exists($key, $b)) {
            $stmt->execute([$key, trim($b[$key])]);
        }
    }
    $rows = db()->query('SELECT `key`, `value` FROM settings')->fetchAll();
    $out  = [];
    foreach ($rows as $r) $out[$r['key']] = $r['value'];
    json_response($out);
}

json_response(['error' => 'Method not allowed'], 405);
