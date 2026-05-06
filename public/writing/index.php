<?php
// public/writing/index.php — Writing index. Server-rendered list of
// published posts, newest first, with tag-chip filter and a plain-text
// search. No JS framework; the filter/search runs against rows already
// in the DOM so it stays fast and JS-off readers still see the list.

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../includes/markdown.php';

$settings = fetch_settings();
$author   = $settings['name']    ?? '';
$tagline  = $settings['tagline'] ?? '';

$rows = db()->query("
    SELECT id, slug, title, excerpt, cover_image, tags, body_markdown, published_at
    FROM posts
    WHERE is_published = 1 AND published_at IS NOT NULL AND published_at <= NOW()
    ORDER BY published_at DESC
")->fetchAll();

$tagCounts = [];
foreach ($rows as &$r) {
    $r['tag_list'] = csv_to_array($r['tags']);
    foreach ($r['tag_list'] as $t) {
        $tagCounts[$t] = ($tagCounts[$t] ?? 0) + 1;
    }
    if (empty(trim((string)$r['excerpt']))) {
        $r['excerpt'] = markdown_excerpt($r['body_markdown'], 220);
    }
}
unset($r);
arsort($tagCounts);

$pageTitle = 'Writing' . ($author ? " — {$author}" : '');
$pageDesc  = $tagline ?: 'Writing on engineering, software, and the occasional ceramic.';
$pageUrl   = APP_URL . '/writing/';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
<link rel="canonical" href="<?= htmlspecialchars($pageUrl) ?>">
<meta property="og:type"        content="website">
<meta property="og:title"       content="<?= htmlspecialchars($pageTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
<meta property="og:url"         content="<?= htmlspecialchars($pageUrl) ?>">
<meta name="twitter:card"  content="summary">
<meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($pageDesc) ?>">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="icon" href="/favicon.ico">
<link rel="alternate" type="application/rss+xml" title="<?= htmlspecialchars($pageTitle) ?>" href="/writing/feed.xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="/fonts/syne.css" rel="stylesheet">
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
  body{background:var(--bg);color:var(--text);font-family:var(--mono);min-height:100vh}
  body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:9999;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,0.025) 2px,rgba(0,0,0,0.025) 4px)}
  a{color:inherit;text-decoration:none}
  a:focus-visible,button:focus-visible,input:focus-visible{outline:2px solid var(--amber);outline-offset:2px}

  /* NAV — mirrors public/index.php so the section feels like the same site. */
  nav{position:fixed;top:0;left:0;right:0;z-index:100;height:56px;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;background:rgba(13,13,13,0.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--border)}
  .nav-logo{font-family:var(--display);font-weight:800;font-size:1.1rem;color:var(--amber);letter-spacing:-0.02em}
  .nav-links{display:flex;gap:1.5rem;align-items:center}
  .nav-links a{color:var(--muted);font-size:0.72rem;letter-spacing:0.1em;text-transform:uppercase;transition:color 0.2s}
  .nav-links a:hover,.nav-links a.current{color:var(--amber)}
  @media(max-width:640px){nav{padding:0 1.2rem}.nav-links{gap:1rem}.nav-links a{font-size:0.65rem}}

  main{max-width:840px;margin:0 auto;padding:7rem 2rem 5rem}
  .page-head{margin-bottom:2.5rem}
  .page-num{font-size:0.62rem;color:var(--amber);letter-spacing:0.15em;text-transform:uppercase;border:1px solid var(--amber-dim);padding:0.2rem 0.5rem;display:inline-block;margin-bottom:1rem}
  .page-title{font-family:var(--display);font-size:clamp(2rem,5vw,3rem);font-weight:800;letter-spacing:-0.02em;line-height:1.2;margin-bottom:0.5rem}
  .page-sub{font-size:0.85rem;color:var(--muted);max-width:560px;line-height:1.6}
  .feed-link{font-size:0.62rem;color:var(--muted);letter-spacing:0.1em;text-transform:uppercase;border:1px solid var(--border);padding:0.25rem 0.6rem;margin-left:0.6rem;transition:all 0.2s}
  .feed-link:hover{border-color:var(--amber);color:var(--amber)}

  .controls{display:flex;flex-direction:column;gap:1rem;margin-bottom:2.5rem}
  .search{width:100%;background:var(--bg2);border:1px solid var(--border);color:var(--text);font-family:var(--mono);font-size:0.85rem;padding:0.7rem 1rem;outline:none;transition:border-color 0.2s}
  .search:focus{border-color:var(--amber-dim);box-shadow:0 0 0 2px var(--amber-glow)}
  .tag-row{display:flex;flex-wrap:wrap;gap:0.4rem;align-items:center}
  .tag-chip{background:transparent;border:1px solid var(--border);color:var(--muted);font-family:var(--mono);font-size:0.66rem;letter-spacing:0.06em;padding:0.3rem 0.6rem;cursor:pointer;transition:all 0.15s;text-transform:lowercase}
  .tag-chip:hover{border-color:var(--amber-dim);color:var(--text)}
  .tag-chip.active{background:var(--amber-glow);border-color:var(--amber);color:var(--amber)}
  .tag-count{color:var(--muted);font-size:0.6rem;margin-left:0.3rem}

  .post-list{display:flex;flex-direction:column;gap:1.2rem}
  .post-card{background:var(--bg2);border:1px solid var(--border);padding:1.4rem 1.5rem;display:flex;gap:1.4rem;transition:border-color 0.2s,transform 0.2s}
  .post-card:hover{border-color:var(--amber-dim);transform:translateY(-2px)}
  .post-cover{flex-shrink:0;width:130px;height:90px;background:var(--bg3);border:1px solid var(--border);overflow:hidden}
  .post-cover img{width:100%;height:100%;object-fit:cover;display:block}
  .post-body{flex:1;min-width:0}
  .post-meta{display:flex;align-items:center;gap:0.8rem;font-size:0.62rem;color:var(--muted);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:0.4rem}
  .post-date{color:var(--amber)}
  .post-title{font-family:var(--display);font-size:1.25rem;font-weight:700;color:var(--text);margin-bottom:0.5rem;line-height:1.25}
  .post-card:hover .post-title{color:var(--amber)}
  .post-excerpt{font-size:0.78rem;color:var(--muted);line-height:1.6;margin-bottom:0.6rem}
  .post-tags{display:flex;flex-wrap:wrap;gap:0.3rem}
  .post-tag{font-size:0.6rem;color:var(--muted);border:1px solid var(--border);padding:0.1rem 0.4rem;text-transform:lowercase}
  @media(max-width:640px){
    main{padding:5.5rem 1.2rem 3rem}
    .post-card{flex-direction:column;padding:1.2rem}
    .post-cover{width:100%;height:160px}
  }

  .empty{background:var(--bg2);border:1px dashed var(--border);padding:3rem 1.5rem;text-align:center;color:var(--muted);font-size:0.78rem}
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
  <header class="page-head">
    <div class="page-num">04 / writing</div>
    <h1 class="page-title">Writing</h1>
    <p class="page-sub">Notes, essays, and the occasional debugging story. <a href="/writing/feed.xml" class="feed-link">RSS</a></p>
  </header>

  <?php if (!$rows): ?>
    <div class="empty">Nothing published yet — check back soon.</div>
  <?php else: ?>
    <div class="controls">
      <input type="search" class="search" id="post-search" placeholder="Search posts…" aria-label="Search posts">
      <?php if ($tagCounts): ?>
      <div class="tag-row" id="tag-row">
        <button class="tag-chip active" type="button" data-tag="">All<span class="tag-count"><?= count($rows) ?></span></button>
        <?php foreach ($tagCounts as $tag => $count): ?>
          <button class="tag-chip" type="button" data-tag="<?= htmlspecialchars(strtolower($tag)) ?>"><?= htmlspecialchars($tag) ?><span class="tag-count"><?= $count ?></span></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="post-list" id="post-list">
      <?php foreach ($rows as $p):
        $tagsLower = array_map('strtolower', $p['tag_list']);
        $haystack  = strtolower($p['title'] . ' ' . $p['excerpt'] . ' ' . implode(' ', $p['tag_list']));
      ?>
      <a class="post-card"
         href="/writing/<?= rawurlencode($p['slug']) ?>/"
         data-tags="<?= htmlspecialchars(implode(' ', $tagsLower)) ?>"
         data-search="<?= htmlspecialchars($haystack) ?>">
        <?php if (!empty($p['cover_image'])): ?>
        <div class="post-cover"><img src="<?= htmlspecialchars($p['cover_image']) ?>" alt="" loading="lazy"></div>
        <?php endif; ?>
        <div class="post-body">
          <div class="post-meta">
            <span class="post-date"><?= htmlspecialchars(date('M j, Y', strtotime($p['published_at']))) ?></span>
          </div>
          <h2 class="post-title"><?= htmlspecialchars($p['title']) ?></h2>
          <?php if (!empty($p['excerpt'])): ?>
          <p class="post-excerpt"><?= htmlspecialchars($p['excerpt']) ?></p>
          <?php endif; ?>
          <?php if ($p['tag_list']): ?>
          <div class="post-tags">
            <?php foreach ($p['tag_list'] as $t): ?>
              <span class="post-tag"><?= htmlspecialchars($t) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="empty" id="empty-msg" style="display:none;margin-top:2rem">No posts match.</div>
  <?php endif; ?>
</main>

<footer>
  © <?= date('Y') ?> <?= htmlspecialchars($author ?: 'dev.portfolio') ?> · <a href="/writing/feed.xml">RSS</a>
</footer>

<script>
(function () {
  const cards   = Array.from(document.querySelectorAll('.post-card'));
  if (!cards.length) return;
  const search  = document.getElementById('post-search');
  const chips   = Array.from(document.querySelectorAll('.tag-chip'));
  const empty   = document.getElementById('empty-msg');
  let activeTag = '';
  let query     = '';

  function apply() {
    let visible = 0;
    const q = query.trim().toLowerCase();
    cards.forEach(card => {
      const tags = card.dataset.tags.split(' ').filter(Boolean);
      const matchTag = !activeTag || tags.includes(activeTag);
      const matchQ   = !q || card.dataset.search.includes(q);
      const show = matchTag && matchQ;
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    if (empty) empty.style.display = visible ? 'none' : 'block';
  }

  if (search) {
    search.addEventListener('input', e => { query = e.target.value; apply(); });
  }
  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      activeTag = chip.dataset.tag;
      apply();
    });
  });
})();
</script>

</body>
</html>
