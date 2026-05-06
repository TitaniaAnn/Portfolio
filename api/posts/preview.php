<?php
// api/posts/preview.php — Render a markdown body to HTML so the admin can
// preview formatting before publishing. Admin-only; doesn't touch the DB.

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/markdown.php';

cors_headers();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}
require_admin_write();

$body = get_json_body();
$md   = (string)($body['body_markdown'] ?? '');
if (strlen($md) > 200000) {
    json_response(['error' => 'body too large'], 422);
}

json_response(['html' => render_markdown($md)]);
