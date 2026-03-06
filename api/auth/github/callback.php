<?php
// api/auth/github/callback.php — Handle GitHub OAuth callback

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
if (!$code) die('No authorization code received.');

// Exchange code for token
$ch = curl_init('https://github.com/login/oauth/access_token');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'client_id'     => GITHUB_CLIENT_ID,
        'client_secret' => GITHUB_CLIENT_SECRET,
        'code'          => $code,
        'redirect_uri'  => GITHUB_REDIRECT_URI,
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
]);
$tokenData = json_decode(curl_exec($ch), true) ?? [];
curl_close($ch);

$accessToken = $tokenData['access_token'] ?? '';
if (!$accessToken) die('Failed to obtain GitHub access token.');

// Fetch user profile
$profile = gh_get('https://api.github.com/user', $accessToken);

// GitHub may not expose email in profile — fetch separately
$emails = gh_get('https://api.github.com/user/emails', $accessToken);
$primaryEmail = '';
foreach ($emails as $e) {
    if ($e['primary'] && $e['verified']) {
        $primaryEmail = strtolower($e['email']);
        break;
    }
}
if (!$primaryEmail) $primaryEmail = strtolower($profile['email'] ?? '');
if (!$primaryEmail) die('Could not retrieve a verified email from GitHub.');

// Whitelist check
if (!is_allowed_email($primaryEmail)) {
    http_response_code(403);
    die('Access denied: ' . htmlspecialchars($primaryEmail) . ' is not an authorised admin. <a href="/">Go back</a>');
}

$name   = $profile['name'] ?? $profile['login'] ?? 'Unknown';
$avatar = $profile['avatar_url'] ?? '';

$adminId = upsert_admin($primaryEmail, $name, $avatar, 'github');
$token   = create_session($adminId);
$_SESSION['admin_token'] = $token;

header('Location: ' . APP_URL . '/admin/');
exit;

function gh_get(string $url, string $token): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer $token",
            'Accept: application/vnd.github+json',
            'User-Agent: PortfolioApp/1.0',
        ],
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true) ?? [];
}
