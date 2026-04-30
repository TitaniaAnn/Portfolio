<?php
// api/audit/index.php — paginated audit log viewer (admin, GET only)
//
// GET /api/audit/?limit=50&offset=0&action=login
//   limit  — max rows returned (1..500, default 50)
//   offset — pagination offset (>=0, default 0)
//   action — optional substring filter on the action column

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/util.php';

cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

require_admin();

// Pre-migration: report empty rather than a 500 from a missing table.
if (!table_exists('audit_log')) {
    json_response(['rows' => [], 'total' => 0, 'limit' => 0, 'offset' => 0]);
}

$limit  = max(1, min(500, (int)($_GET['limit']  ?? 50)));
$offset = max(0, (int)($_GET['offset'] ?? 0));
$action = trim((string)($_GET['action'] ?? ''));

$where  = '';
$params = [];
if ($action !== '') {
    $where = 'WHERE l.action LIKE :action';
    $params[':action'] = '%' . $action . '%';
}

$totalStmt = db()->prepare("SELECT COUNT(*) FROM audit_log l $where");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$rowStmt = db()->prepare("
    SELECT l.id, l.admin_id, l.action, l.detail, l.ip, l.created_at,
           a.name  AS admin_name,
           a.email AS admin_email
    FROM audit_log l
    LEFT JOIN admins a ON a.id = l.admin_id
    $where
    ORDER BY l.id DESC
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) $rowStmt->bindValue($k, $v);
$rowStmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$rowStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$rowStmt->execute();

json_response([
    'rows'   => $rowStmt->fetchAll(),
    'total'  => $total,
    'limit'  => $limit,
    'offset' => $offset,
]);
