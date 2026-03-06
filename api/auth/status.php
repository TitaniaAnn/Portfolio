<?php
// api/auth/status.php — Returns current session info

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

cors_headers();
$admin = current_admin();

if ($admin) {
    json_response([
        'authenticated' => true,
        'name'   => $admin['name'],
        'email'  => $admin['email'],
        'avatar' => $admin['avatar_url'],
    ]);
} else {
    json_response(['authenticated' => false]);
}
