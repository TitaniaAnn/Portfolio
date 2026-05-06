# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this project is

A PHP/MySQL portfolio site deployed on shared hosting (Bluehost/cPanel). The web root is the repo root; `.htaccess` rewrites unmatched paths into `public/`. Other top-level directories (`api/`, `admin/`, `includes/`, `config/`, `uploads/`, `sql/`) are reached directly because they exist as real directories on disk.

## No build step

Plain PHP — no Composer, no npm, no compilation. Edit files and deploy directly. To run locally you need a PHP + MySQL server (XAMPP, Laravel Herd, or `php -S localhost:8000` from the repo root). Parsedown is vendored at [includes/Parsedown.php](includes/Parsedown.php) so there is nothing to install.

## Configuration

All secrets and environment values live in [config/config.php](config/config.php). It is not committed with real values — fill in DB credentials, `APP_URL`, `APP_SECRET`, OAuth keys, and `ADMIN_EMAILS` before deploying.

## Request routing

`.htaccess` does three rewrites, in order:

1. `/` → `public/index.php`
2. `/writing/<slug>/` → `public/writing/post.php?slug=<slug>` (only when no real file/dir matches; this lets `/writing/` and `/writing/feed.xml` still serve directly)
3. Everything else not a real file/dir → `public/$1`

Because `api/`, `admin/`, and `includes/` exist as real directories at the repo root, requests like `/api/projects/` or `/admin/login.php` hit them directly without rewriting. `config/` and `includes/` are blocked from web access via `.htaccess` deny rules.

## Architecture

### Shared includes ([includes/](includes/))

- **[db.php](includes/db.php)** — singleton PDO via `db()`
- **[auth.php](includes/auth.php)** — `current_admin()`, `require_admin()` (read-only), `require_admin_write()` (state-changing, enforces CSRF), `create_session()`, `upsert_admin()`, `is_allowed_email()`
- **[response.php](includes/response.php)** — `json_response()`, `cors_headers()`, `get_json_body()`
- **[util.php](includes/util.php)** — `csv_to_array()`, `clean_url()`, `string_list()`, `fetch_settings()`, `audit_log()`, plus `column_exists()`/`table_exists()`/`index_exists()` used by the migration runner
- **[upload.php](includes/upload.php)** — `validate_upload()` and `random_filename()` shared by the image and resume upload endpoints
- **[http.php](includes/http.php)** — curl wrappers (`http_get`, `http_post`, `http_get_bearer`) used by OAuth callbacks; throws `RuntimeException` on transport/parse failure
- **[markdown.php](includes/markdown.php)** — `render_markdown()` and `markdown_excerpt()`; Parsedown is configured in **safe mode** (raw HTML escaped), defense in depth even though only the admin writes posts

### API endpoints (under `api/`)

- **[projects/index.php](api/projects/index.php)** — REST CRUD; GET public, write requires `require_admin_write()`. Supports `PATCH` for bulk reorder.
- **[projects/import.php](api/projects/import.php)** — bulk import projects from JSON (admin); accepts a single object, a bare list, or `{projects: [...]}`. Skips by case-insensitive title match.
- **[posts/index.php](api/posts/index.php)** — admin CRUD for writing posts. After every write, regenerates the static `public/writing/feed.xml`. Public pages read directly from the DB (not this endpoint) so they can server-render OG tags.
- **[posts/preview.php](api/posts/preview.php)** — renders unsaved markdown for the admin editor preview pane.
- **[settings/index.php](api/settings/index.php)** — GET/POST for key-value site settings.
- **[skills/index.php](api/skills/index.php)** — CRUD for the About-section skill groups.
- **[uploads/index.php](api/uploads/index.php)** — multipart project image upload → `uploads/projects/<random>.<ext>`.
- **[uploads/resume.php](api/uploads/resume.php)** — resume upload (separate validation rules).
- **[audit/index.php](api/audit/index.php)** — read-only feed of `audit_log` rows for the admin dashboard.
- **[auth/google/](api/auth/google/)**, **[auth/github/](api/auth/github/)**, **[auth/status.php](api/auth/status.php)**, **[auth/logout.php](api/auth/logout.php)** — OAuth login/callback flows and session status.

