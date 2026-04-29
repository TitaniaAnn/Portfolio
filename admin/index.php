<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$admin = current_admin();
if (!$admin) {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="icon" href="/favicon.ico">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:#0d0d0d; --bg2:#141414; --bg3:#1a1a1a; --border:#2a2a2a;
    --amber:#f59e0b; --amber-dim:#b45309; --amber-glow:rgba(245,158,11,0.12);
    --text:#e8e0d0; --muted:#555;
    --green:#4ade80; --red:#f87171; --blue:#60a5fa;
    --mono:'JetBrains Mono',monospace; --display:'Syne',sans-serif;
  }
  *{margin:0;padding:0;box-sizing:border-box}
  body{background:var(--bg);color:var(--text);font-family:var(--mono);min-height:100vh}
  body::before{content:'';position:fixed;inset:0;pointer-events:none;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,0.02) 2px,rgba(0,0,0,0.02) 4px)}

  /* TOPBAR */
  .topbar{position:fixed;top:0;left:0;right:0;z-index:100;height:52px;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;background:rgba(13,13,13,0.96);backdrop-filter:blur(12px);border-bottom:1px solid var(--border)}
  .tb-left{display:flex;align-items:center;gap:1rem}
  .tb-logo{font-family:var(--display);font-size:1rem;font-weight:800;color:var(--amber)}
  .tb-breadcrumb{font-size:0.65rem;color:var(--muted);letter-spacing:0.1em}
  .tb-right{display:flex;align-items:center;gap:1rem}
  .tb-user{display:flex;align-items:center;gap:0.6rem;font-size:0.72rem;color:var(--muted)}
  .tb-avatar{width:26px;height:26px;border-radius:50%;border:1px solid var(--border);object-fit:cover}
  .btn-sm{background:transparent;border:1px solid var(--border);color:var(--muted);font-family:var(--mono);font-size:0.62rem;letter-spacing:0.1em;padding:0.3rem 0.7rem;cursor:pointer;text-transform:uppercase;text-decoration:none;transition:all 0.2s}
  .btn-sm:hover{border-color:var(--amber);color:var(--amber)}

  /* LAYOUT */
  .layout{display:flex;min-height:100vh;padding-top:52px}
  .sidebar{width:220px;flex-shrink:0;background:var(--bg2);border-right:1px solid var(--border);padding:1.5rem 0;position:fixed;top:52px;bottom:0;overflow-y:auto}
  .main{flex:1;padding:2rem 2rem 4rem;margin-left:220px}
  @media(max-width:700px){.sidebar{display:none}.main{margin-left:0}}

  .nav-item{display:flex;align-items:center;gap:0.6rem;padding:0.65rem 1.2rem;font-size:0.72rem;letter-spacing:0.06em;color:var(--muted);text-decoration:none;transition:all 0.2s;cursor:pointer;border:none;background:none;width:100%;text-align:left}
  .nav-item.active,.nav-item:hover{color:var(--amber);background:var(--amber-glow)}
  .nav-item .nav-icon{width:16px;text-align:center}
  .nav-section{font-size:0.55rem;letter-spacing:0.18em;text-transform:uppercase;color:var(--border);padding:1rem 1.2rem 0.3rem}

  /* PANELS */
  .panel{display:none}.panel.active{display:block}
  .panel-header{margin-bottom:2rem}
  .panel-title{font-family:var(--display);font-size:1.5rem;font-weight:700;margin-bottom:0.3rem}
  .panel-sub{font-size:0.72rem;color:var(--muted)}

  /* STATS ROW */
  .stats-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;margin-bottom:2rem}
  .stat-card{background:var(--bg2);border:1px solid var(--border);padding:1.2rem;position:relative;overflow:hidden}
  .stat-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--amber),transparent)}
  .stat-card-num{font-family:var(--display);font-size:1.8rem;font-weight:800;color:var(--amber)}
  .stat-card-label{font-size:0.63rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.1em;margin-top:0.3rem}

  /* FORMS */
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
  @media(max-width:600px){.form-grid{grid-template-columns:1fr}}
  .form-group{margin-bottom:1.2rem}
  .form-group.full{grid-column:1/-1}
  label{display:block;font-size:0.63rem;color:var(--muted);letter-spacing:0.12em;text-transform:uppercase;margin-bottom:0.4rem}
  label .req{color:var(--amber)}
  input,textarea,select{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);font-family:var(--mono);font-size:0.8rem;padding:0.6rem 0.8rem;outline:none;transition:border-color 0.2s;resize:vertical}
  input:focus,textarea:focus,select:focus{border-color:var(--amber-dim);box-shadow:0 0 0 2px var(--amber-glow)}
  select option{background:var(--bg2)}
  .hint{font-size:0.6rem;color:var(--muted);margin-top:0.3rem}
  .form-actions{display:flex;gap:0.8rem;align-items:center;margin-top:1rem}
  .btn-save{background:var(--amber);color:#0d0d0d;border:none;padding:0.7rem 1.6rem;font-family:var(--mono);font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;cursor:pointer;transition:all 0.2s}
  .btn-save:hover{background:#fbbf24}
  .btn-reset{background:transparent;border:1px solid var(--border);color:var(--muted);font-family:var(--mono);font-size:0.7rem;letter-spacing:0.08em;padding:0.7rem 1.2rem;cursor:pointer;text-transform:uppercase;transition:all 0.2s}
  .btn-reset:hover{border-color:var(--muted);color:var(--text)}

  /* PROJECT TABLE */
  .proj-table{width:100%;border-collapse:collapse;margin-top:0.5rem}
  .proj-table th{font-size:0.6rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);padding:0.5rem 0.8rem;border-bottom:1px solid var(--border);text-align:left;font-weight:400}
  .proj-table td{padding:0.9rem 0.8rem;border-bottom:1px solid var(--border);font-size:0.78rem;vertical-align:middle}
  .proj-table tr:hover td{background:var(--bg3)}
  .proj-lang-badge{display:inline-block;font-size:0.58rem;letter-spacing:0.1em;text-transform:uppercase;color:var(--amber);background:var(--amber-glow);padding:0.1rem 0.45rem}
  .status-badge{display:inline-block;font-size:0.56rem;letter-spacing:0.1em;text-transform:uppercase;padding:0.12rem 0.4rem}
  .status-badge.active{color:var(--green);border:1px solid var(--green)}
  .status-badge.wip{color:var(--blue);border:1px solid var(--blue)}
  .status-badge.archived{color:var(--muted);border:1px solid var(--border)}
  .tbl-actions{display:flex;gap:0.4rem}
  .tbl-btn{background:none;border:1px solid var(--border);color:var(--muted);font-family:var(--mono);font-size:0.6rem;letter-spacing:0.08em;padding:0.25rem 0.5rem;cursor:pointer;text-transform:uppercase;transition:all 0.2s}
  .tbl-btn:hover{border-color:var(--amber);color:var(--amber)}
  .tbl-btn.del:hover{border-color:var(--red);color:var(--red)}
  .empty-row td{text-align:center;color:var(--muted);padding:3rem}
  .drag-handle{cursor:grab;color:var(--border);font-size:1rem;padding:0.9rem 0.5rem;user-select:none;transition:color 0.2s}
  .drag-handle:hover{color:var(--muted)}
  .proj-table tr.dragging td{opacity:0.35}
  .proj-table tr.drag-over td{border-top:2px solid var(--amber)}

  /* TOAST */
  .toast{position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;background:var(--bg3);border:1px solid var(--green);color:var(--green);font-size:0.75rem;padding:0.7rem 1rem;pointer-events:none;animation:toastIn 0.25s ease}
  @keyframes toastIn{from{transform:translateY(6px);opacity:0}}
  .toast.err{border-color:var(--red);color:var(--red)}

  /* EDIT MODAL */
  .modal-overlay{display:none;position:fixed;inset:0;z-index:500;background:rgba(0,0,0,0.8);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:1rem}
  .modal-overlay.open{display:flex}
  .modal{background:var(--bg2);border:1px solid var(--amber-dim);width:100%;max-width:620px;max-height:90vh;overflow-y:auto}
  .modal-header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid var(--border)}
  .modal-title{font-family:var(--display);font-size:1rem;font-weight:700;color:var(--amber)}
  .modal-close{background:none;border:none;color:var(--muted);font-size:1.1rem;cursor:pointer;font-family:var(--mono);padding:0.2rem 0.4rem;transition:color 0.2s}
  .modal-close:hover{color:var(--red)}
  .modal-body{padding:1.5rem}

  /* IMAGE GALLERY */
  .img-gallery{display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.6rem;min-height:0}
  .img-thumb{position:relative;width:90px;height:70px;flex-shrink:0}
  .img-thumb img{width:100%;height:100%;object-fit:cover;border:1px solid var(--border);display:block}
  .img-thumb-del{position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.75);border:none;color:var(--red);font-size:0.6rem;cursor:pointer;width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-family:var(--mono);line-height:1}
  .img-thumb-del:hover{background:var(--red);color:#fff}
  .img-upload-area{border:1px dashed var(--border);padding:0.8rem;cursor:pointer;transition:border-color 0.2s;text-align:center;position:relative}
  .img-upload-area:hover{border-color:var(--amber-dim)}
  .img-upload-area input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
  .img-upload-label{font-size:0.65rem;color:var(--muted);letter-spacing:0.08em}
  .img-uploading{font-size:0.65rem;color:var(--amber);margin-top:0.4rem}
  .img-thumb-star{position:absolute;bottom:2px;right:2px;background:rgba(0,0,0,0.75);border:none;color:var(--muted);font-size:0.72rem;cursor:pointer;width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-family:var(--mono);line-height:1;transition:color 0.2s;padding:0}
  .img-thumb-star:hover{color:var(--amber)}
  .img-thumb.is-summary{outline:2px solid var(--amber)}
  .img-thumb.is-summary .img-thumb-star{color:var(--amber)}
  .img-thumb[draggable]{cursor:grab}
  .img-thumb.img-drag-over{outline:2px solid var(--amber);opacity:0.6}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="tb-left">
    <div class="tb-logo">⚙ Admin</div>
    <div class="tb-breadcrumb">/ dashboard</div>
  </div>
  <div class="tb-right">
    <div class="tb-user">
      <?php if ($admin['avatar_url']): ?>
      <img src="<?= htmlspecialchars($admin['avatar_url']) ?>" class="tb-avatar" alt="">
      <?php endif; ?>
      <span><?= htmlspecialchars($admin['name']) ?></span>
    </div>
    <a href="/" class="btn-sm">← Site</a>
    <a href="/api/auth/logout.php" class="btn-sm">Logout</a>
  </div>
</div>

<div class="layout">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="nav-section">Content</div>
    <button class="nav-item active" onclick="showPanel('projects')"><span class="nav-icon">◈</span> Projects</button>
    <button class="nav-item" onclick="showPanel('add-project')"><span class="nav-icon">＋</span> Add Project</button>
    <button class="nav-item" onclick="showPanel('skills')"><span class="nav-icon">◧</span> Skills</button>
    <div class="nav-section">Site</div>
    <button class="nav-item" onclick="showPanel('settings')"><span class="nav-icon">⚙</span> Settings</button>
    <button class="nav-item" onclick="showPanel('resume')"><span class="nav-icon">▤</span> Resume</button>
    <div class="nav-section">System</div>
    <a href="/admin/update.php" class="nav-item"><span class="nav-icon">⬆</span> DB Migrations</a>
    <div class="nav-section">Account</div>
    <button class="nav-item" onclick="showPanel('account')"><span class="nav-icon">◉</span> Account</button>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <!-- ── PROJECTS LIST ── -->
    <div class="panel active" id="panel-projects">
      <div class="panel-header">
        <div class="panel-title">Projects</div>
        <div class="panel-sub">Manage your portfolio projects</div>
      </div>
      <div class="stats-row">
        <div class="stat-card"><div class="stat-card-num" id="count-total">—</div><div class="stat-card-label">Total</div></div>
        <div class="stat-card"><div class="stat-card-num" id="count-active">—</div><div class="stat-card-label">Active</div></div>
        <div class="stat-card"><div class="stat-card-num" id="count-wip">—</div><div class="stat-card-label">WIP</div></div>
      </div>
      <table class="proj-table">
        <thead>
          <tr>
            <th></th>
            <th>Title</th>
            <th>Language</th>
            <th>Status</th>
            <th>Links</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="proj-tbody">
          <tr class="empty-row"><td colspan="6">Loading...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- ── ADD PROJECT ── -->
    <div class="panel" id="panel-add-project">
      <div class="panel-header">
        <div class="panel-title">Add Project</div>
        <div class="panel-sub">Add a new project to your portfolio</div>
      </div>
      <div class="form-grid">
        <div class="form-group full">
          <label>Title <span class="req">*</span></label>
          <input type="text" id="ap-title" placeholder="My Awesome Project">
        </div>
        <div class="form-group full">
          <label>Short Description <span class="req">*</span></label>
          <textarea id="ap-short-desc" rows="2" placeholder="1–2 sentence summary shown on the project card"></textarea>
        </div>
        <div class="form-group full">
          <label>Full Description <span class="req">*</span></label>
          <textarea id="ap-desc" rows="4" placeholder="Detailed description shown in the project detail view"></textarea>
          <div class="hint">Markdown: **bold** · *italic* · [text](url) · # Heading · ## Sub-heading</div>
        </div>
        <div class="form-group">
          <label>Primary Language <span class="req">*</span></label>
          <input type="text" id="ap-lang" list="lang-options" placeholder="e.g. JavaScript">
        </div>
        <div class="form-group">
          <label>Status</label>
          <select id="ap-status">
            <option value="active">Active</option>
            <option value="wip">WIP</option>
            <option value="archived">Archived</option>
          </select>
        </div>
        <div class="form-group">
          <label>GitHub URL</label>
          <input type="url" id="ap-github" placeholder="https://github.com/user/repo">
        </div>
        <div class="form-group">
          <label>Live Demo URL</label>
          <input type="url" id="ap-demo" placeholder="https://myproject.dev">
        </div>
        <div class="form-group">
          <label>Tags</label>
          <input type="text" id="ap-tags" placeholder="React, Node.js, PostgreSQL">
          <div class="hint">Comma-separated</div>
        </div>
        <div class="form-group">
          <label>Sort Order</label>
          <input type="text" id="ap-sort" placeholder="0" value="0">
          <div class="hint">Lower = appears first</div>
        </div>
        <div class="form-group">
          <label>Year Created</label>
          <input type="number" id="ap-year" placeholder="2024" min="1900" max="2099">
        </div>
        <div class="form-group full">
          <label>Project Images</label>
          <div class="img-gallery" id="ap-img-gallery"></div>
          <div class="img-upload-area">
            <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" multiple onchange="uploadImages(this,'ap')">
            <div class="img-upload-label">＋ Add images — jpg, png, gif, webp, max 5 MB each</div>
          </div>
          <div id="ap-img-status" class="img-uploading" style="display:none">Uploading...</div>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn-save" onclick="addProject()">＋ Add Project</button>
        <button class="btn-reset" onclick="resetAddForm()">Reset</button>
      </div>
    </div>

    <!-- ── SKILLS ── -->
    <div class="panel" id="panel-skills">
      <div class="panel-header">
        <div class="panel-title">Skills</div>
        <div class="panel-sub">Manage the grouped skill tags shown in the About section</div>
      </div>
      <div id="skill-groups-list" style="display:flex;flex-direction:column;gap:0.6rem;margin-bottom:2rem"></div>
      <div style="background:var(--bg2);border:1px solid var(--border);padding:1.2rem">
        <div style="font-size:0.63rem;color:var(--amber);letter-spacing:0.14em;text-transform:uppercase;margin-bottom:1rem">Add Group</div>
        <div class="form-grid">
          <div class="form-group"><label>Group Label <span class="req">*</span></label><input type="text" id="sg-label" placeholder="e.g. Mobile &amp; Frontend"></div>
          <div class="form-group"><label>Sort Order</label><input type="text" id="sg-sort" value="0"><div class="hint">Lower = appears first</div></div>
          <div class="form-group full"><label>Skills <span class="req">*</span></label><input type="text" id="sg-skills" placeholder="Flutter, Dart, React"><div class="hint">Comma-separated</div></div>
        </div>
        <div class="form-actions">
          <button class="btn-save" onclick="addSkillGroup()">＋ Add Group</button>
        </div>
      </div>
    </div>

    <!-- ── SETTINGS ── -->
    <div class="panel" id="panel-settings">
      <div class="panel-header">
        <div class="panel-title">Site Settings</div>
        <div class="panel-sub">Manage your portfolio content</div>
      </div>
      <div class="form-grid">
        <div class="form-group"><label>Your Name</label><input type="text" id="s-name" placeholder="Jane Smith"></div>
        <div class="form-group"><label>Role / Title</label><input type="text" id="s-role" placeholder="Full-Stack Developer"></div>
        <div class="form-group full"><label>Bio</label><textarea id="s-bio" rows="3" placeholder="A short paragraph about yourself..."></textarea></div>
        <div class="form-group"><label>Email</label><input type="text" id="s-email" placeholder="you@example.com"></div>
        <div class="form-group"><label>GitHub URL</label><input type="url" id="s-github" placeholder="https://github.com/username"></div>
        <div class="form-group"><label>LinkedIn URL</label><input type="url" id="s-linkedin" placeholder="https://linkedin.com/in/username"></div>
        <div class="form-group"><label>Location</label><input type="text" id="s-location" placeholder="San Francisco, CA"></div>
        <div class="form-group"><label>Tagline</label><input type="text" id="s-tagline" placeholder="Building great software, one commit at a time"></div>
        <div class="form-group"><label>Years of Experience</label><input type="text" id="s-years" placeholder="5+"></div>
        <div class="form-group full">
          <label>Ticker Items</label>
          <textarea id="s-ticker" rows="5" placeholder="Full-Stack Developer&#10;WordPress Developer&#10;Ceramics Instructor"></textarea>
          <div class="hint">One item per line. Cycles through these in the hero typed effect.</div>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn-save" onclick="saveSettings()">💾 Save Settings</button>
      </div>
    </div>

    <!-- ── RESUME ── -->
    <div class="panel" id="panel-resume">
      <div class="panel-header">
        <div class="panel-title">Resume</div>
        <div class="panel-sub">Upload a PDF resume for your portfolio</div>
      </div>
      <div id="resume-current" style="margin-bottom:1.5rem;display:none">
        <div style="background:var(--bg2);border:1px solid var(--border);padding:1rem;display:flex;align-items:center;justify-content:space-between;max-width:400px">
          <div>
            <div style="font-size:0.63rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.3rem">Current Resume</div>
            <a id="resume-link" href="" target="_blank" style="color:var(--amber);font-size:0.8rem;text-decoration:none">resume.pdf ↗</a>
          </div>
        </div>
      </div>
      <div style="max-width:400px">
        <div class="img-upload-area">
          <input type="file" accept="application/pdf" onchange="uploadResume(this)">
          <div class="img-upload-label">＋ Replace resume — PDF only, max 10 MB</div>
        </div>
        <div id="resume-status" class="img-uploading" style="display:none">Uploading...</div>
      </div>
    </div>

    <!-- ── ACCOUNT ── -->
    <div class="panel" id="panel-account">
      <div class="panel-header">
        <div class="panel-title">Account</div>
        <div class="panel-sub">Your admin account details</div>
      </div>
      <div style="background:var(--bg2);border:1px solid var(--border);padding:1.5rem;max-width:400px">
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem">
          <?php if ($admin['avatar_url']): ?>
          <img src="<?= htmlspecialchars($admin['avatar_url']) ?>" style="width:48px;height:48px;border-radius:50%;border:1px solid var(--border)" alt="">
          <?php endif; ?>
          <div>
            <div style="font-weight:600;margin-bottom:0.2rem"><?= htmlspecialchars($admin['name']) ?></div>
            <div style="font-size:0.72rem;color:var(--muted)"><?= htmlspecialchars($admin['email']) ?></div>
          </div>
        </div>
        <div style="font-size:0.68rem;color:var(--muted);border-top:1px solid var(--border);padding-top:1rem">
          <div style="margin-bottom:0.5rem">Provider: <span style="color:var(--amber)"><?= htmlspecialchars($admin['provider']) ?></span></div>
          <div>Session expires in 8 hours of inactivity</div>
        </div>
        <div style="margin-top:1.2rem">
          <a href="/api/auth/logout.php" class="btn-reset" style="display:inline-block;text-decoration:none">Sign Out</a>
        </div>
      </div>
    </div>

  </main>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="edit-modal" onclick="handleModalClick(event)">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Edit Project</div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="edit-id">
      <div class="form-grid">
        <div class="form-group full"><label>Title <span class="req">*</span></label><input type="text" id="edit-title"></div>
        <div class="form-group full"><label>Short Description <span class="req">*</span></label><textarea id="edit-short-desc" rows="2"></textarea></div>
        <div class="form-group full"><label>Full Description <span class="req">*</span></label><textarea id="edit-desc" rows="4"></textarea><div class="hint">Markdown: **bold** · *italic* · [text](url) · # Heading · ## Sub-heading</div></div>
        <div class="form-group"><label>Language <span class="req">*</span></label>
          <input type="text" id="edit-lang" list="lang-options" placeholder="e.g. JavaScript">
        </div>
        <div class="form-group"><label>Status</label>
          <select id="edit-status"><option value="active">Active</option><option value="wip">WIP</option><option value="archived">Archived</option></select>
        </div>
        <div class="form-group"><label>GitHub URL</label><input type="url" id="edit-github"></div>
        <div class="form-group"><label>Demo URL</label><input type="url" id="edit-demo"></div>
        <div class="form-group"><label>Tags</label><input type="text" id="edit-tags"><div class="hint">Comma-separated</div></div>
        <div class="form-group"><label>Sort Order</label><input type="text" id="edit-sort"></div>
        <div class="form-group"><label>Year Created</label><input type="number" id="edit-year" min="1900" max="2099"></div>
        <div class="form-group full">
          <label>Project Images</label>
          <div class="img-gallery" id="edit-img-gallery"></div>
          <div class="img-upload-area">
            <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" multiple onchange="uploadImages(this,'edit')">
            <div class="img-upload-label">＋ Add images — jpg, png, gif, webp, max 5 MB each</div>
          </div>
          <div id="edit-img-status" class="img-uploading" style="display:none">Uploading...</div>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn-save" onclick="saveEdit()">💾 Save Changes</button>
        <button class="btn-reset" onclick="closeModal()">Cancel</button>
      </div>
    </div>
  </div>
</div>

<!-- SKILL GROUP EDIT MODAL -->
<div class="modal-overlay" id="skill-modal" onclick="handleSkillModalClick(event)">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Edit Skill Group</div>
      <button class="modal-close" onclick="closeSkillModal()">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="sk-id">
      <div class="form-group"><label>Group Label <span class="req">*</span></label><input type="text" id="sk-label"></div>
      <div class="form-group"><label>Sort Order</label><input type="text" id="sk-sort"><div class="hint">Lower = appears first</div></div>
      <div class="form-group"><label>Skills</label><input type="text" id="sk-skills"><div class="hint">Comma-separated</div></div>
      <div class="form-actions">
        <button class="btn-save" onclick="saveSkillGroup()">💾 Save Changes</button>
        <button class="btn-reset" onclick="closeSkillModal()">Cancel</button>
      </div>
    </div>
  </div>
</div>

<datalist id="lang-options">
  <option>JavaScript</option>
  <option>TypeScript</option>
  <option>Python</option>
  <option>PHP</option>
  <option>Flutter</option>
  <option>Go</option>
  <option>Rust</option>
  <option>Ruby</option>
  <option>C++</option>
  <option>Swift</option>
  <option>Kotlin</option>
  <option>Other</option>
</datalist>

<script>
const API = '/api';
let allProjects = [];

// ── Panel navigation ────────────────────────────────────────
function showPanel(name) {
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('panel-' + name).classList.add('active');
  event.currentTarget.classList.add('active');
}

// ── Load data ───────────────────────────────────────────────
async function loadProjects() {
  const res = await fetch(`${API}/projects/`);
  allProjects = await res.json();
  renderTable(allProjects);
  updateCounts(allProjects);
}

async function loadSettings() {
  const s = await fetch(`${API}/settings/`).then(r => r.json());
  document.getElementById('s-name').value     = s.name     || '';
  document.getElementById('s-role').value     = s.role     || '';
  document.getElementById('s-bio').value      = s.bio      || '';
  document.getElementById('s-email').value    = s.email    || '';
  document.getElementById('s-github').value   = s.github   || '';
  document.getElementById('s-linkedin').value = s.linkedin || '';
  document.getElementById('s-location').value = s.location || '';
  document.getElementById('s-tagline').value  = s.tagline     || '';
  document.getElementById('s-years').value    = s.years_exp   || '';
  document.getElementById('s-ticker').value   = s.ticker_items || '';
}

// ── Render table ─────────────────────────────────────────────
function renderTable(projects) {
  const tbody = document.getElementById('proj-tbody');
  if (!projects.length) {
    tbody.innerHTML = '<tr class="empty-row"><td colspan="6">No projects yet — add one!</td></tr>';
    return;
  }
  tbody.innerHTML = projects.map(p => `
    <tr draggable="true" data-id="${p.id}">
      <td class="drag-handle" title="Drag to reorder">⠿</td>
      <td><strong>${esc(p.title)}</strong></td>
      <td><span class="proj-lang-badge">${esc(p.language)}</span></td>
      <td><span class="status-badge ${esc(p.status)}">${esc(p.status)}</span></td>
      <td style="font-size:0.65rem">
        ${p.github_url ? `<a href="${esc(p.github_url)}" target="_blank" style="color:var(--amber);text-decoration:none">GitHub</a>` : '—'}
        ${p.demo_url   ? ` · <a href="${esc(p.demo_url)}" target="_blank" style="color:var(--blue);text-decoration:none">Demo</a>` : ''}
      </td>
      <td>
        <div class="tbl-actions">
          <button class="tbl-btn edit" onclick="openEdit(${p.id})">Edit</button>
          <button class="tbl-btn del"  onclick="deleteProject(${p.id})">Delete</button>
        </div>
      </td>
    </tr>
  `).join('');
  initDragDrop();
}

function updateCounts(projects) {
  document.getElementById('count-total').textContent  = projects.length;
  document.getElementById('count-active').textContent = projects.filter(p=>p.status==='active').length;
  document.getElementById('count-wip').textContent    = projects.filter(p=>p.status==='wip').length;
}

// ── Image gallery helpers ─────────────────────────────────────
const projectImages       = { ap: [], edit: [] };
const projectSummaryImage = { ap: null, edit: null };

function setSummaryImage(prefix, i) {
  const url = projectImages[prefix][i];
  projectSummaryImage[prefix] = projectSummaryImage[prefix] === url ? null : url;
  renderImageGallery(prefix);
}

async function uploadImages(input, prefix) {
  const files = Array.from(input.files);
  if (!files.length) return;
  const statusEl = document.getElementById(`${prefix}-img-status`);
  statusEl.style.display = 'block';
  for (const file of files) {
    const fd = new FormData();
    fd.append('image', file);
    const res = await fetch(`${API}/uploads/`, { method: 'POST', body: fd });
    if (res.ok) {
      projectImages[prefix].push((await res.json()).url);
    } else {
      toast(`Failed to upload ${file.name}`, true);
    }
  }
  statusEl.style.display = 'none';
  input.value = '';
  renderImageGallery(prefix);
}

function removeProjectImage(prefix, i) {
  const removed = projectImages[prefix][i];
  if (projectSummaryImage[prefix] === removed) projectSummaryImage[prefix] = null;
  projectImages[prefix].splice(i, 1);
  renderImageGallery(prefix);
}

function renderImageGallery(prefix) {
  const gallery = document.getElementById(`${prefix}-img-gallery`);
  gallery.innerHTML = projectImages[prefix].map((url, i) => {
    const isSummary = projectSummaryImage[prefix] === url;
    return `
      <div class="img-thumb${isSummary ? ' is-summary' : ''}" draggable="true" data-index="${i}">
        <img src="${url}" alt="">
        <button type="button" class="img-thumb-del" onclick="removeProjectImage('${prefix}',${i})">✕</button>
        <button type="button" class="img-thumb-star" onclick="setSummaryImage('${prefix}',${i})" title="${isSummary ? 'Summary image (click to unset)' : 'Set as card summary image'}">${isSummary ? '★' : '☆'}</button>
      </div>
    `;
  }).join('');
  initGalleryDragDrop(prefix, gallery);
}

function initGalleryDragDrop(prefix, gallery) {
  let dragSrc = null;
  gallery.querySelectorAll('.img-thumb').forEach(thumb => {
    thumb.addEventListener('dragstart', function(e) {
      dragSrc = this;
      e.dataTransfer.effectAllowed = 'move';
      setTimeout(() => this.style.opacity = '0.35', 0);
    });
    thumb.addEventListener('dragend', function() {
      this.style.opacity = '';
      gallery.querySelectorAll('.img-thumb').forEach(t => t.classList.remove('img-drag-over'));
    });
    thumb.addEventListener('dragover', function(e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      gallery.querySelectorAll('.img-thumb').forEach(t => t.classList.remove('img-drag-over'));
      if (this !== dragSrc) this.classList.add('img-drag-over');
    });
    thumb.addEventListener('drop', function(e) {
      e.preventDefault();
      if (!dragSrc || this === dragSrc) return;
      const srcIdx  = parseInt(dragSrc.dataset.index);
      const destIdx = parseInt(this.dataset.index);
      const imgs = projectImages[prefix];
      const [moved] = imgs.splice(srcIdx, 1);
      imgs.splice(destIdx, 0, moved);
      renderImageGallery(prefix);
    });
  });
}

// ── Add project ──────────────────────────────────────────────
async function addProject() {
  const title     = document.getElementById('ap-title').value.trim();
  const shortDesc = document.getElementById('ap-short-desc').value.trim();
  const desc      = document.getElementById('ap-desc').value.trim();
  const lang      = document.getElementById('ap-lang').value;
  if (!title || !desc || !lang) { toast('Fill in required fields', true); return; }

  const tags = document.getElementById('ap-tags').value.split(',').map(t=>t.trim()).filter(Boolean);
  const body = {
    title, short_description: shortDesc, description: desc, language: lang,
    tags,
    github_url:    document.getElementById('ap-github').value.trim(),
    demo_url:      document.getElementById('ap-demo').value.trim(),
    images:        projectImages.ap,
    summary_image: projectSummaryImage.ap || null,
    status:        document.getElementById('ap-status').value,
    sort_order:    parseInt(document.getElementById('ap-sort').value) || 0,
    year:          parseInt(document.getElementById('ap-year').value) || null,
  };

  const res = await fetch(`${API}/projects/`, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body) });
  if (res.ok) {
    toast('Project added!');
    resetAddForm();
    await loadProjects();
    showPanelById('projects');
  } else {
    const err = await res.json();
    toast(err.error || 'Error', true);
  }
}

