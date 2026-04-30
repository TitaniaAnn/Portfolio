<?php
// ============================================================
// config/config.php  — loads environment from .env at the project root
// ============================================================
//
// Real values live in `.env` (which is git-ignored and blocked from web
// access by .htaccess). `.env.example` documents the keys.
//
// Start output buffering at the earliest possible moment so any stray
// output (BOM, PHP notice, accidental echo) is captured by the buffer
// and discarded by json_response() instead of corrupting JSON responses.
if (!ob_get_level()) ob_start();

// ── Tiny .env loader ────────────────────────────────────────
// Accepts KEY=value lines, optional surrounding double or single quotes,
// `#` comments, and blank lines. No nested interpolation, no variable
// substitution — just enough for our use case.
(function () {
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;

    $path = dirname(__DIR__) . '/.env';
    if (!is_readable($path)) return;

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $eq = strpos($line, '=');
        if ($eq === false) continue;
        $key = trim(substr($line, 0, $eq));
        $val = trim(substr($line, $eq + 1));
        if ($key === '') continue;
        // Strip surrounding quotes if present.
        if (strlen($val) >= 2) {
            $first = $val[0];
            $last  = substr($val, -1);
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $val = substr($val, 1, -1);
            }
        }
        // Populate getenv() and $_ENV so anything downstream that prefers
        // those mechanisms still works.
        if (getenv($key) === false) {
            putenv("{$key}={$val}");
            $_ENV[$key] = $val;
        }
    }
})();

function env(string $key, string $default = ''): string {
    $v = $_ENV[$key] ?? getenv($key);
    return ($v === false || $v === '') ? $default : (string)$v;
}

// ── Database ────────────────────────────────────────────────
define('DB_HOST',    env('DB_HOST', 'localhost'));
define('DB_NAME',    env('DB_NAME'));
define('DB_USER',    env('DB_USER'));
define('DB_PASS',    env('DB_PASS'));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// ── App ─────────────────────────────────────────────────────
define('APP_URL',    rtrim(env('APP_URL'), '/'));   // No trailing slash
define('APP_SECRET', env('APP_SECRET'));

// ── Session ─────────────────────────────────────────────────
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', (string)(60 * 60 * 8)));

// ── Whitelisted admin emails ────────────────────────────────
$_admins = array_values(array_filter(array_map(
    'trim',
    explode(',', env('ADMIN_EMAILS'))
), static fn($s) => $s !== ''));
define('ADMIN_EMAILS', $_admins);
unset($_admins);

// ── Google OAuth ────────────────────────────────────────────
define('GOOGLE_CLIENT_ID',     env('GOOGLE_CLIENT_ID'));
define('GOOGLE_CLIENT_SECRET', env('GOOGLE_CLIENT_SECRET'));
define('GOOGLE_REDIRECT_URI',  APP_URL . '/api/auth/google/callback.php');

// ── GitHub OAuth ────────────────────────────────────────────
define('GITHUB_CLIENT_ID',     env('GITHUB_CLIENT_ID'));
define('GITHUB_CLIENT_SECRET', env('GITHUB_CLIENT_SECRET'));
define('GITHUB_REDIRECT_URI',  APP_URL . '/api/auth/github/callback.php');

// ── Upload limits (bytes) ───────────────────────────────────
define('UPLOAD_MAX_IMAGE',  5  * 1024 * 1024);   //  5 MB for project images
define('UPLOAD_MAX_RESUME', 10 * 1024 * 1024);   // 10 MB for the PDF résumé
