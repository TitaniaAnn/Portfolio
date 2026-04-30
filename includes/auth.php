<?php
// includes/auth.php — Session management & auth helpers

require_once __DIR__ . '/db.php';

function session_start_secure(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

/**
 * Returns the logged-in admin row (with `csrf_token` merged in) or null.
 * The csrf_token is the per-session double-submit value used by
 * require_admin_write() to validate state-changing requests.
 */
function current_admin(): ?array {
    session_start_secure();
    $token = $_SESSION['admin_token'] ?? null;
    if (!$token) return null;

    $pdo = db();
    try {
        $stmt = $pdo->prepare('
            SELECT a.*, s.csrf_token AS session_csrf
            FROM admins a
            JOIN sessions s ON s.admin_id = a.id
            WHERE s.id = ? AND s.expires_at > NOW()
        ');
        $stmt->execute([$token]);
    } catch (PDOException $e) {
        // sessions.csrf_token column may not exist yet (pre-migration).
        // Fall back so the user can still reach /admin/update.php to run it.
        $stmt = $pdo->prepare('
            SELECT a.* FROM admins a
            JOIN sessions s ON s.admin_id = a.id
            WHERE s.id = ? AND s.expires_at > NOW()
        ');
        $stmt->execute([$token]);
    }
    $row = $stmt->fetch();
    if (!$row) return null;
    $row['csrf_token'] = $row['session_csrf'] ?? '';
    unset($row['session_csrf']);

    // If the column exists but the session row was created before migrations
    // ran, lazily populate a CSRF token so the user doesn't have to log out
    // and back in. This runs at most once per session.
    if ($row['csrf_token'] === '') {
        $newCsrf = bin2hex(random_bytes(32));
        try {
            $pdo->prepare('UPDATE sessions SET csrf_token = ? WHERE id = ?')
                ->execute([$newCsrf, $token]);
            $row['csrf_token'] = $newCsrf;
        } catch (PDOException $e) {
            // Column still missing (migration not yet run) — leave empty.
        }
    }

    return $row;
}

/** Auth gate for read-only endpoints. */
function require_admin(): array {
    $admin = current_admin();
    if (!$admin) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    return $admin;
}

/**
 * Auth gate for state-changing endpoints. Verifies session AND that the
 * client supplied a matching CSRF token in the X-CSRF-Token header.
 * Skipped for GET/HEAD/OPTIONS — those should use require_admin instead.
 */
function require_admin_write(): array {
    $admin = require_admin();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        return $admin;
    }
    $supplied = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected = $admin['csrf_token'] ?? '';
    if ($expected === '' || !hash_equals($expected, $supplied)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'CSRF token missing or invalid']);
        exit;
    }
    return $admin;
}

function is_allowed_email(string $email): bool {
    return in_array(strtolower($email), array_map('strtolower', ADMIN_EMAILS), true);
}

function create_session(int $adminId): array {
    $token = bin2hex(random_bytes(32));
    $csrf  = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    try {
        db()->prepare('INSERT INTO sessions (id, admin_id, csrf_token, ip, user_agent, expires_at) VALUES (?,?,?,?,?,?)')
           ->execute([$token, $adminId, $csrf, $ip, $ua, $expires]);
    } catch (PDOException $e) {
        // Pre-migration: fall back to the legacy schema without csrf_token.
        db()->prepare('INSERT INTO sessions (id, admin_id, ip, user_agent, expires_at) VALUES (?,?,?,?,?)')
           ->execute([$token, $adminId, $ip, $ua, $expires]);
    }

    return ['token' => $token, 'csrf' => $csrf];
}

function destroy_session(): void {
    session_start_secure();
    $token = $_SESSION['admin_token'] ?? null;
    if ($token) {
        db()->prepare('DELETE FROM sessions WHERE id = ?')->execute([$token]);
    }
    session_destroy();
}

function upsert_admin(string $email, string $name, string $avatar, string $provider): int {
    $pdo = db();
    $stmt = $pdo->prepare('
        INSERT INTO admins (email, name, avatar_url, provider)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE name = VALUES(name), avatar_url = VALUES(avatar_url), provider = VALUES(provider)
    ');
    $stmt->execute([$email, $name, $avatar, $provider]);
    $id = $pdo->lastInsertId();
    if (!$id) {
        $lookup = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
        $lookup->execute([$email]);
        $id = $lookup->fetchColumn();
    }
    return (int)$id;
}
