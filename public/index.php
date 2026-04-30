<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portfolio</title>
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="icon" href="/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;600;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0d0d0d; --bg2: #141414; --bg3: #1a1a1a;
    --border: #2a2a2a;
    --amber: #f59e0b; --amber-dim: #b45309; --amber-glow: rgba(245,158,11,0.12);
    --text: #e8e0d0; --muted: #666;
    --green: #4ade80; --blue: #60a5fa; --red: #f87171;
    --mono: 'JetBrains Mono', monospace;
    --display: 'Syne', sans-serif;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  html { scroll-behavior: smooth; }
  body { background:var(--bg); color:var(--text); font-family:var(--mono); overflow-x:hidden; }
  body::before {
    content:''; position:fixed; inset:0; pointer-events:none; z-index:9999;
    background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
    opacity:0.35;
  }
  body::after {
    content:''; position:fixed; inset:0; pointer-events:none; z-index:9998;
    background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,0.025) 2px,rgba(0,0,0,0.025) 4px);
  }

  /* Visible focus ring for keyboard users on every interactive element. */
  a:focus-visible, button:focus-visible, [tabindex]:focus-visible {
    outline: 2px solid var(--amber);
    outline-offset: 2px;
  }
  /* Visually-hidden helper for screen reader text */
  .sr-only { position:absolute !important; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0 0 0 0); white-space:nowrap; border:0; }

  .cursor { display:inline-block; width:10px; height:1.2em; background:var(--amber); vertical-align:text-bottom; animation:blink 1s step-end infinite; margin-left:2px; }
  @keyframes blink { 50%{opacity:0} }
  @keyframes fadeUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
  @keyframes float  { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }

  /* NAV */
  nav { position:fixed; top:0; left:0; right:0; z-index:100; height:56px; padding:0 2rem; display:flex; align-items:center; justify-content:space-between; background:rgba(13,13,13,0.92); backdrop-filter:blur(12px); border-bottom:1px solid var(--border); }
  .nav-logo { font-family:var(--display); font-weight:800; font-size:1.1rem; color:var(--amber); text-decoration:none; letter-spacing:-0.02em; }
  .nav-links { display:flex; gap:1.5rem; align-items:center; }
  .nav-links a { color:var(--muted); text-decoration:none; font-size:0.72rem; letter-spacing:0.1em; text-transform:uppercase; transition:color 0.2s; }
  .nav-links a:hover { color:var(--amber); }
  .nav-admin { background:transparent; border:1px solid var(--amber-dim); color:var(--amber); font-family:var(--mono); font-size:0.68rem; letter-spacing:0.1em; padding:0.35rem 0.85rem; cursor:pointer; text-transform:uppercase; text-decoration:none; transition:all 0.2s; }
  .nav-admin:hover { background:var(--amber-glow); }
  .nav-hamburger { display:none; flex-direction:column; gap:5px; background:none; border:none; cursor:pointer; padding:4px; }
  .nav-hamburger span { display:block; width:22px; height:2px; background:var(--text); transition:all 0.25s; }
  .nav-hamburger.open span:nth-child(1) { transform:translateY(7px) rotate(45deg); }
  .nav-hamburger.open span:nth-child(2) { opacity:0; }
  .nav-hamburger.open span:nth-child(3) { transform:translateY(-7px) rotate(-45deg); }
  @media (max-width: 640px) {
    .nav-hamburger { display:flex; }
    .nav-links { display:none; flex-direction:column; align-items:flex-start; gap:0; position:absolute; top:56px; left:0; right:0; background:rgba(13,13,13,0.97); border-bottom:1px solid var(--border); padding:0.5rem 0; }
    .nav-links.open { display:flex; }
    .nav-links a { padding:0.75rem 2rem; width:100%; font-size:0.75rem; }
    .nav-admin { margin:0.5rem 2rem 0.75rem; }
  }

  /* HERO */
  #hero { min-height:100vh; display:flex; align-items:center; padding:7rem 2rem 4rem; position:relative; overflow:hidden; }
  .hero-grid { position:absolute; inset:0; background-image:linear-gradient(var(--border) 1px,transparent 1px),linear-gradient(90deg,var(--border) 1px,transparent 1px); background-size:60px 60px; opacity:0.25; mask-image:radial-gradient(ellipse 80% 60% at 50% 50%,black,transparent); }
  .hero-inner { max-width:900px; margin:0 auto; position:relative; z-index:1; }
  .hero-tag { display:inline-flex; align-items:center; gap:0.5rem; background:var(--amber-glow); border:1px solid var(--amber-dim); color:var(--amber); font-size:0.68rem; letter-spacing:0.12em; padding:0.35rem 0.8rem; text-transform:uppercase; margin-bottom:2rem; animation:fadeUp 0.5s ease both; }
  .hero-tag::before { content:'▶'; font-size:0.5rem; }
  .hero-name { font-family:var(--display); font-size:clamp(3.5rem,10vw,7rem); font-weight:800; line-height:0.9; letter-spacing:-0.03em; animation:fadeUp 0.5s 0.1s ease both; margin-bottom:0.5rem; }
  .hero-name .accent { color:var(--amber); }
  .hero-sub { font-size:clamp(0.95rem,2.5vw,1.3rem); color:var(--muted); margin-bottom:2.5rem; animation:fadeUp 0.5s 0.2s ease both; }
  .hero-sub .typed { color:var(--text); }
  .hero-prompt { font-size:0.82rem; color:var(--muted); margin-bottom:3rem; animation:fadeUp 0.5s 0.3s ease both; }
  .hero-prompt .pc { color:var(--green); margin-right:0.5rem; }
  .hero-ctas { display:flex; gap:1rem; flex-wrap:wrap; animation:fadeUp 0.5s 0.4s ease both; }
  .btn-primary { background:var(--amber); color:#0d0d0d; border:none; padding:0.8rem 1.8rem; font-family:var(--mono); font-size:0.78rem; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; cursor:pointer; text-decoration:none; display:inline-block; transition:all 0.2s; clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,0 100%); }
  .btn-primary:hover { background:#fbbf24; transform:translateY(-2px); box-shadow:0 8px 30px rgba(245,158,11,0.3); }
  .btn-secondary { background:transparent; color:var(--text); border:1px solid var(--border); padding:0.8rem 1.8rem; font-family:var(--mono); font-size:0.78rem; letter-spacing:0.08em; text-transform:uppercase; cursor:pointer; text-decoration:none; display:inline-block; transition:all 0.2s; }
  .btn-secondary:hover { border-color:var(--amber); color:var(--amber); }
  .hero-stats { display:flex; gap:3rem; margin-top:4rem; padding-top:2rem; border-top:1px solid var(--border); animation:fadeUp 0.5s 0.5s ease both; flex-wrap:wrap; }
  .stat-num { font-family:var(--display); font-size:2rem; font-weight:800; color:var(--amber); }
  .stat-label { font-size:0.68rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.1em; margin-top:0.2rem; }

  /* SECTIONS */
  section { padding:5rem 2rem; }
  .section-inner { max-width:1000px; margin:0 auto; }
  .section-header { display:flex; align-items:center; gap:1rem; margin-bottom:3rem; }
  .section-num { font-size:0.62rem; color:var(--amber); letter-spacing:0.15em; text-transform:uppercase; border:1px solid var(--amber-dim); padding:0.2rem 0.5rem; }
  .section-title { font-family:var(--display); font-size:2rem; font-weight:700; }
  .section-line { flex:1; height:1px; background:linear-gradient(90deg,var(--border),transparent); }

  /* ABOUT */
  #about { background:var(--bg); }
  .about-grid { display:grid; grid-template-columns:1fr 1fr; gap:4rem; align-items:start; }
  @media(max-width:700px){.about-grid{grid-template-columns:1fr;gap:2rem}}
  .about-text p { color:var(--muted); line-height:1.8; font-size:0.83rem; margin-bottom:1rem; }
  .about-text p strong { color:var(--text); }
  .skills-block { display:flex; flex-direction:column; gap:1.4rem; }
  .skill-group-label { font-size:0.58rem; letter-spacing:0.16em; text-transform:uppercase; color:var(--amber); margin-bottom:0.5rem; }
  .skill-tags { display:flex; flex-wrap:wrap; gap:0.4rem; }
  .skill-tag { font-size:0.68rem; color:var(--muted); border:1px solid var(--border); padding:0.2rem 0.55rem; letter-spacing:0.04em; transition:all 0.2s; }
  .skill-tag:hover { border-color:var(--amber-dim); color:var(--text); }

  /* PROJECTS */
  #projects { background:var(--bg2); }
  .projects-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1.5rem; }
  .project-card { background:var(--bg3); border:1px solid var(--border); padding:1.5rem; position:relative; transition:all 0.3s; overflow:hidden; animation:fadeUp 0.5s ease both; display:flex; flex-direction:column; }
  .project-card::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; background:linear-gradient(90deg,var(--amber),transparent); transform:scaleX(0); transform-origin:left; transition:transform 0.3s; }
  .project-card:hover { border-color:var(--amber-dim); transform:translateY(-4px); box-shadow:0 12px 40px rgba(0,0,0,0.4); }
  .project-card:hover::before { transform:scaleX(1); }
  .card-corner { position:absolute; top:0; right:0; width:0; height:0; border-style:solid; border-width:0 22px 22px 0; border-color:transparent var(--amber-dim) transparent transparent; }
  .card-lang { display:inline-block; font-size:0.58rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--amber); background:var(--amber-glow); padding:0.15rem 0.5rem; margin-bottom:1rem; }
  .card-status { display:inline-block; font-size:0.55rem; letter-spacing:0.1em; text-transform:uppercase; padding:0.12rem 0.4rem; margin-left:0.4rem; }
  .card-year { display:inline-block; font-size:0.55rem; letter-spacing:0.08em; color:var(--muted); padding:0.12rem 0.4rem; margin-left:0.4rem; }
  .card-status.active  { color:var(--green); border:1px solid var(--green); }
  .card-status.wip     { color:var(--blue);  border:1px solid var(--blue);  }
  .card-status.archived{ color:var(--muted); border:1px solid var(--border);}
  .card-title { font-family:var(--display); font-size:1.1rem; font-weight:700; margin-bottom:0.6rem; }
  .card-desc  { font-size:0.77rem; color:var(--muted); line-height:1.7; margin-bottom:1.2rem; flex:1; }
  .card-tags  { display:flex; gap:0.4rem; flex-wrap:wrap; margin-bottom:1rem; }
  .tag { font-size:0.58rem; color:var(--muted); border:1px solid var(--border); padding:0.1rem 0.4rem; letter-spacing:0.05em; }
  .card-footer { display:flex; align-items:center; justify-content:space-between; border-top:1px solid var(--border); padding-top:0.8rem; }
  .gh-link { display:flex; align-items:center; gap:0.4rem; color:var(--muted); font-size:0.68rem; text-decoration:none; letter-spacing:0.06em; transition:color 0.2s; }
  .gh-link:hover { color:var(--amber); }
  .demo-link { color:var(--amber); font-size:0.68rem; text-decoration:none; letter-spacing:0.08em; text-transform:uppercase; transition:opacity 0.2s; }
  .demo-link:hover { opacity:0.7; }
  .card-image-wrap { position:relative; margin:-1.5rem -1.5rem 1rem; width:calc(100% + 3rem); }
  .card-image-wrap img { width:100%; height:160px; object-fit:cover; display:block; border-bottom:1px solid var(--border); filter:brightness(0.85); transition:filter 0.3s; }
  .project-card:hover .card-image-wrap img { filter:brightness(1); }
  .card-image-count { position:absolute; bottom:6px; right:8px; background:rgba(0,0,0,0.72); color:var(--amber); font-size:0.58rem; letter-spacing:0.1em; padding:0.15rem 0.45rem; font-family:var(--mono); pointer-events:none; }
  .empty-state { grid-column:1/-1; text-align:center; padding:4rem 2rem; color:var(--muted); font-size:0.8rem; border:1px dashed var(--border); }

  /* CONTACT */
  #contact { background:var(--bg); }
  .contact-box { background:var(--bg2); border:1px solid var(--border); padding:3rem; max-width:560px; }
  .contact-row { display:flex; align-items:center; gap:1rem; margin-bottom:1.4rem; }
  .c-icon { color:var(--amber); width:20px; text-align:center; }
  .c-key { font-size:0.65rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.1em; min-width:70px; }
  .c-val { font-size:0.83rem; color:var(--text); text-decoration:none; transition:color 0.2s; }
  a.c-val:hover { color:var(--amber); }

  /* FOOTER */
  footer { border-top:1px solid var(--border); padding:2rem; text-align:center; font-size:0.68rem; color:var(--muted); }
  footer a { color:var(--amber); text-decoration:none; }

  /* Loading skeleton */
  .skeleton { background:linear-gradient(90deg,var(--bg3) 25%,var(--border) 50%,var(--bg3) 75%); background-size:200% 100%; animation:shimmer 1.5s infinite; border-radius:2px; }
  @keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

  /* PROJECT CARD — clickable */
  .project-card { cursor:pointer; }

  /* PROJECT DETAIL MODAL */
  .proj-detail-overlay { display:none; position:fixed; inset:0; z-index:200; background:rgba(0,0,0,0.88); backdrop-filter:blur(6px); align-items:flex-start; justify-content:center; padding:2rem 1rem; overflow-y:auto; }
  .proj-detail-overlay.open { display:flex; }
  .proj-detail { background:var(--bg2); border:1px solid var(--amber-dim); width:100%; max-width:800px; position:relative; animation:fadeUp 0.25s ease both; margin:auto; }
  .proj-detail-close { position:absolute; top:0.7rem; right:0.7rem; z-index:10; background:rgba(0,0,0,0.65); border:1px solid var(--border); color:var(--muted); font-family:var(--mono); font-size:0.85rem; width:30px; height:30px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; }
  .proj-detail-close:hover { border-color:var(--red); color:var(--red); }

  /* CAROUSEL */
  .proj-carousel { background:#000; }
  .proj-carousel.no-images { display:none; }
  .carousel-img-wrap { position:relative; width:100%; line-height:0; }
  .carousel-img-wrap img { width:100%; height:400px; object-fit:contain; background:#000; display:block; }
  .carousel-btn { position:absolute; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.55); border:1px solid rgba(255,255,255,0.15); color:var(--text); font-family:var(--mono); font-size:1.5rem; width:44px; height:44px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; z-index:2; line-height:1; }
  .carousel-btn:hover { border-color:var(--amber); color:var(--amber); background:rgba(0,0,0,0.8); }
  .carousel-btn.prev { left:0.7rem; }
  .carousel-btn.next { right:0.7rem; }
  .carousel-btn.hidden { display:none; }
  .carousel-bar { display:flex; align-items:center; justify-content:center; gap:1rem; padding:0.5rem 1rem; background:var(--bg3); border-top:1px solid var(--border); min-height:2rem; }
  .carousel-dots { display:flex; gap:0.45rem; }
  .carousel-counter { font-size:0.58rem; color:var(--muted); letter-spacing:0.12em; white-space:nowrap; }
  .carousel-dot { width:7px; height:7px; border-radius:50%; background:rgba(255,255,255,0.3); cursor:pointer; transition:background 0.2s; border:none; padding:0; flex-shrink:0; }
  .carousel-dot.active { background:var(--amber); }

  /* DETAIL BODY */
  .proj-detail-body { padding:1.5rem 2rem 2rem; }
  .proj-detail-meta { margin-bottom:0.8rem; display:flex; align-items:center; gap:0.4rem; }
  .proj-detail-title { font-family:var(--display); font-size:1.5rem; font-weight:700; margin-bottom:1rem; line-height:1.2; }
  .proj-detail-desc { font-size:0.82rem; color:var(--muted); line-height:1.85; margin-bottom:1.2rem; white-space:pre-wrap; }
  .proj-detail-desc strong { color: var(--text); font-weight: 600; }
  .proj-detail-desc a { color: var(--amber); text-decoration: none; }
  .proj-detail-desc h3 { color: var(--amber); font-size: 0.88rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; margin: 1.2rem 0 0.4rem; font-family: var(--mono); }
  .proj-detail-desc h4 { color: var(--text); font-size: 0.84rem; font-weight: 600; margin: 0.9rem 0 0.3rem; font-family: var(--mono); }

  /* Code formatting — applies anywhere renderDesc output appears */
  code { font-family: var(--mono); font-size: 0.9em; background: var(--bg3); border: 1px solid var(--border); padding: 0.05em 0.35em; color: var(--text); border-radius: 2px; }
  pre  { font-family: var(--mono); background: var(--bg3); border: 1px solid var(--border); padding: 0.8rem 1rem; margin: 0.8rem 0; overflow-x: auto; white-space: pre; line-height: 1.55; }
  pre code { background: none; border: none; padding: 0; font-size: 0.78rem; color: var(--text); }
  .proj-detail-desc pre, .about-text pre, .card-desc pre { white-space: pre; }
  .proj-detail-links { display:flex; gap:1rem; flex-wrap:wrap; margin-top:1.2rem; padding-top:1.2rem; border-top:1px solid var(--border); align-items:center; }
  @media(max-width:600px) { .carousel-img-wrap img { height:240px; } .proj-detail-body { padding:1rem 1.2rem 1.5rem; } }

</style>
</head>
<body>

<nav>
  <a href="#hero" class="nav-logo" id="nav-logo">dev.portfolio</a>
  <button class="nav-hamburger" id="nav-hamburger" aria-label="Toggle menu" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>
  <div class="nav-links" id="nav-links">
    <a href="#about">About</a>
    <a href="#projects">Projects</a>
    <a href="#contact">Contact</a>
    <a id="nav-github" href="#" target="_blank" rel="noopener">GitHub</a>
  </div>
</nav>

<section id="hero">
  <div class="hero-grid"></div>
  <div class="hero-inner">
    <div class="hero-tag">Available for work &nbsp;●&nbsp; Open to collaborations</div>
    <h1 class="hero-name"><span id="hero-name">Loading</span><br><span class="accent" id="hero-name2">...</span></h1>
    <p class="hero-sub"><span class="typed" id="typed-role"></span><span class="cursor" aria-hidden="true"></span></p>
    <p class="hero-prompt"><span class="pc">$</span><span id="hero-tagline">Loading portfolio data...</span></p>
    <div class="hero-ctas">
      <a href="#projects" class="btn-primary">View Projects →</a>
      <a id="cta-github" href="#" target="_blank" rel="noopener" class="btn-secondary">GitHub Profile</a>
      <a id="cta-resume" href="#" download="resume.pdf" class="btn-secondary" style="display:none">⬇ Resume</a>
    </div>
    <div class="hero-stats">
      <div><div class="stat-num" id="stat-projects">—</div><div class="stat-label">Projects Shipped</div></div>
      <div><div class="stat-num" id="stat-years">—</div><div class="stat-label">Years Experience</div></div>
    </div>
  </div>
</section>

<section id="about"><div class="section-inner">
  <div class="section-header"><span class="section-num">01</span><h2 class="section-title">About</h2><div class="section-line"></div></div>
  <div class="about-grid">
    <div class="about-text" id="about-text"><p>Loading...</p></div>
    <div class="skills-block" id="skills-block"></div>
  </div>
</div></section>

<section id="projects"><div class="section-inner">
  <div class="section-header"><span class="section-num">02</span><h2 class="section-title">Projects</h2><div class="section-line"></div></div>
  <div class="projects-grid" id="projects-grid">
    <div class="empty-state">Loading projects...</div>
  </div>
</div></section>

<section id="contact"><div class="section-inner">
  <div class="section-header"><span class="section-num">03</span><h2 class="section-title">Contact</h2><div class="section-line"></div></div>
  <div class="contact-box">
    <div class="contact-row"><span class="c-icon" aria-hidden="true">✉</span><span class="c-key">email</span><a id="c-email" href="#" class="c-val">—</a></div>
    <div class="contact-row"><span class="c-icon" aria-hidden="true">⌥</span><span class="c-key">github</span><a id="c-github" href="#" target="_blank" rel="noopener" class="c-val">—</a></div>
    <div class="contact-row"><span class="c-icon" aria-hidden="true">◈</span><span class="c-key">linkedin</span><a id="c-linkedin" href="#" target="_blank" rel="noopener" class="c-val">—</a></div>
    <div class="contact-row"><span class="c-icon" aria-hidden="true">◉</span><span class="c-key">location</span><span id="c-location" class="c-val">—</span></div>
    <div style="margin-top:2rem"><a id="c-email-btn" href="#" class="btn-primary">Send a Message →</a></div>
  </div>
</div></section>

<footer>
  <p>PHP · MySQL · Vanilla JS · CSS3 &nbsp;|&nbsp; <span id="footer-name">Portfolio</span> © <?= date('Y') ?> &nbsp;|&nbsp; <a id="footer-github" href="#" rel="noopener">GitHub</a></p>
</footer>

<!-- PROJECT DETAIL MODAL -->
<div class="proj-detail-overlay" id="proj-detail-overlay" role="dialog" aria-modal="true" aria-labelledby="pd-title">
  <div class="proj-detail">
    <button class="proj-detail-close" id="proj-detail-close" type="button" aria-label="Close project details">✕</button>
    <div class="proj-carousel" id="proj-carousel">
      <div class="carousel-img-wrap">
        <img id="carousel-img" src="" alt="">
        <button class="carousel-btn prev hidden" id="carousel-prev" type="button" aria-label="Previous image">&#8249;</button>
        <button class="carousel-btn next hidden" id="carousel-next" type="button" aria-label="Next image">&#8250;</button>
      </div>
      <div class="carousel-bar">
        <div class="carousel-dots" id="carousel-dots" role="tablist" aria-label="Project image slides"></div>
        <div class="carousel-counter" id="carousel-counter" aria-live="polite"></div>
      </div>
    </div>
    <div class="proj-detail-body">
      <div class="proj-detail-meta">
        <span class="card-lang" id="pd-lang"></span>
        <span class="card-status" id="pd-status"></span>
        <span class="card-year" id="pd-year" style="display:none"></span>
      </div>
      <h2 class="proj-detail-title" id="pd-title"></h2>
      <div class="proj-detail-desc" id="pd-desc"></div>
      <div class="card-tags" id="pd-tags"></div>
      <div class="proj-detail-links" id="pd-links"></div>
    </div>
  </div>
</div>

<script>
const API = '/api';
let siteSettings = {};
let allProjectsData = [];
let currentProject = null;

// ── Network: small wrapper that surfaces parse errors instead of swallowing
async function apiFetch(path) {
  const res = await fetch(`${API}${path}`);
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  return await res.json();
}

// ── Init ────────────────────────────────────────────────────
async function init() {
  const [settings, projects, skillGroups] = await Promise.all([
    apiFetch('/settings/').catch(err => { console.error('settings load failed:', err); return {}; }),
    apiFetch('/projects/').catch(err => { console.error('projects load failed:', err); return []; }),
    apiFetch('/skills/').catch(err   => { console.error('skills load failed:',   err); return []; }),
  ]);
  siteSettings    = settings;
  allProjectsData = projects;
  applySettings(settings, projects.length);
  renderProjects(projects);
  renderSkills(skillGroups);
}

function renderSkills(groups) {
  const el = document.getElementById('skills-block');
  if (!groups.length) { el.innerHTML = ''; return; }
  el.innerHTML = groups.map(g => `
    <div>
      <div class="skill-group-label">${esc(g.label)}</div>
      <div class="skill-tags">${(g.skills||[]).map(s=>`<span class="skill-tag">${esc(s)}</span>`).join('')}</div>
    </div>
  `).join('');
}

function applySettings(s, projectCount) {
  const name = s.name || 'Developer';
  const parts = name.split(' ');
  const first = parts[0];
  const rest  = parts.slice(1).join(' ');
  document.getElementById('hero-name').textContent  = first;
  document.getElementById('hero-name2').textContent = rest || first;
  document.title = `${name} — Portfolio`;
  document.getElementById('nav-logo').textContent   = (s.name || 'portfolio').toLowerCase().replace(' ','.');
  document.getElementById('hero-tagline').textContent = s.tagline || '';
  document.getElementById('stat-projects').textContent = projectCount;
  document.getElementById('stat-years').textContent    = s.years_exp || '—';

  const gh = s.github || '#';
  document.getElementById('nav-github').href  = gh;
  document.getElementById('cta-github').href  = gh;
  document.getElementById('c-github').href    = gh;
  document.getElementById('c-github').textContent = gh.replace(/^https?:\/\//, '');
  document.getElementById('footer-github').href   = gh;
  document.getElementById('footer-name').textContent = name;

  const email = s.email || '';
  document.getElementById('c-email').href = `mailto:${email}`;
  document.getElementById('c-email').textContent = email;
  document.getElementById('c-email-btn').href = `mailto:${email}`;

  const li = s.linkedin || '';
  document.getElementById('c-linkedin').href = li || '#';
  document.getElementById('c-linkedin').textContent = li ? li.replace(/^https?:\/\//, '') : '—';

  document.getElementById('c-location').textContent = s.location || '—';

  const bioEl = document.getElementById('about-text');
  const bioParagraphs = (s.bio || '').split(/\n\n+/).filter(Boolean).map(p => `<p>${renderDesc(p)}</p>`).join('');
  bioEl.innerHTML = (bioParagraphs || '<p></p>') + '<div style="margin-top:1.5rem"><a href="#contact" class="btn-secondary">Get in Touch →</a></div>';

  const resumeBtn = document.getElementById('cta-resume');
  if (s.resume_url) {
    resumeBtn.href = s.resume_url;
    resumeBtn.style.display = '';
  } else {
    resumeBtn.style.display = 'none';
  }

  const tickerItems = s.ticker_items
    ? s.ticker_items.split('\n').map(t => t.trim()).filter(Boolean)
    : [s.role || 'Developer'];
  startTyped(tickerItems);
}

function renderProjects(projects) {
  const grid = document.getElementById('projects-grid');
  if (!projects.length) {
    grid.innerHTML = '<div class="empty-state">No projects yet.</div>';
    return;
  }
  grid.innerHTML = projects.map((p, i) => {
    const summaryImg = p.summary_image || (p.images && p.images[0]);
    const shortDesc  = p.short_description || p.description;
    return `
    <div class="project-card" style="animation-delay:${i*0.07}s" onclick="openProjectDetail(${p.id})" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openProjectDetail(${p.id});}">
      <div class="card-corner" aria-hidden="true"></div>
      ${summaryImg ? `
        <div class="card-image-wrap">
          <img src="${esc(summaryImg)}" alt="${esc(p.title)} preview" loading="lazy">
          ${p.images && p.images.length > 1 ? `<span class="card-image-count">+${p.images.length - 1} more</span>` : ''}
        </div>` : ''}
      <div>
        <span class="card-lang">${esc(p.language)}</span>
        <span class="card-status ${esc(p.status)}">${esc(p.status)}</span>
        ${p.year ? `<span class="card-year">${esc(String(p.year))}</span>` : ''}
      </div>
      <div class="card-title">${esc(p.title)}</div>
      <div class="card-desc">${renderDesc(shortDesc)}</div>
      <div class="card-tags">${(p.tags||[]).map(t=>`<span class="tag">${esc(t)}</span>`).join('')}</div>
      <div class="card-footer" onclick="event.stopPropagation()">
        ${p.github_url ? `<a href="${esc(p.github_url)}" target="_blank" rel="noopener" class="gh-link">⌥ GitHub →</a>` : '<span></span>'}
        ${p.demo_url   ? `<a href="${esc(p.demo_url)}"   target="_blank" rel="noopener" class="demo-link">Live Demo →</a>` : ''}
      </div>
    </div>
  `}).join('');
}

// ── Typed effect ─────────────────────────────────────────────
function startTyped(roles) {
  if (!roles.length) roles = ['Developer'];
  let ri = 0, ci = 0, del = false;
  const el = document.getElementById('typed-role');
  function tick() {
    const cur = roles[ri];
    if (!del) { el.textContent = cur.slice(0, ++ci); if (ci===cur.length){del=true;setTimeout(tick,1800);return;} }
    else      { el.textContent = cur.slice(0, --ci); if (ci===0){del=false;ri=(ri+1)%roles.length;} }
    setTimeout(tick, del ? 42 : 80);
  }
  tick();
}

function esc(s) {
  if (s === null || s === undefined) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Render a small markdown-like subset to HTML. All input is escaped first
// so raw HTML is impossible. Code blocks and inline code are stashed
// behind placeholders before any other rule runs, so markdown characters
// inside code (like * or [) survive untouched.
function renderDesc(text) {
  if (!text) return '';
  let h = esc(text);

  // Stash each protected region so subsequent rules can't see it.
  const stash = [];
  const place = (html) => {
    stash.push(html);
    return `@@CODE${stash.length - 1}@@`;
  };

  // Fenced code blocks: ```optional-lang\n...\n```
  h = h.replace(/```([a-zA-Z0-9_-]*)\n?([\s\S]*?)```/g, (_, lang, body) => {
    body = body.replace(/\n+$/, '');
    const cls = lang ? ` class="lang-${lang}"` : '';
    return place(`<pre><code${cls}>${body}</code></pre>`);
  });
  // Double-backtick inline code: ``…`` — allows literal ` inside (GitHub-style).
  // Run before single-backtick so the doubles win. Optional single space at
  // either end is stripped, matching the common `` `code` `` idiom.
  h = h.replace(/``\s?(.+?)\s?``/g, (_, body) => place(`<code>${body}</code>`));
  // Inline code: `…` (single line, no backticks inside)
  h = h.replace(/`([^`\n]+)`/g, (_, body) => place(`<code>${body}</code>`));

  // Links [text](url) — before bold so brackets/parens don't collide
  h = h.replace(/\[([^\]\n]+)\]\(([^)\s]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
  // Bold-italic ***x*** before bold/italic to win the longest match
  h = h.replace(/\*\*\*([^*\n]+?)\*\*\*/g, '<strong><em>$1</em></strong>');
  // Bold **x**
  h = h.replace(/\*\*([^*\n]+?)\*\*/g,     '<strong>$1</strong>');
  // Italic *x* — lookarounds avoid matching inside already-rendered <strong>
  h = h.replace(/(^|[^*])\*([^*\n]+?)\*(?!\*)/g, '$1<em>$2</em>');
  // Headings (line-anchored)
  h = h.replace(/^## (.+)$/gm, '<h4>$1</h4>');
  h = h.replace(/^# (.+)$/gm,  '<h3>$1</h3>');

  // Restore the stashed code regions.
  h = h.replace(/@@CODE(\d+)@@/g, (_, i) => stash[parseInt(i, 10)]);
  return h;
}

// ── Project detail modal & carousel ─────────────────────────
let carouselImages = [];
let carouselIdx    = 0;
let lastFocusedBeforeModal = null;

function openProjectDetail(id) {
  const p = allProjectsData.find(x => x.id === id);
  if (!p) return;
  currentProject = p;

  carouselImages = p.images || [];
  carouselIdx    = 0;
  const carousel = document.getElementById('proj-carousel');
  if (carouselImages.length) {
    carousel.classList.remove('no-images');
    updateCarousel();
  } else {
    carousel.classList.add('no-images');
  }

  document.getElementById('pd-lang').textContent  = p.language;
  const statusEl = document.getElementById('pd-status');
  statusEl.textContent = p.status;
  statusEl.className   = `card-status ${p.status}`;
  const yearEl = document.getElementById('pd-year');
  if (p.year) { yearEl.textContent = p.year; yearEl.style.display = ''; }
  else yearEl.style.display = 'none';
  document.getElementById('pd-title').textContent = p.title;
  document.getElementById('pd-desc').innerHTML    = renderDesc(p.description);
  document.getElementById('pd-tags').innerHTML    = (p.tags||[]).map(t=>`<span class="tag">${esc(t)}</span>`).join('');

  const links = [];
  if (p.github_url) links.push(`<a href="${esc(p.github_url)}" target="_blank" rel="noopener" class="gh-link">⌥ GitHub →</a>`);
  if (p.demo_url)   links.push(`<a href="${esc(p.demo_url)}"   target="_blank" rel="noopener" class="demo-link">Live Demo →</a>`);
  document.getElementById('pd-links').innerHTML = links.join('');

  lastFocusedBeforeModal = document.activeElement;
  document.getElementById('proj-detail-overlay').classList.add('open');
  document.body.style.overflow = 'hidden';
  setTimeout(() => document.getElementById('proj-detail-close').focus(), 30);
}

function closeProjDetail() {
  document.getElementById('proj-detail-overlay').classList.remove('open');
  document.body.style.overflow = '';
  if (lastFocusedBeforeModal && typeof lastFocusedBeforeModal.focus === 'function') {
    lastFocusedBeforeModal.focus();
  }
}

function updateCarousel() {
  const img = document.getElementById('carousel-img');
  img.src = carouselImages[carouselIdx] || '';
  img.alt = currentProject ? `${currentProject.title} screenshot ${carouselIdx + 1} of ${carouselImages.length}` : '';
  const single = carouselImages.length <= 1;
  document.getElementById('carousel-prev').classList.toggle('hidden', single);
  document.getElementById('carousel-next').classList.toggle('hidden', single);
  document.getElementById('carousel-counter').textContent = single ? '' : `${carouselIdx + 1} / ${carouselImages.length}`;
  document.getElementById('carousel-dots').innerHTML = single ? '' :
    carouselImages.map((_, i) =>
      `<button class="carousel-dot${i===carouselIdx?' active':''}" type="button" role="tab" aria-label="Slide ${i+1} of ${carouselImages.length}" aria-selected="${i===carouselIdx}" onclick="goCarousel(${i})"></button>`
    ).join('');
}

function carouselPrev() { carouselIdx = (carouselIdx - 1 + carouselImages.length) % carouselImages.length; updateCarousel(); }
function carouselNext() { carouselIdx = (carouselIdx + 1) % carouselImages.length; updateCarousel(); }
function goCarousel(i)  { carouselIdx = i; updateCarousel(); }

document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('proj-detail-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeProjDetail();
  });
  document.getElementById('proj-detail-close').addEventListener('click', closeProjDetail);
  document.getElementById('carousel-prev').addEventListener('click', carouselPrev);
  document.getElementById('carousel-next').addEventListener('click', carouselNext);
  document.addEventListener('keydown', function(e) {
    if (!document.getElementById('proj-detail-overlay').classList.contains('open')) return;
    if (e.key === 'Escape')      closeProjDetail();
    if (e.key === 'ArrowLeft')   carouselPrev();
    if (e.key === 'ArrowRight')  carouselNext();
  });
});

init();

// Hamburger menu
(function() {
  const btn = document.getElementById('nav-hamburger');
  const links = document.getElementById('nav-links');
  btn.addEventListener('click', function() {
    const open = links.classList.toggle('open');
    btn.classList.toggle('open', open);
    btn.setAttribute('aria-expanded', open);
  });
  links.querySelectorAll('a').forEach(function(a) {
    a.addEventListener('click', function() {
      links.classList.remove('open');
      btn.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    });
  });
})();
</script>
</body>
</html>
