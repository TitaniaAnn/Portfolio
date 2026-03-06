<?php
// api/auth/logout.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

destroy_session();
header('Location: ' . APP_URL . '/admin/login.php');
exit;
