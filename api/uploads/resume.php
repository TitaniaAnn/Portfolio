<?php
// api/uploads/resume.php
// GET  — returns current resume URL (public)
// POST — upload a new PDF resume (admin only)

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../includes/util.php';

cors_headers();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = db()->prepare('SELECT `value` FROM settings WHERE `key` = ?');
    $stmt->execute(['resume_url']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    json_response(['url' => $row ? $row['value'] : null]);
}

$admin = require_admin_write();

if ($method !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

validate_upload(
    $_FILES['resume'] ?? [],
    ['application/pdf' => 'pdf'],
    UPLOAD_MAX_RESUME
);

$upload_dir = __DIR__ . '/../../uploads/resume/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (!move_uploaded_file($_FILES['resume']['tmp_name'], $upload_dir . 'resume.pdf')) {
    json_response(['error' => 'Failed to save file'], 500);
}

$url = '/uploads/resume/resume.pdf';

db()->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?')
   ->execute(['resume_url', $url, $url]);

audit_log('upload.resume', $admin['id']);
json_response(['url' => $url]);