function resetAddForm() {
  ['ap-title','ap-short-desc','ap-desc','ap-tags','ap-github','ap-demo'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('ap-lang').value   = '';
  document.getElementById('ap-status').value = 'active';
  document.getElementById('ap-sort').value   = '0';
  document.getElementById('ap-year').value   = '';
  projectImages.ap       = [];
  projectSummaryImage.ap = null;
  renderImageGallery('ap');
}

// ── Delete ───────────────────────────────────────────────────
async function deleteProject(id) {
  if (!confirm('Delete this project? This cannot be undone.')) return;
  const res = await fetch(`${API}/projects/?id=${id}`, { method:'DELETE' });
  if (res.ok) { toast('Project deleted'); await loadProjects(); }
  else toast('Error deleting', true);
}

// ── Edit modal ───────────────────────────────────────────────
function openEdit(id) {
  const p = allProjects.find(proj => proj.id === id);
  if (!p) return;
  document.getElementById('edit-id').value          = p.id;
  document.getElementById('edit-title').value       = p.title;
  document.getElementById('edit-short-desc').value  = p.short_description || '';
  document.getElementById('edit-desc').value        = p.description;

  document.getElementById('edit-lang').value        = p.language;
  document.getElementById('edit-status').value      = p.status;
  document.getElementById('edit-github').value      = p.github_url || '';
  document.getElementById('edit-demo').value        = p.demo_url   || '';
  document.getElementById('edit-tags').value        = (p.tags||[]).join(', ');
  document.getElementById('edit-sort').value        = p.sort_order || 0;
  document.getElementById('edit-year').value        = p.year || '';
  projectImages.edit       = [...(p.images || [])];
  projectSummaryImage.edit = p.summary_image || null;
  renderImageGallery('edit');
  document.getElementById('edit-modal').classList.add('open');
}

async function saveEdit() {
  const id   = document.getElementById('edit-id').value;
  const tags = document.getElementById('edit-tags').value.split(',').map(t=>t.trim()).filter(Boolean);
  const body = {
    title:             document.getElementById('edit-title').value.trim(),
    short_description: document.getElementById('edit-short-desc').value.trim(),
    description:       document.getElementById('edit-desc').value.trim(),
    language:          document.getElementById('edit-lang').value,
    status:            document.getElementById('edit-status').value,
    github_url:        document.getElementById('edit-github').value.trim(),
    demo_url:          document.getElementById('edit-demo').value.trim(),
    images:            projectImages.edit,
    summary_image:     projectSummaryImage.edit || null,
    tags,
    sort_order:        parseInt(document.getElementById('edit-sort').value) || 0,
    year:              parseInt(document.getElementById('edit-year').value) || null,
  };

  const res = await fetch(`${API}/projects/?id=${id}`, { method:'PUT', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body) });
  if (res.ok) {
    closeModal();
    toast('Project updated!');
    await loadProjects();
  } else toast('Error saving', true);
}

