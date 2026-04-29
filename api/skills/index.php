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

cors_headers();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($method === 'GET') {
    $rows = db()->query('SELECT * FROM skill_groups ORDER BY sort_order ASC, id ASC')->fetchAll();
    foreach ($rows as &$r) {
        $r['skills'] = $r['skills'] ? array_map('trim', explode(',', $r['skills'])) : [];
    }
    json_response($rows);
}

require_admin();

if ($method === 'POST') {
    $b     = get_json_body();
    $label = trim($b['label'] ?? '');
    if (!$label) json_response(['error' => 'label required'], 422);
    $skills = implode(',', array_map('trim', (array)($b['skills'] ?? [])));
    $sort   = (int)($b['sort_order'] ?? 0);
    $stmt   = db()->prepare('INSERT INTO skill_groups (label, skills, sort_order) VALUES (?,?,?)');
    $stmt->execute([$label, $skills, $sort]);
    $row = db()->query('SELECT * FROM skill_groups WHERE id = ' . db()->lastInsertId())->fetch();
    $row['skills'] = $row['skills'] ? array_map('trim', explode(',', $row['skills'])) : [];
    json_response($row, 201);
}

if ($method === 'PUT') {
    if (!$id) json_response(['error' => 'id required'], 422);
    $b      = get_json_body();
    $label  = trim($b['label'] ?? '');
    if (!$label) json_response(['error' => 'label required'], 422);
    $skills = implode(',', array_map('trim', (array)($b['skills'] ?? [])));
    $sort   = (int)($b['sort_order'] ?? 0);
    db()->prepare('UPDATE skill_groups SET label=?, skills=?, sort_order=? WHERE id=?')
       ->execute([$label, $skills, $sort, $id]);
    $row = db()->query("SELECT * FROM skill_groups WHERE id = $id")->fetch();
    if (!$row) json_response(['error' => 'Not found'], 404);
    $row['skills'] = $row['skills'] ? array_map('trim', explode(',', $row['skills'])) : [];
    json_response($row);
}

if ($method === 'DELETE') {
    if (!$id) json_response(['error' => 'id required'], 422);
    db()->prepare('DELETE FROM skill_groups WHERE id = ?')->execute([$id]);
    json_response(['success' => true]);
}

json_response(['error' => 'Method not allowed'], 405);
