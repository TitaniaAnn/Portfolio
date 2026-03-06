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

// Returns the logged-in admin row, or null
function current_admin(): ?array {
    session_start_secure();
    $token = $_SESSION['admin_token'] ?? null;
    if (!$token) return null;

    $pdo = db();
    $stmt = $pdo->prepare('
        SELECT a.* FROM admins a
        JOIN sessions s ON s.admin_id = a.id
        WHERE s.id = ? AND s.expires_at > NOW()
    ');
    $stmt->execute([$token]);
    return $stmt->fetch() ?: null;
}

function require_admin(): array {
    $admin = current_admin();
    if (!$admin) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    return $admin;
}

function is_allowed_email(string $email): bool {
    return in_array(strtolower($email), array_map('strtolower', ADMIN_EMAILS), true);
}

function create_session(int $adminId): string {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    db()->prepare('INSERT INTO sessions (id, admin_id, ip, user_agent, expires_at) VALUES (?,?,?,?,?)')
       ->execute([$token, $adminId, $ip, $ua, $expires]);

    return $token;
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
        $id = $pdo->query("SELECT id FROM admins WHERE email = " . $pdo->quote($email))->fetchColumn();
    }
    return (int)$id;
}
?>