function closeModal() {
  document.getElementById('edit-modal').classList.remove('open');
  projectImages.edit       = [];
  projectSummaryImage.edit = null;
  renderImageGallery('edit');
}
function handleModalClick(e) { if (e.target === document.getElementById('edit-modal')) closeModal(); }

// ── Save settings ────────────────────────────────────────────
async function saveSettings() {
  const body = {
    name:     document.getElementById('s-name').value.trim(),
    role:     document.getElementById('s-role').value.trim(),
    bio:      document.getElementById('s-bio').value.trim(),
    email:    document.getElementById('s-email').value.trim(),
    github:   document.getElementById('s-github').value.trim(),
    linkedin: document.getElementById('s-linkedin').value.trim(),
    location: document.getElementById('s-location').value.trim(),
    tagline:      document.getElementById('s-tagline').value.trim(),
    years_exp:    document.getElementById('s-years').value.trim(),
    ticker_items: document.getElementById('s-ticker').value.trim(),
  };
  const res = await fetch(`${API}/settings/`, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body) });
  if (res.ok) toast('Settings saved!');
  else toast('Error saving settings', true);
}

// ── Drag-and-drop reorder ────────────────────────────────────
function initDragDrop() {
  const tbody = document.getElementById('proj-tbody');
  let dragSrc = null;

  tbody.querySelectorAll('tr[data-id]').forEach(row => {
    row.addEventListener('dragstart', function(e) {
      dragSrc = this;
      this.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
    });
    row.addEventListener('dragend', function() {
      this.classList.remove('dragging');
      tbody.querySelectorAll('tr').forEach(r => r.classList.remove('drag-over'));
    });
    row.addEventListener('dragover', function(e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      tbody.querySelectorAll('tr').forEach(r => r.classList.remove('drag-over'));
      if (this !== dragSrc) this.classList.add('drag-over');
    });
    row.addEventListener('drop', function(e) {
      e.preventDefault();
      if (!dragSrc || this === dragSrc) return;
      const srcId  = parseInt(dragSrc.dataset.id);
      const destId = parseInt(this.dataset.id);
      const srcIdx  = allProjects.findIndex(p => p.id === srcId);
      const destIdx = allProjects.findIndex(p => p.id === destId);
      const [moved] = allProjects.splice(srcIdx, 1);
      allProjects.splice(destIdx, 0, moved);
      allProjects.forEach((p, i) => p.sort_order = i);
      renderTable(allProjects);
      updateCounts(allProjects);
      saveOrder();
    });
  });
}

