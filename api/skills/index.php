<?php
// api/skills/index.php
// GET    /api/skills/         — list all groups (public)
// POST   /api/skills/         — create group (admin)
// PUT    /api/skills/?id=N    — update group (admin)
// DELETE /api/skills/?id=N    — delete group (admin)

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/util.php';

cors_headers();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($method === 'GET') {
    $rows = db()->query('SELECT * FROM skill_groups ORDER BY sort_order ASC, id ASC')->fetchAll();
    foreach ($rows as &$r) {
        $r['skills'] = csv_to_array($r['skills']);
    }
    json_response($rows);
}

$admin = require_admin_write();

if ($method === 'POST') {
    $b     = get_json_body();
    $label = trim($b['label'] ?? '');
    if ($label === '') json_response(['error' => 'label required'], 422);
    if (strlen($label) > 255) json_response(['error' => 'label too long'], 422);

    $skills = implode(',', csv_to_array(is_array($b['skills'] ?? null) ? implode(',', (array)$b['skills']) : ($b['skills'] ?? '')));
    $sort   = (int)($b['sort_order'] ?? 0);

    db()->prepare('INSERT INTO skill_groups (label, skills, sort_order) VALUES (?,?,?)')
        ->execute([$label, $skills, $sort]);
    $newId = (int) db()->lastInsertId();

    audit_log('skill_group.create', $admin['id'], "id={$newId} label={$label}");
    json_response(load_group($newId), 201);
}

if ($method === 'PUT') {
    if (!$id) json_response(['error' => 'id required'], 422);
    $b      = get_json_body();
    $label  = trim($b['label'] ?? '');
    if ($label === '') json_response(['error' => 'label required'], 422);
    if (strlen($label) > 255) json_response(['error' => 'label too long'], 422);

    $skills = implode(',', csv_to_array(is_array($b['skills'] ?? null) ? implode(',', (array)$b['skills']) : ($b['skills'] ?? '')));
    $sort   = (int)($b['sort_order'] ?? 0);

    db()->prepare('UPDATE skill_groups SET label=?, skills=?, sort_order=? WHERE id=?')
        ->execute([$label, $skills, $sort, $id]);

    $row = load_group($id);
    if (!$row) json_response(['error' => 'Not found'], 404);
    audit_log('skill_group.update', $admin['id'], "id={$id}");
    json_response($row);
}

if ($method === 'DELETE') {
    if (!$id) json_response(['error' => 'id required'], 422);
    db()->prepare('DELETE FROM skill_groups WHERE id = ?')->execute([$id]);
    audit_log('skill_group.delete', $admin['id'], "id={$id}");
    json_response(['success' => true]);
}

json_response(['error' => 'Method not allowed'], 405);

function load_group(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM skill_groups WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) return null;
    $row['skills'] = csv_to_array($row['skills']);
    return $row;
}
