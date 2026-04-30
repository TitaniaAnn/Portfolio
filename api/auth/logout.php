<?php
// api/auth/logout.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';

$admin = current_admin();
if ($admin) {
    audit_log('logout', $admin['id']);
}
destroy_session();
header('Location: ' . APP_URL . '/admin/login.php');
exit;
