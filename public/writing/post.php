<?php
// public/writing/post.php — Single writing post. Reached via the .htaccess
// rewrite that maps /writing/<slug>/ here. Renders markdown server-side and
// emits per-post Open Graph tags so social cards aren't generic.

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../includes/markdown.php';

$slug = $_GET['slug'] ?? '';
if ($slug === '' || !preg_match('/^[a-z0-9][a-z0-9-]*$/', $slug)) {
    http_response_code(404);
    echo '<h1>404 — Not Found</h1>';
    exit;
}

$stmt = db()->prepare('
    SELECT * FROM posts
    WHERE slug = ? AND is_published = 1 AND published_at IS NOT NULL AND published_at <= NOW()
    LIMIT 1
');
$stmt->execute([$slug]);
$post = $stmt->fetch();
if (!$post) {
    http_response_code(404);
    echo '<h1>404 — Post Not Found</h1><p><a href="/writing/">← Back to Writing</a></p>';
    exit;
}

$tags = csv_to_array($post['tags']);

$linked = [];
$lp = db()->prepare('
    SELECT p.id, p.title, p.short_description, p.summary_image, p.language
    FROM post_projects pp
    JOIN projects p ON p.id = pp.project_id
    WHERE pp.post_id = ?
    ORDER BY p.sort_order ASC, p.title ASC
');
$lp->execute([$post['id']]);
$linked = $lp->fetchAll();

$settings = fetch_settings();
$author   = $settings['name']   ?? '';

$html      = render_markdown($post['body_markdown']);
$excerpt   = trim((string)$post['excerpt']) !== ''
    ? $post['excerpt']
    : markdown_excerpt($post['body_markdown'], 220);

$pageUrl   = APP_URL . '/writing/' . rawurlencode($post['slug']) . '/';
$ogImage   = $post['cover_image'] ?: extract_first_image($html);
if ($ogImage && !preg_match('#^https?://#i', $ogImage)) {
    $ogImage = APP_URL . '/' . ltrim($ogImage, '/');
}
$pageTitle = $post['title'] . ($author ? " — {$author}" : '');

function extract_first_image(string $html): ?string {
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) return $m[1];
    return null;
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($excerpt) ?>">
<link rel="canonical" href="<?= htmlspecialchars($pageUrl) ?>">

<meta property="og:type"        content="article">
<meta property="og:title"       content="<?= htmlspecialchars($post['title']) ?>">
<meta property="og:description" content="<?= htmlspecialchars($excerpt) ?>">
<meta property="og:url"         content="<?= htmlspecialchars($pageUrl) ?>">
<?php if ($ogImage): ?>
<meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
<?php endif; ?>
<meta property="article:published_time" content="<?= htmlspecialchars(date('c', strtotime($post['published_at']))) ?>">
<?php if ($post['updated_at']): ?>
<meta property="article:modified_time" content="<?= htmlspecialchars(date('c', strtotime($post['updated_at']))) ?>">
<?php endif; ?>
<?php if ($author): ?>
<meta property="article:author" content="<?= htmlspecialchars($author) ?>">
<?php endif; ?>
<?php foreach ($tags as $t): ?>
<meta property="article:tag" content="<?= htmlspecialchars($t) ?>">
<?php endforeach; ?>

<meta name="twitter:card"  content="<?= $ogImage ? 'summary_large_image' : 'summary' ?>">
<meta name="twitter:title" content="<?= htmlspecialchars($post['title']) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($excerpt) ?>">
<?php if ($ogImage): ?>
<meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">
<?php endif; ?>

<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="icon" href="/favicon.ico">
<link rel="alternate" type="application/rss+xml" title="Writing" href="/writing/feed.xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="/fonts/syne.css" rel="stylesheet">

<script type="application/ld+json">
<?= json_encode([
    '@context'      => 'https://schema.org',
    '@type'         => 'BlogPosting',
    'headline'      => $post['title'],
    'description'   => $excerpt,
    'datePublished' => date('c', strtotime($post['published_at'])),
    'dateModified'  => $post['updated_at'] ? date('c', strtotime($post['updated_at'])) : null,
    'author'        => $author ? ['@type' => 'Person', 'name' => $author] : null,
    'image'         => $ogImage,
    'mainEntityOfPage' => $pageUrl,
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>

<style>
  :root {
    --bg:#0d0d0d; --bg2:#141414; --bg3:#1a1a1a; --border:#2a2a2a;
    --amber:#f59e0b; --amber-dim:#b45309; --amber-glow:rgba(245,158,11,0.12);
    --text:#e8e0d0; --muted:#666;
    --green:#4ade80; --blue:#60a5fa;
    --mono:'JetBrains Mono',monospace; --display:'Syne',sans-serif;
  }
  *{margin:0;padding:0;box-sizing:border-box}
  html{scroll-behavior:smooth}
  body{background:var(--bg);color:var(--text);font-family:var(--mono);min-height:100vh;line-height:1.7}
  body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:9999;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,0.025) 2px,rgba(0,0,0,0.025) 4px)}
  a{color:var(--amber);text-decoration:none}
  a:hover{text-decoration:underline}
  a:focus-visible,button:focus-visible{outline:2px solid var(--amber);outline-offset:2px}

  nav{position:fixed;top:0;left:0;right:0;z-index:100;height:56px;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;background:rgba(13,13,13,0.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--border)}
  .nav-logo{font-family:var(--display);font-weight:800;font-size:1.1rem;color:var(--amber);letter-spacing:-0.02em}
  .nav-links{display:flex;gap:1.5rem;align-items:center}
  .nav-links a{color:var(--muted);font-size:0.72rem;letter-spacing:0.1em;text-transform:uppercase;transition:color 0.2s}
  .nav-links a:hover,.nav-links a.current{color:var(--amber)}
  @media(max-width:640px){nav{padding:0 1.2rem}.nav-links{gap:1rem}.nav-links a{font-size:0.65rem}}

  main{max-width:720px;margin:0 auto;padding:7rem 2rem 4rem}
  .back-link{display:inline-block;font-size:0.62rem;color:var(--muted);letter-spacing:0.12em;text-transform:uppercase;margin-bottom:2rem;transition:color 0.2s}
  .back-link:hover{color:var(--amber);text-decoration:none}

  .post-header{margin-bottom:2.5rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border)}
  .post-header .meta{font-size:0.65rem;color:var(--muted);letter-spacing:0.12em;text-transform:uppercase;margin-bottom:1rem;display:flex;flex-wrap:wrap;gap:1rem;align-items:center}
  .post-header .date{color:var(--amber)}
  .post-header h1{font-family:var(--display);font-size:clamp(1.8rem,5vw,2.8rem);font-weight:800;letter-spacing:-0.02em;line-height:1.15;margin-bottom:1rem}
  .post-tags{display:flex;flex-wrap:wrap;gap:0.4rem}
  .post-tag{font-size:0.62rem;color:var(--muted);border:1px solid var(--border);padding:0.15rem 0.45rem;text-transform:lowercase}

  .post-cover{margin:0 0 2.5rem;border:1px solid var(--border)}
  .post-cover img{width:100%;height:auto;display:block}

  /* Body — read-optimized typography. */
  .post-body{font-size:0.95rem;color:var(--text)}
  .post-body p{margin:0 0 1.3rem}
  .post-body h1,.post-body h2,.post-body h3,.post-body h4{font-family:var(--display);font-weight:700;letter-spacing:-0.01em;margin:2.4rem 0 1rem;line-height:1.25}
  .post-body h1{font-size:1.7rem}
  .post-body h2{font-size:1.4rem}
  .post-body h3{font-size:1.15rem}
  .post-body h4{font-size:1rem;text-transform:uppercase;letter-spacing:0.06em;color:var(--amber)}
  .post-body ul,.post-body ol{margin:0 0 1.3rem 1.5rem}
  .post-body li{margin-bottom:0.4rem}
  .post-body blockquote{border-left:3px solid var(--amber-dim);background:var(--bg2);padding:0.8rem 1.2rem;margin:1.5rem 0;color:var(--muted);font-style:italic}
  .post-body blockquote p:last-child{margin-bottom:0}
  .post-body code{background:var(--bg3);border:1px solid var(--border);padding:0.1rem 0.35rem;font-size:0.85em;color:var(--amber)}
  .post-body pre{background:var(--bg2);border:1px solid var(--border);padding:1rem 1.2rem;overflow-x:auto;margin:1.5rem 0;font-size:0.82rem;line-height:1.55}
  .post-body pre code{background:transparent;border:none;padding:0;color:var(--text)}
  .post-body img{max-width:100%;height:auto;border:1px solid var(--border);margin:1.5rem 0;display:block}
  .post-body a{color:var(--amber);text-decoration:underline;text-decoration-color:var(--amber-dim);text-underline-offset:3px}
  .post-body a:hover{text-decoration-color:var(--amber)}
  .post-body hr{border:none;border-top:1px solid var(--border);margin:2.5rem 0}
  .post-body table{width:100%;border-collapse:collapse;margin:1.5rem 0;font-size:0.85rem}
  .post-body th,.post-body td{border:1px solid var(--border);padding:0.5rem 0.75rem;text-align:left}
  .post-body th{background:var(--bg2);font-weight:600;color:var(--amber)}

  .linked-projects{margin-top:3.5rem;padding-top:2rem;border-top:1px solid var(--border)}
  .linked-projects-label{font-size:0.62rem;color:var(--amber);letter-spacing:0.15em;text-transform:uppercase;margin-bottom:1rem}
  .linked-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem}
  .linked-card{background:var(--bg2);border:1px solid var(--border);padding:1rem 1.2rem;color:var(--text);transition:border-color 0.2s,transform 0.2s;text-decoration:none}
  .linked-card:hover{border-color:var(--amber-dim);transform:translateY(-2px);text-decoration:none}
  .linked-card .lc-lang{font-size:0.55rem;letter-spacing:0.1em;text-transform:uppercase;color:var(--amber);margin-bottom:0.4rem}
  .linked-card .lc-title{font-family:var(--display);font-size:1rem;font-weight:700;margin-bottom:0.3rem}
  .linked-card .lc-sub{font-size:0.7rem;color:var(--muted);line-height:1.5}

  footer{padding:2rem;text-align:center;color:var(--muted);font-size:0.65rem;letter-spacing:0.1em;text-transform:uppercase;border-top:1px solid var(--border);margin-top:4rem}
  footer a{color:var(--amber)}
