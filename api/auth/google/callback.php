<?php
// api/auth/google/callback.php — Handle Google OAuth callback

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

session_start_secure();

// Validate state
$state = $_GET['state'] ?? '';
if (!hash_equals($_SESSION['oauth_state'] ?? '', $state)) {
    die('Invalid OAuth state. <a href="/">Go back</a>');
}
unset($_SESSION['oauth_state']);

$code = $_GET['code'] ?? '';
if (!$code) {
    die('No authorization code received.');
}

// Exchange code for token
$tokenRes = http_post('https://oauth2.googleapis.com/token', [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
]);

if (empty($tokenRes['access_token'])) {
    die('Failed to obtain access token.');
}

// Fetch user info
$userRes = http_get('https://www.googleapis.com/oauth2/v2/userinfo', $tokenRes['access_token']);

$email  = strtolower($userRes['email'] ?? '');
$name   = $userRes['name']    ?? 'Unknown';
$avatar = $userRes['picture'] ?? '';

if (!$email) die('Could not retrieve email from Google.');

// Whitelist check
if (!is_allowed_email($email)) {
    http_response_code(403);
    die('Access denied: ' . htmlspecialchars($email) . ' is not an authorised admin. <a href="/">Go back</a>');
}

// Create or update admin record
$adminId = upsert_admin($email, $name, $avatar, 'google');

// Create session
$token = create_session($adminId);
$_SESSION['admin_token'] = $token;

header('Location: ' . APP_URL . '/admin/');
exit;

// ── Helpers ────────────────────────────────────────────────
function http_post(string $url, array $data): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true) ?? [];
}

function http_get(string $url, string $token): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => ["Authorization: Bearer $token"],
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true) ?? [];
}
