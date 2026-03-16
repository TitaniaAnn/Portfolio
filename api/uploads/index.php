<?php
// api/uploads/index.php — Image upload endpoint
// POST /api/uploads/ — Upload a project image (admin only)

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

cors_headers();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    json_response(['error' => 'No image uploaded'], 422);
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    json_response(['error' => 'Upload error code: ' . $file['error']], 422);
}

// Validate MIME type using finfo (not user-supplied content-type)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
if (!array_key_exists($mime, $allowed)) {
    json_response(['error' => 'Invalid file type. Allowed: jpg, png, gif, webp'], 422);
}

// 5 MB max
if ($file['size'] > 5 * 1024 * 1024) {
    json_response(['error' => 'File too large (max 5 MB)'], 422);
}

$ext      = $allowed[$mime];
$filename = bin2hex(random_bytes(16)) . '.' . $ext;

$upload_dir = __DIR__ . '/../../uploads/projects/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
    json_response(['error' => 'Failed to save file'], 500);
}

json_response(['url' => '/uploads/projects/' . $filename]);
