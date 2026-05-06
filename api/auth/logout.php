<?php
// api/auth/logout.php — POST + CSRF required so a third-party page cannot
// log the admin out via a one-pixel <img src=...> CSRF.

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

$admin = current_admin();
if ($admin) {
    $supplied = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    $expected = (string)($admin['csrf_token'] ?? '');
    if ($expected === '' || !hash_equals($expected, (string)$supplied)) {
        http_response_code(403);
        exit;
    }
    audit_log('logout', $admin['id']);
}

destroy_session();
header('Location: ' . APP_URL . '/admin/login.php');
exit;
