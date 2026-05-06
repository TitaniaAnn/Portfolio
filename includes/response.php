<?php
// includes/response.php — JSON helpers & CORS

function json_response(mixed $data, int $code = 200): never {
    // Discard anything that landed in output buffers before us
    // (BOMs, notices, accidental echoes from included files).
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function cors_headers(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    // Strict equality — `str_starts_with($origin, APP_URL)` would let
    // `https://cynthia-brown.com.attacker.com` slip through.
    if ($origin === APP_URL) {
        header("Access-Control-Allow-Origin: $origin");
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, X-CSRF-Token');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function get_json_body(): array {
    // `??` only coalesces null. json_decode also returns scalars for valid
    // non-object bodies like `false` or `42`, which would violate the array
    // return type and 500 every endpoint that uses this helper.
    $decoded = json_decode((string) file_get_contents('php://input'), true);
    return is_array($decoded) ? $decoded : [];
}