async function saveOrder() {
  const payload = allProjects.map((p, i) => ({ id: p.id, sort_order: i }));
  const res = await fetch(`${API}/projects/`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (res.ok) toast('Order saved');
  else toast('Error saving order', true);
}

// ── Toast ────────────────────────────────────────────────────
function toast(msg, err=false) {
  const t = document.createElement('div');
  t.className = 'toast' + (err ? ' err' : '');
  t.textContent = (err ? '⚠ ' : '✓ ') + msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2500);
}

function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showPanelById(name) {
  document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  document.getElementById('panel-'+name).classList.add('active');
  document.querySelectorAll('.nav-item').forEach(n=>{
    if (n.getAttribute('onclick')?.includes(`'${name}'`)) n.classList.add('active');
  });
}

// ── Skills ───────────────────────────────────────────────────
let allSkillGroups = [];

async function loadSkills() {
  const res = await fetch(`${API}/skills/`);
  allSkillGroups = await res.json();
  renderSkillGroups();
}

function renderSkillGroups() {
  const list = document.getElementById('skill-groups-list');
  if (!allSkillGroups.length) {
    list.innerHTML = '<div style="color:var(--muted);font-size:0.78rem;padding:0.5rem 0">No skill groups yet — add one below.</div>';
    return;
  }
  list.innerHTML = allSkillGroups.map(g => `
    <div style="background:var(--bg2);border:1px solid var(--border);padding:1rem 1.2rem;display:flex;align-items:flex-start;gap:1rem">
      <div style="flex:1;min-width:0">
        <div style="font-size:0.58rem;letter-spacing:0.16em;text-transform:uppercase;color:var(--amber);margin-bottom:0.5rem">${esc(g.label)}</div>
        <div style="display:flex;flex-wrap:wrap;gap:0.35rem">
          ${(g.skills||[]).map(s=>`<span style="font-size:0.68rem;color:var(--muted);border:1px solid var(--border);padding:0.15rem 0.5rem">${esc(s)}</span>`).join('')}
        </div>
      </div>
      <div class="tbl-actions">
        <button class="tbl-btn" onclick="openSkillEdit(${g.id})">Edit</button>
        <button class="tbl-btn del" onclick="deleteSkillGroup(${g.id})">Delete</button>
      </div>
    </div>
  `).join('');
}