### Admin and public pages

- **[admin/index.php](admin/index.php)** — single-file admin dashboard, inline CSS + JS, no framework. Uses the API endpoints above.
- **[admin/login.php](admin/login.php)** — login screen with Google + GitHub buttons.
- **[admin/update.php](admin/update.php)** — **web-based migration runner**. Each migration has an idempotent `check()` (using `column_exists`/`table_exists`/`index_exists`) and an `sql` string. Pending migrations are listed; clicking "Run" applies all pending ones and writes `migration.applied`/`migration.failed` to the audit log. This is the canonical way to upgrade existing installs — `sql/schema.sql` is the *target* schema, not a migration script.
- **[public/index.php](public/index.php)** — portfolio homepage. Server-renders the page shell (and conditionally the Writing nav link based on whether any post is published), then fetches projects/settings client-side.
- **[public/writing/index.php](public/writing/index.php)** — server-rendered list of published posts with tag-chip filter and plain-text search; works without JS.
- **[public/writing/post.php](public/writing/post.php)** — single post page; reached via the `/writing/<slug>/` rewrite.

## Database

Schema is in [sql/schema.sql](sql/schema.sql). Tables:

- `admins` — whitelisted email login records
- `sessions` — server-side sessions, with a per-session `csrf_token` (double-submit) and `expires_at` index
- `projects` — main content; `tags` is comma-separated, split into arrays on read
- `project_images` — one-to-many gallery images per project
- `skill_groups` — About-section skill groupings (`skills` is comma-separated)
- `settings` — key-value site settings
- `posts` — markdown writing entries (slug, title, excerpt, body_markdown, tags, is_published, published_at)
- `post_projects` — many-to-many link between posts and projects
- `audit_log` — security-relevant events (logins, denials, admin writes, migration runs)

Schema changes are applied via [admin/update.php](admin/update.php), not by re-running `schema.sql`. When adding a column or table, append a new entry to `$migrations` in `admin/update.php` *and* mirror it in `schema.sql` so fresh installs match.

## Auth model

- Only emails listed in `ADMIN_EMAILS` (config.php) can log in. OAuth callbacks reject anyone else.
- Sessions live in MySQL (`sessions` table), 8-hour expiry, `HttpOnly` + `Secure` + `SameSite=Lax` cookies.
- CSRF: every session has a `csrf_token`. State-changing API calls must send `X-CSRF-Token` matching `current_admin()['csrf_token']`. `require_admin_write()` enforces this for non-GET methods; GET/HEAD/OPTIONS are exempt and use `require_admin()`.
- The OAuth `state` parameter is validated on callback to prevent login CSRF.
- `auth.php` has fallback paths that swallow `PDOException` when `sessions.csrf_token` doesn't exist yet — this exists so an admin on a pre-migration install can still reach `/admin/update.php` to run migrations. Don't remove these without updating the migration story.
- `audit_log()` in `util.php` never throws — a logging outage cannot block a write.

## Tags, images, and posts

- `projects.tags` and `skill_groups.skills` are stored as comma-separated strings and split with `csv_to_array()` on read.
- `project_images` rows are returned as an `images` array on every project response.
- Project image uploads go to `uploads/projects/<random hex>.<ext>` — filenames are not user-controlled.
- Posts store raw markdown in `body_markdown`; rendering happens at read time via `render_markdown()`. Parsedown safe mode is on, so any raw HTML in a post is escaped.
- After any post write, `api/posts/index.php` rewrites `public/writing/feed.xml` (RSS 2.0). Don't hand-edit that file — it'll be overwritten on the next save.

## Deployment

Runs on cPanel shared hosting. The HTTPS redirect block in `.htaccess` should be uncommented once SSL is active. `.user.ini` and `php.ini` set per-account PHP limits.
