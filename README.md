# Portfolio — Setup Guide

## File Structure
```
portfolio/
├── config/
│   └── config.php          ← EDIT THIS FIRST
├── includes/
│   ├── auth.php
│   ├── db.php
│   └── response.php
├── api/
│   ├── auth/
│   │   ├── google/
│   │   │   ├── login.php
│   │   │   └── callback.php
│   │   ├── github/
│   │   │   ├── login.php
│   │   │   └── callback.php
│   │   ├── logout.php
│   │   └── status.php
│   ├── projects/
│   │   └── index.php
│   └── settings/
│       └── index.php
├── public/
│   ├── index.php           ← Portfolio homepage
│   └── admin/
│       ├── index.php       ← Admin dashboard
│       └── login.php       ← Login page
├── schema.sql
├── .htaccess
└── README.md
```




---

## Step 1 — Upload Files

Upload all files to your hosting server, keeping the directory structure intact.
Place the `public/` contents at your web root (or configure your host to point there).

---

## Step 2 — Create the Database

1. In cPanel / phpMyAdmin, create a new MySQL database (e.g. `portfolio`)
2. Create a database user and grant it full privileges on that database
3. Import `schema.sql` — this creates all tables and seeds default data

---

## Step 3 — Edit `config/config.php`

Fill in:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `APP_URL` — your full domain, no trailing slash (e.g. `https://yourdomain.com`)
- `APP_SECRET` — run `openssl rand -hex 32` and paste the result
- `ADMIN_EMAILS` — already set to `titaniaandroid@gmail.com`

---

## Step 4 — Set Up Google OAuth

1. Go to https://console.cloud.google.com/apis/credentials
2. Create a new project (or use an existing one)
3. Click **Create Credentials → OAuth client ID**
4. Application type: **Web application**
5. Add Authorised redirect URI:
   ```
   https://yourdomain.com/api/auth/google/callback.php
   ```
6. Copy the **Client ID** and **Client Secret** into `config.php`
7. On the OAuth consent screen, add `titaniaandroid@gmail.com` as a Test User (if app is in testing mode)

---

## Step 5 — Set Up GitHub OAuth

1. Go to https://github.com/settings/developers
2. Click **New OAuth App**
3. Set **Authorization callback URL** to:
   ```
   https://yourdomain.com/api/auth/github/callback.php
   ```
4. Copy the **Client ID** and generate a **Client Secret** → paste both into `config.php`
5. Make sure the GitHub account linked to your email has that email set as **primary & verified**

---

## Step 6 — Enable HTTPS

Uncomment the HTTPS redirect block in `.htaccess` once your SSL certificate is active.
Most shared hosts provide free SSL via Let's Encrypt in cPanel.

---

## Step 7 — Test

1. Visit `https://yourdomain.com` — portfolio loads data from the API
2. Visit `https://yourdomain.com/admin/` — redirects to login
3. Click **Continue with Google** or **Continue with GitHub**
4. After sign-in, you're in the admin dashboard

---

## Admin Features

| Feature | Description |
|---------|-------------|
| Add Project | Title, description, language, GitHub URL, demo URL, tags, sort order |
| Edit Project | Inline modal editor for all fields |
| Delete Project | Confirmation prompt before deletion |
| Site Settings | Name, role, bio, email, GitHub, LinkedIn, location, tagline |
| Account | View your OAuth profile, sign out |

---

## Security Notes

- Only emails listed in `ADMIN_EMAILS` (config.php) can log in — everyone else is blocked at callback
- Sessions are stored server-side in MySQL with an 8-hour expiry
- Session cookies are `HttpOnly`, `Secure`, and `SameSite=Lax`
- CSRF is mitigated via OAuth `state` parameter (validated on callback)
- All API write endpoints require a valid session
- `config/` and `includes/` directories are blocked from direct web access via `.htaccess`
- SQL uses PDO prepared statements throughout