function openSkillEdit(id) {
  const g = allSkillGroups.find(x => x.id === id);
  if (!g) return;
  document.getElementById('sk-id').value     = g.id;
  document.getElementById('sk-label').value  = g.label;
  document.getElementById('sk-sort').value   = g.sort_order ?? 0;
  document.getElementById('sk-skills').value = (g.skills||[]).join(', ');
  document.getElementById('skill-modal').classList.add('open');
}

async function saveSkillGroup() {
  const id   = document.getElementById('sk-id').value;
  const body = {
    label:      document.getElementById('sk-label').value.trim(),
    skills:     document.getElementById('sk-skills').value.split(',').map(s=>s.trim()).filter(Boolean),
    sort_order: parseInt(document.getElementById('sk-sort').value) || 0,
  };
  if (!body.label) { toast('Label is required', true); return; }
  const res = await fetch(`${API}/skills/?id=${id}`, { method:'PUT', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body) });
  if (res.ok) { closeSkillModal(); toast('Skill group updated!'); await loadSkills(); }
  else toast('Error saving', true);
}

async function addSkillGroup() {
  const body = {
    label:      document.getElementById('sg-label').value.trim(),
    skills:     document.getElementById('sg-skills').value.split(',').map(s=>s.trim()).filter(Boolean),
    sort_order: parseInt(document.getElementById('sg-sort').value) || 0,
  };
  if (!body.label) { toast('Label is required', true); return; }
  const res = await fetch(`${API}/skills/`, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body) });
  if (res.ok) {
    document.getElementById('sg-label').value  = '';
    document.getElementById('sg-skills').value = '';
    document.getElementById('sg-sort').value   = '0';
    toast('Skill group added!');
    await loadSkills();
  } else toast('Error adding', true);
}

