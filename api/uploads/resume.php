<?php
// api/uploads/resume.php
// GET  — returns current resume URL (public)
// POST — upload a new PDF resume (admin only)

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

cors_headers();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = db()->prepare('SELECT `value` FROM settings WHERE `key` = ?');
    $stmt->execute(['resume_url']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    json_response(['url' => $row ? $row['value'] : null]);
}

require_admin();

if ($method !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (empty($_FILES['resume']) || $_FILES['resume']['error'] === UPLOAD_ERR_NO_FILE) {
    json_response(['error' => 'No file uploaded'], 422);
}

$file = $_FILES['resume'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    json_response(['error' => 'Upload error code: ' . $file['error']], 422);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if ($mime !== 'application/pdf') {
    json_response(['error' => 'Invalid file type. Only PDF files are accepted.'], 422);
}

// 10 MB max
if ($file['size'] > 10 * 1024 * 1024) {
    json_response(['error' => 'File too large (max 10 MB)'], 422);
}

$upload_dir = __DIR__ . '/../../uploads/resume/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (!move_uploaded_file($file['tmp_name'], $upload_dir . 'resume.pdf')) {
    json_response(['error' => 'Failed to save file'], 500);
}

$url = '/uploads/resume/resume.pdf';

db()->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?')
   ->execute(['resume_url', $url, $url]);

json_response(['url' => $url]);
