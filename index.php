<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portfolio</title>
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
  .skills-block { display:flex; flex-direction:column; gap:1rem; }
  .skill-row { display:flex; align-items:center; gap:1rem; }
  .skill-name { font-size:0.7rem; color:var(--muted); width:90px; flex-shrink:0; text-transform:uppercase; letter-spacing:0.08em; }
  .skill-bar { flex:1; height:4px; background:var(--border); overflow:hidden; }
  .skill-fill { height:100%; background:var(--amber); transform-origin:left; animation:growBar 1.2s ease both; }
  @keyframes growBar{from{transform:scaleX(0)}}
  .skill-pct { font-size:0.62rem; color:var(--muted); width:30px; text-align:right; }

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
</style>
</head>
<body>

<nav>
  <a href="#hero" class="nav-logo" id="nav-logo">dev.portfolio</a>
  <div class="nav-links">
    <a href="#about">About</a>
    <a href="#projects">Projects</a>
    <a href="#contact">Contact</a>
    <a id="nav-github" href="#" target="_blank">GitHub</a>
    <a href="/admin/" class="nav-admin">⚙ Admin</a>
  </div>
</nav>

<section id="hero">
  <div class="hero-grid"></div>
  <div class="hero-inner">
    <div class="hero-tag">Available for work &nbsp;●&nbsp; Open to collaborations</div>
    <h1 class="hero-name"><span id="hero-name">Loading</span><br><span class="accent" id="hero-name2">...</span></h1>
    <p class="hero-sub"><span class="typed" id="typed-role"></span><span class="cursor"></span></p>
    <p class="hero-prompt"><span class="pc">$</span><span id="hero-tagline">Loading portfolio data...</span></p>
    <div class="hero-ctas">
      <a href="#projects" class="btn-primary">View Projects →</a>
      <a id="cta-github" href="#" target="_blank" class="btn-secondary">GitHub Profile</a>
    </div>
    <div class="hero-stats">
      <div><div class="stat-num" id="stat-projects">—</div><div class="stat-label">Projects Shipped</div></div>
      <div><div class="stat-num" id="stat-years">—</div><div class="stat-label">Years Experience</div></div>
      <div><div class="stat-num">∞</div><div class="stat-label">Problems Solved</div></div>
    </div>
  </div>
</section>

<section id="about"><div class="section-inner">
  <div class="section-header"><span class="section-num">01</span><h2 class="section-title">About</h2><div class="section-line"></div></div>
  <div class="about-grid">
    <div class="about-text" id="about-text"><p>Loading...</p></div>
    <div class="skills-block">
      <div class="skill-row"><span class="skill-name">PHP</span><div class="skill-bar"><div class="skill-fill" style="width:90%"></div></div><span class="skill-pct">90%</span></div>
      <div class="skill-row"><span class="skill-name">SQL/MySQL</span><div class="skill-bar"><div class="skill-fill" style="width:85%"></div></div><span class="skill-pct">85%</span></div>
      <div class="skill-row"><span class="skill-name">Javascript</span><div class="skill-bar"><div class="skill-fill" style="width:85%"></div></div><span class="skill-pct">85%</span></div>
      <div class="skill-row"><span class="skill-name">WordPress</span><div class="skill-bar"><div class="skill-fill" style="width:88%"></div></div><span class="skill-pct">88%</span></div>
      <div class="skill-row"><span class="skill-name">CSS</span><div class="skill-bar"><div class="skill-fill" style="width:80%"></div></div><span class="skill-pct">80%</span></div>
      <div class="skill-row"><span class="skill-name">Python</span><div class="skill-bar"><div class="skill-fill" style="width:65%"></div></div><span class="skill-pct">65%</span></div>
      <div class="skill-row"><span class="skill-name">C#/.NET</span><div class="skill-bar"><div class="skill-fill" style="width:70%"></div></div><span class="skill-pct">70%</span></div>
      <div class="skill-row"><span class="skill-name">C++</span><div class="skill-bar"><div class="skill-fill" style="width:60%"></div></div><span class="skill-pct">60%</span></div>
      <div class="skill-row"><span class="skill-name">Cybersecurity</span><div class="skill-bar"><div class="skill-fill" style="width:68%"></div></div><span class="skill-pct">68%</span></div>
      <div class="skill-row"><span class="skill-name">Java</span><div class="skill-bar"><div class="skill-fill" style="width:60%"></div></div><span class="skill-pct">60%</span></div>
    </div>
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
    <div class="contact-row"><span class="c-icon">✉</span><span class="c-key">email</span><a id="c-email" href="#" class="c-val">—</a></div>
    <div class="contact-row"><span class="c-icon">⌥</span><span class="c-key">github</span><a id="c-github" href="#" target="_blank" class="c-val">—</a></div>
    <div class="contact-row"><span class="c-icon">◈</span><span class="c-key">linkedin</span><a id="c-linkedin" href="#" target="_blank" class="c-val">—</a></div>
    <div class="contact-row"><span class="c-icon">◉</span><span class="c-key">location</span><span id="c-location" class="c-val">—</span></div>
    <div style="margin-top:2rem"><a id="c-email-btn" href="#" class="btn-primary">Send a Message →</a></div>
  </div>
