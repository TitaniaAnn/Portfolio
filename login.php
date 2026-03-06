<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Already logged in? redirect to admin dashboard
if (current_admin()) {
    header('Location: ' . APP_URL . '/admin/');
    exit;
}

$error = $_GET['error'] ?? '';
$errorMessages = [
    'denied'       => 'Access denied — your account is not authorised.',
    'state_error'  => 'Security check failed. Please try again.',
    'token_error'  => 'Could not complete sign-in. Please try again.',
];
$errorText = $errorMessages[$error] ?? ($error ? htmlspecialchars($error) : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:#0d0d0d; --bg2:#141414; --bg3:#1a1a1a; --border:#2a2a2a;
    --amber:#f59e0b; --amber-dim:#b45309; --amber-glow:rgba(245,158,11,0.12);
    --text:#e8e0d0; --muted:#555;
    --mono:'JetBrains Mono',monospace; --display:'Syne',sans-serif;
  }
  *{margin:0;padding:0;box-sizing:border-box}
  body { background:var(--bg); color:var(--text); font-family:var(--mono); min-height:100vh; display:flex; align-items:center; justify-content:center; }
  body::before { content:''; position:fixed; inset:0; pointer-events:none; background-image:linear-gradient(var(--border) 1px,transparent 1px),linear-gradient(90deg,var(--border) 1px,transparent 1px); background-size:60px 60px; opacity:0.15; }

  .login-wrap { position:relative; z-index:1; width:100%; max-width:420px; padding:2rem; }

  .login-header { text-align:center; margin-bottom:2.5rem; }
  .login-logo { font-family:var(--display); font-size:1.6rem; font-weight:800; color:var(--amber); margin-bottom:0.4rem; }
  .login-sub { font-size:0.68rem; color:var(--muted); letter-spacing:0.1em; text-transform:uppercase; }

  .login-box { background:var(--bg2); border:1px solid var(--border); padding:2rem; box-shadow:0 0 60px rgba(0,0,0,0.5); }
  .login-box h2 { font-family:var(--display); font-size:1.1rem; font-weight:700; margin-bottom:0.4rem; }
  .login-box p  { font-size:0.73rem; color:var(--muted); line-height:1.6; margin-bottom:2rem; }

  .oauth-btn {
    display:flex; align-items:center; gap:0.8rem;
    width:100%; padding:0.85rem 1.2rem; margin-bottom:1rem;
    background:var(--bg3); border:1px solid var(--border);
    color:var(--text); font-family:var(--mono); font-size:0.78rem;
    letter-spacing:0.06em; cursor:pointer; text-decoration:none;
    transition:all 0.2s;
  }
  .oauth-btn:hover { border-color:var(--amber-dim); background:var(--amber-glow); color:var(--amber); }
  .oauth-btn:hover .btn-icon-wrap { border-color:var(--amber-dim); }
  .btn-icon-wrap { width:28px; height:28px; display:flex; align-items:center; justify-content:center; border:1px solid var(--border); flex-shrink:0; font-size:1rem; transition:border-color 0.2s; }
  .btn-label { flex:1; text-align:left; }
  .btn-arrow { color:var(--muted); font-size:0.8rem; }

  .divider { display:flex; align-items:center; gap:1rem; margin:1.5rem 0; }
  .divider::before,.divider::after { content:''; flex:1; height:1px; background:var(--border); }
  .divider span { font-size:0.6rem; color:var(--muted); letter-spacing:0.15em; text-transform:uppercase; }

  .security-note { margin-top:1.5rem; padding:0.8rem; background:var(--bg3); border-left:2px solid var(--amber-dim); font-size:0.68rem; color:var(--muted); line-height:1.6; }
  .security-note strong { color:var(--amber); }

  <?php if (!empty($error)): ?>
  .error-msg { background:rgba(248,113,113,0.08); border:1px solid #f87171; color:#f87171; padding:0.7rem 1rem; font-size:0.73rem; margin-bottom:1.2rem; }
  <?php endif; ?>

  .back-link { display:block; text-align:center; margin-top:1.5rem; font-size:0.68rem; color:var(--muted); text-decoration:none; transition:color 0.2s; }
  .back-link:hover { color:var(--amber); }
</style>
</head>
<body>

<div class="login-wrap">
  <div class="login-header">
    <div class="login-logo">⚙ Admin</div>
    <div class="login-sub">Portfolio Management</div>
  </div>
  <div class="login-box">
    <h2>Sign In</h2>
    <p>Only authorised accounts can access the admin panel. Sign in with your linked provider below.</p>

    <?php if ($errorText): ?>
    <div class="error-msg">⚠ <?= $errorText ?></div>
    <?php endif; ?>

    <a href="<?= APP_URL ?>/api/auth/google/login.php" class="oauth-btn">
      <div class="btn-icon-wrap">
        <svg width="16" height="16" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.2l6.7-6.7C35.8 2.2 30.3 0 24 0 14.7 0 6.7 5.4 2.9 13.3l7.8 6C12.5 13.1 17.8 9.5 24 9.5z"/><path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.2-.4-4.7H24v9h12.7c-.6 3-2.3 5.5-4.8 7.2l7.6 5.9c4.4-4.1 7-10.1 7-17.4z"/><path fill="#FBBC05" d="M10.7 28.7c-.5-1.5-.8-3.1-.8-4.7s.3-3.2.8-4.7l-7.8-6C1.1 16.5 0 20.1 0 24s1.1 7.5 2.9 10.7l7.8-6z"/><path fill="#34A853" d="M24 48c6.3 0 11.6-2.1 15.5-5.6l-7.6-5.9c-2.1 1.4-4.8 2.3-7.9 2.3-6.2 0-11.5-3.6-13.3-8.7l-7.8 6C6.7 42.6 14.7 48 24 48z"/></svg>
      </div>
      <span class="btn-label">Continue with Google</span>
      <span class="btn-arrow">→</span>
    </a>

    <div class="divider"><span>or</span></div>

    <a href="<?= APP_URL ?>/api/auth/github/login.php" class="oauth-btn">
      <div class="btn-icon-wrap">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="var(--text)"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.44 9.8 8.2 11.38.6.1.83-.26.83-.58v-2.03c-3.34.73-4.04-1.61-4.04-1.61-.54-1.38-1.33-1.75-1.33-1.75-1.09-.74.08-.73.08-.73 1.2.09 1.84 1.24 1.84 1.24 1.07 1.83 2.8 1.3 3.49 1 .1-.78.42-1.3.76-1.6-2.67-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.12-.3-.54-1.52.12-3.17 0 0 1.01-.32 3.3 1.23a11.5 11.5 0 0 1 3-.4c1.02.005 2.04.14 3 .4 2.28-1.55 3.3-1.23 3.3-1.23.66 1.65.24 2.87.12 3.17.77.84 1.24 1.91 1.24 3.22 0 4.61-2.81 5.63-5.48 5.92.43.37.81 1.1.81 2.22v3.29c0 .32.22.7.84.58C20.57 21.8 24 17.3 24 12c0-6.63-5.37-12-12-12z"/></svg>
      </div>
      <span class="btn-label">Continue with GitHub</span>
      <span class="btn-arrow">→</span>
    </a>

    <div class="security-note">
      <strong>🔒 Access restricted.</strong> Only whitelisted accounts can log in. Unauthorised sign-in attempts are blocked.
    </div>
  </div>
  <a href="/" class="back-link">← Back to portfolio</a>
</div>
</body>
</html>
