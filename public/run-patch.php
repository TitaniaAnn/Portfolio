<?php
// TEMPORARY — delete this file after running the migration.
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$admin = current_admin();
if (!$admin) {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}

$patches = [
    'patch_001_add_project_year' => 'ALTER TABLE projects ADD COLUMN year YEAR NULL AFTER sort_order',
];

require_once __DIR__ . '/../includes/db.php';

$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run'])) {
    foreach ($patches as $name => $sql) {
        try {
            db()->exec($sql);
            $results[$name] = ['ok' => true, 'msg' => 'Applied successfully.'];
        } catch (PDOException $e) {
            $code = $e->getCode();
            // 1060 = column already exists — treat as already applied
            if ($code == '42000' && str_contains($e->getMessage(), 'Duplicate column')) {
                $results[$name] = ['ok' => true, 'msg' => 'Already applied (column exists).'];
            } else {
                $results[$name] = ['ok' => false, 'msg' => htmlspecialchars($e->getMessage())];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Run Database Patch</title>
<style>
  body { background:#0d0d0d; color:#e8e0d0; font-family:'Courier New',monospace; padding:2rem; max-width:640px; margin:0 auto; }
  h1 { color:#f59e0b; font-size:1.1rem; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:0.4rem; }
  .warn { background:#2a1a00; border:1px solid #b45309; color:#fbbf24; padding:0.75rem 1rem; font-size:0.8rem; margin-bottom:2rem; }
  table { width:100%; border-collapse:collapse; margin-bottom:2rem; font-size:0.8rem; }
  th { text-align:left; color:#666; font-weight:normal; border-bottom:1px solid #2a2a2a; padding:0.4rem 0.5rem; }
  td { padding:0.5rem; border-bottom:1px solid #1a1a1a; }
  .ok  { color:#4ade80; }
  .err { color:#f87171; }
  code { background:#1a1a1a; padding:0.15rem 0.4rem; font-size:0.75rem; color:#60a5fa; }
  button { background:transparent; border:1px solid #b45309; color:#f59e0b; font-family:inherit; font-size:0.8rem; letter-spacing:0.1em; text-transform:uppercase; padding:0.6rem 1.5rem; cursor:pointer; }
  button:hover { background:rgba(245,158,11,0.1); }
  .back { display:inline-block; margin-top:1.5rem; color:#666; font-size:0.75rem; text-decoration:none; }
  .back:hover { color:#f59e0b; }
</style>
</head>
<body>

<h1>Database Patch Runner</h1>
<div class="warn">⚠ Delete this file from the server after running. It should not be left in place.</div>

<?php if ($results): ?>
  <table>
    <tr><th>Patch</th><th>Result</th></tr>
    <?php foreach ($results as $name => $r): ?>
    <tr>
      <td><code><?= $name ?></code></td>
      <td class="<?= $r['ok'] ? 'ok' : 'err' ?>"><?= $r['ok'] ? '✓' : '✗' ?> <?= $r['msg'] ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <p style="font-size:0.8rem;color:#666">Done. You can now <a href="/admin/" style="color:#f59e0b">return to the admin panel</a> and delete this file.</p>
<?php else: ?>
  <p style="font-size:0.8rem;color:#666;margin-bottom:1.5rem">The following patches will be applied:</p>
  <table>
    <tr><th>Patch</th><th>SQL</th></tr>
    <?php foreach ($patches as $name => $sql): ?>
    <tr>
      <td><code><?= $name ?></code></td>
      <td><code><?= htmlspecialchars($sql) ?></code></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <form method="post">
    <button type="submit" name="run" value="1">▶ Run Patches</button>
  </form>
<?php endif; ?>

<a href="/admin/" class="back">← Back to Admin</a>
</body>
</html>
