<?php
// api/uploads/index.php — Image upload endpoint
// POST /api/uploads/ — Upload a project image (admin only)

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../includes/util.php';

cors_headers();
$admin = require_admin_write();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$check = validate_upload(
    $_FILES['image'] ?? [],
    ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'],
    UPLOAD_MAX_IMAGE
);

$filename   = random_filename($check['ext']);
$upload_dir = __DIR__ . '/../../uploads/projects/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
    json_response(['error' => 'Failed to save file'], 500);
}

audit_log('upload.image', $admin['id'], $filename);
json_response(['url' => '/uploads/projects/' . $filename]);
