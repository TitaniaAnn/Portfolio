<?php
// ============================================================
// config/config.php  — Edit this file before deploying
// ============================================================

// ── Database ────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ── App ─────────────────────────────────────────────────────
define('APP_URL',    'https://cynthia-brown.com');   // No trailing slash
qdefine('APP_SECRET', 'openssl rand -hex 32');  // openssl rand -hex 32

// ── Session ──────────────────────────────────────────────────
define('SESSION_LIFETIME', 60 * 60 * 8);  // 8 hours in seconds

// ── Whitelisted admin emails ─────────────────────────────────
// Only these emails can log in to the admin panel
define('ADMIN_EMAILS', [
    // 'another@example.com',  // add more if needed
]);

// ── Google OAuth ─────────────────────────────────────────────
// Create at: https://console.cloud.google.com/apis/credentials
// Authorised redirect URI: APP_URL . '/api/auth/google/callback.php'
define('GOOGLE_CLIENT_ID',     '');
define('GOOGLE_CLIENT_SECRET', '');
define('GOOGLE_REDIRECT_URI',  APP_URL . '/api/auth/google/callback.php');

// ── GitHub OAuth ─────────────────────────────────────────────
// Create at: https://github.com/settings/developers
// Callback URL: APP_URL . '/api/auth/github/callback.php'
define('GITHUB_CLIENT_ID',     '');
define('GITHUB_CLIENT_SECRET', '');
define('GITHUB_REDIRECT_URI',  APP_URL . '/api/auth/github/callback.php');
