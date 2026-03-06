<?php
// api/auth/github/login.php — Redirect to GitHub OAuth

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

session_start_secure();

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = http_build_query([
    'client_id'    => GITHUB_CLIENT_ID,
    'redirect_uri' => GITHUB_REDIRECT_URI,
    'scope'        => 'read:user user:email',
    'state'        => $state,
]);

header('Location: https://github.com/login/oauth/authorize?' . $params);
exit;