</div></section>

<footer>
  <p>Built with precision — <span id="footer-name">Portfolio</span> © <?= date('Y') ?> &nbsp;|&nbsp; <a id="footer-github" href="#">GitHub</a> &nbsp;|&nbsp; <a href="/admin/">Admin</a></p>
</footer>

<script>
const API = '/api';
let siteSettings = {};

// ── Load settings & projects ────────────────────────────────
async function init() {
  const [settings, projects] = await Promise.all([
    fetch(`${API}/settings/`).then(r => r.json()).catch(() => ({})),
    fetch(`${API}/projects/`).then(r => r.json()).catch(() => []),
  ]);
  siteSettings = settings;
  applySettings(settings, projects.length);
  renderProjects(projects);
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
  document.getElementById('c-github').textContent = gh.replace('https://','');
  document.getElementById('footer-github').href   = gh;
  document.getElementById('footer-name').textContent = name;

  const email = s.email || '';
  document.getElementById('c-email').href = `mailto:${email}`;
  document.getElementById('c-email').textContent = email;
  document.getElementById('c-email-btn').href = `mailto:${email}`;

  const li = s.linkedin || '';
  document.getElementById('c-linkedin').href = li || '#';
  document.getElementById('c-linkedin').textContent = li ? li.replace('https://','') : '—';

  document.getElementById('c-location').textContent = s.location || '—';

  const bioEl = document.getElementById('about-text');
  bioEl.innerHTML = `<p>${esc(s.bio || '')}</p><div style="margin-top:1.5rem"><a href="#contact" class="btn-secondary">Get in Touch →</a></div>`;

  startTyped(s.role || 'Developer');
}

function renderProjects(projects) {
  const grid = document.getElementById('projects-grid');
  if (!projects.length) {
    grid.innerHTML = '<div class="empty-state">No projects yet.</div>';
    return;
  }
  grid.innerHTML = projects.map((p, i) => `
    <div class="project-card" style="animation-delay:${i*0.07}s">
      <div class="card-corner"></div>
      <div>
        <span class="card-lang">${esc(p.language)}</span>
        <span class="card-status ${esc(p.status)}">${esc(p.status)}</span>
      </div>
      <div class="card-title">${esc(p.title)}</div>
      <div class="card-desc">${esc(p.description)}</div>
      <div class="card-tags">${(p.tags||[]).map(t=>`<span class="tag">${esc(t)}</span>`).join('')}</div>
      <div class="card-footer">
        ${p.github_url ? `<a href="${esc(p.github_url)}" target="_blank" class="gh-link">⌥ GitHub →</a>` : '<span></span>'}
        ${p.demo_url   ? `<a href="${esc(p.demo_url)}"   target="_blank" class="demo-link">Live Demo →</a>` : ''}
      </div>
    </div>
  `).join('');
}

// ── Typed effect ─────────────────────────────────────────────
function startTyped(baseRole) {
  const roles = [baseRole, 'WordPress Developer', 'Cybersecurity Enthusiast', 'Ceramics Instructor', 'Open Source Builder'];
  let ri = 0, ci = 0, del = false;
  const el = document.getElementById('typed-role');
  function tick() {
    const cur = roles[ri];
    if (!del) { el.textContent = cur.slice(0, ++ci); if (ci===cur.length){del=true;setTimeout(tick,1800);return;} }
    else       { el.textContent = cur.slice(0, --ci); if (ci===0){del=false;ri=(ri+1)%roles.length;} }
    setTimeout(tick, del ? 42 : 80);
  }
  tick();
}

function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

init();
</script>
</body>
</html>