async function deleteSkillGroup(id) {
  if (!confirm('Delete this skill group?')) return;
  const res = await fetch(`${API}/skills/?id=${id}`, { method:'DELETE' });
  if (res.ok) { toast('Deleted'); await loadSkills(); }
  else toast('Error deleting', true);
}

function closeSkillModal() { document.getElementById('skill-modal').classList.remove('open'); }
function handleSkillModalClick(e) { if (e.target === document.getElementById('skill-modal')) closeSkillModal(); }

// ── Resume ───────────────────────────────────────────────────
async function loadResume() {
  const res = await fetch(`${API}/uploads/resume.php`);
  if (!res.ok) return;
  const data = await res.json();
  if (data.url) {
    document.getElementById('resume-current').style.display = 'block';
    document.getElementById('resume-link').href = data.url;
  }
}

async function uploadResume(input) {
  if (!input.files[0]) return;
  const statusEl = document.getElementById('resume-status');
  statusEl.style.display = 'block';
  const fd = new FormData();
  fd.append('resume', input.files[0]);
  const res = await fetch(`${API}/uploads/resume.php`, { method: 'POST', body: fd });
  statusEl.style.display = 'none';
  input.value = '';
  if (res.ok) {
    const data = await res.json();
    document.getElementById('resume-current').style.display = 'block';
    document.getElementById('resume-link').href = data.url;
    toast('Resume uploaded!');
  } else {
    const err = await res.json().catch(() => ({}));
    toast(err.error || 'Upload failed', true);
  }
}

// ── Init ─────────────────────────────────────────────────────
loadProjects();
loadSettings();
loadSkills();
loadResume();
</script>
</body>
</html>
