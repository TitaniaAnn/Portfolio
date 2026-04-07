# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this project is

A PHP/MySQL portfolio site deployed on shared hosting (Bluehost/cPanel). The web root is `public/`; all other directories are backend-only and blocked from direct access via `.htaccess`.

## No build step

This is plain PHP — no Composer, no npm, no compilation. Edit files and deploy directly. To test locally you need a PHP + MySQL server (e.g. XAMPP, Laravel Herd, or `php -S localhost:8000` pointed at the repo root).

## Configuration

All secrets and environment values live in [config/config.php](config/config.php). This file is not committed with real values — fill in DB credentials, `APP_URL`, `APP_SECRET`, OAuth keys, and `ADMIN_EMAILS` before deploying.

## Request routing

`.htaccess` rewrites all requests to `public/`. The PHP files under `api/` are reached via clean URLs like `/api/projects/`, `/api/auth/google/login.php`, etc. because `.htaccess` maps `/api/...` → `public/api/...` → `api/...` is actually served directly — verify by reading the rewrite rules if confused.

Actually: `.htaccess` maps bare `/` to `public/index.php` and everything else to `public/$1`. The `api/`, `admin/`, and `includes/` directories sit at the repo root and are accessed directly at their paths.

## Architecture

- **[config/config.php](config/config.php)** — all constants (DB, OAuth, APP_URL, session lifetime, admin whitelist)
- **[includes/db.php](includes/db.php)** — singleton PDO connection via `db()` function
- **[includes/auth.php](includes/auth.php)** — session helpers: `current_admin()`, `require_admin()`, `create_session()`, `upsert_admin()`
- **[includes/response.php](includes/response.php)** — `json_response()`, `cors_headers()`, `get_json_body()`
- **[api/projects/index.php](api/projects/index.php)** — REST CRUD for projects (GET public, write requires auth)
- **[api/settings/index.php](api/settings/index.php)** — GET/POST for key-value site settings
- **[api/uploads/index.php](api/uploads/index.php)** — multipart image upload (admin only), saves to `uploads/projects/`
- **[api/auth/google/](api/auth/google/)** and **[api/auth/github/](api/auth/github/)** — OAuth login/callback flows
- **[admin/index.php](admin/index.php)** — single-file admin dashboard (inline CSS + JS, no framework)
- **[public/index.php](public/index.php)** — portfolio homepage (fetches from API client-side)

## Database

Schema is in [sql/schema.sql](sql/schema.sql). Tables: `admins`, `sessions`, `projects`, `project_images` (one-to-many), `settings` (key-value).

Migration comments for adding columns to existing installs are at the bottom of `schema.sql`.

## Auth model

Only emails listed in `ADMIN_EMAILS` (config.php) can log in. OAuth callbacks check this whitelist and reject anyone else. Sessions are stored in MySQL (`sessions` table) with an 8-hour expiry. `require_admin()` validates the session token on every write endpoint.

## Tags and images

`projects.tags` is stored as a comma-separated string in the DB and split into arrays on read. `project_images` is a separate table; images are returned as a `images` array on every project response. Uploaded files are stored under `uploads/projects/` with a random hex filename.

## Deployment

This runs on cPanel shared hosting. The HTTPS redirect in `.htaccess` should be uncommented once SSL is active. `config/` and `includes/` are blocked from web access via `.htaccess` deny rules.
