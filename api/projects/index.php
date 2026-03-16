<?php
// api/projects/index.php — CRUD for projects
// GET    /api/projects/         — list all (public)
// POST   /api/projects/         — create (admin)
// PUT    /api/projects/?id=N    — update (admin)
// DELETE /api/projects/?id=N    — delete (admin)

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

cors_headers();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ── GET (public) ───────────────────────────────────────────
if ($method === 'GET') {
    $rows = db()->query('SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC')->fetchAll();

    // Fetch all images in one query and group by project_id
    $imgRows = db()->query('SELECT project_id, url FROM project_images ORDER BY project_id, sort_order ASC, id ASC')->fetchAll();
    $imgMap = [];
    foreach ($imgRows as $img) {
        $imgMap[$img['project_id']][] = $img['url'];
    }

    foreach ($rows as &$r) {
        $r['tags']   = $r['tags'] ? array_map('trim', explode(',', $r['tags'])) : [];
        $r['images'] = $imgMap[$r['id']] ?? [];
    }
    json_response($rows);
}

// ── All write operations require auth ─────────────────────
require_admin();

// ── POST — create ──────────────────────────────────────────
if ($method === 'POST') {
    $b = get_json_body();
    $title  = trim($b['title']  ?? '');
    $desc   = trim($b['description'] ?? '');
    $lang   = trim($b['language'] ?? '');
    if (!$title || !$desc || !$lang) json_response(['error' => 'title, description, language required'], 422);

    $tags   = implode(',', array_map('trim', (array)($b['tags'] ?? [])));
    $github = trim($b['github_url'] ?? '');
    $demo   = trim($b['demo_url']   ?? '');
    $status = in_array($b['status'] ?? '', ['active','wip','archived']) ? $b['status'] : 'active';
    $sort   = (int)($b['sort_order'] ?? 0);

    $stmt = db()->prepare('
        INSERT INTO projects (title, description, language, tags, github_url, demo_url, status, sort_order)
        VALUES (?,?,?,?,?,?,?,?)
    ');
    $stmt->execute([$title, $desc, $lang, $tags, $github, $demo, $status, $sort]);
    $newId = db()->lastInsertId();

    // Insert images
    if (!empty($b['images'])) {
        $imgStmt = db()->prepare('INSERT INTO project_images (project_id, url, sort_order) VALUES (?,?,?)');
        foreach ((array)$b['images'] as $i => $url) {
            $url = trim($url);
            if ($url) $imgStmt->execute([$newId, $url, $i]);
        }
    }

    $row = db()->query("SELECT * FROM projects WHERE id = $newId")->fetch();
    $row['tags']   = $row['tags'] ? array_map('trim', explode(',', $row['tags'])) : [];
    $row['images'] = fetch_images($newId);
    json_response($row, 201);
}

// ── PUT — update ───────────────────────────────────────────
if ($method === 'PUT') {
    if (!$id) json_response(['error' => 'id required'], 422);
    $b = get_json_body();

    $fields = [];
    $params = [];

    $allowed = ['title','description','language','github_url','demo_url','status','sort_order'];
    foreach ($allowed as $f) {
        if (array_key_exists($f, $b)) {
            $fields[] = "`$f` = ?";
            $params[] = $f === 'sort_order' ? (int)$b[$f] : trim($b[$f]);
        }
    }
    if (array_key_exists('tags', $b)) {
        $fields[] = '`tags` = ?';
        $params[] = implode(',', array_map('trim', (array)$b['tags']));
    }

    if ($fields) {
        $params[] = $id;
        db()->prepare('UPDATE projects SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    }

    // Replace images if provided
    if (array_key_exists('images', $b)) {
        db()->prepare('DELETE FROM project_images WHERE project_id = ?')->execute([$id]);
        $imgStmt = db()->prepare('INSERT INTO project_images (project_id, url, sort_order) VALUES (?,?,?)');
        foreach ((array)$b['images'] as $i => $url) {
            $url = trim($url);
            if ($url) $imgStmt->execute([$id, $url, $i]);
        }
    }

    $row = db()->query("SELECT * FROM projects WHERE id = $id")->fetch();
    if (!$row) json_response(['error' => 'Not found'], 404);
    $row['tags']   = $row['tags'] ? array_map('trim', explode(',', $row['tags'])) : [];
    $row['images'] = fetch_images($id);
    json_response($row);
}

// ── DELETE ──────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!$id) json_response(['error' => 'id required'], 422);
    db()->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
    json_response(['success' => true, 'deleted_id' => $id]);
}

json_response(['error' => 'Method not allowed'], 405);

// ── Helper ──────────────────────────────────────────────────
function fetch_images(int $project_id): array {
    $stmt = db()->prepare('SELECT url FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$project_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