</style>
</head>
<body>

<nav>
  <a href="/" class="nav-logo">dev.portfolio</a>
  <div class="nav-links">
    <a href="/#about">About</a>
    <a href="/#projects">Projects</a>
    <a href="/writing/" class="current">Writing</a>
    <a href="/#contact">Contact</a>
  </div>
</nav>

<main>
  <a href="/writing/" class="back-link">← All writing</a>

  <header class="post-header">
    <div class="meta">
      <span class="date"><?= htmlspecialchars(date('M j, Y', strtotime($post['published_at']))) ?></span>
      <?php if ($author): ?><span><?= htmlspecialchars($author) ?></span><?php endif; ?>
    </div>
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <?php if ($tags): ?>
    <div class="post-tags">
      <?php foreach ($tags as $t): ?>
        <span class="post-tag"><?= htmlspecialchars($t) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </header>

  <?php if (!empty($post['cover_image'])): ?>
  <div class="post-cover"><img src="<?= htmlspecialchars($post['cover_image']) ?>" alt=""></div>
  <?php endif; ?>

  <article class="post-body"><?= $html ?></article>

  <?php if ($linked): ?>
  <section class="linked-projects" aria-label="Related projects">
    <div class="linked-projects-label">Related Projects</div>
    <div class="linked-grid">
      <?php foreach ($linked as $proj): ?>
        <a class="linked-card" href="/#projects">
          <?php if (!empty($proj['language'])): ?>
            <div class="lc-lang"><?= htmlspecialchars($proj['language']) ?></div>
          <?php endif; ?>
          <div class="lc-title"><?= htmlspecialchars($proj['title']) ?></div>
          <?php if (!empty($proj['short_description'])): ?>
            <div class="lc-sub"><?= htmlspecialchars($proj['short_description']) ?></div>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</main>

<footer>
  © <?= date('Y') ?> <?= htmlspecialchars($author ?: 'dev.portfolio') ?> · <a href="/writing/">More writing</a> · <a href="/writing/feed.xml">RSS</a>
</footer>

</body>
</html>
