# Quizwhiz Junction

A modern PHP + MySQL quiz web app with a fast Tailwind UI and a clean admin panel. Built for quick local runs on XAMPP and easy tweaking.

## Highlights
- Authentication: register, login, logout (secure password hashing)
- Quizzes: 30s per-question timer, auto-advance, server‑validated timing
- Results: final score, total time, per‑question breakdown
- History: past attempts with points delta and timestamps
- Invites: generate/share code; points rewarded on redemption
- Rankings: global leaderboard (score desc, total_time asc on ties)
- Admin: manage quizzes, questions, options, users, and ranks
- UI: Tailwind (CDN) with a purple/lavender theme; responsive down from desktop

## Tech stack
- PHP 7.4+ (works with PHP 8.x)
- MySQL 5.7/8.0
- Tailwind CSS (via CDN) + a bit of vanilla JS (`assets/js/app.js`)

## Requirements
- Windows with XAMPP (Apache + MySQL) or any LAMP/WAMP equivalent
- A MySQL database user with permissions to create/read/write tables

## Quick start (Windows + XAMPP)
1) Place the project in your XAMPP web root
   - Path: `C:\xampp\htdocs\Quizwhiz-Junction`

2) Create the database
   - Name suggestion: `quizwhiz_junction`
   - Using phpMyAdmin: http://localhost/phpmyadmin → Databases → Create

3) Import schema and sample data
   - In phpMyAdmin: select your DB → Import → choose `database.sql` → Go
   - Optional (CLI): if MySQL is on your PATH, from the project root you can run in PowerShell:
     - `mysql -u root -p quizwhiz_junction < database.sql`

4) Configure the app
   - Edit `includes/config.php`:
     - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
     - `APP_BASE_URL` (default `/Quizwhiz-Junction`; change if deployed elsewhere)

5) Start services and open the app
   - Start Apache and MySQL in XAMPP Control Panel
   - Visit: http://localhost/Quizwhiz-Junction/

## Default access (for first run)
On the first DB connection, the app seeds helpful defaults:
- Admin account (admin panel login via `admin_login.php`):
  - Username: `admin`
  - Password: `pass123`
- Admin user (in `users` table, can access admin via user session if allowed):
  - Email: `admin@gmail.com`
  - Password: `pass123`

Admin area: http://localhost/Quizwhiz-Junction/admin/

Tip: Change these credentials immediately in production.

## Project structure
```
admin/               # Admin dashboard and management pages
assets/
  css/               # Styles (CDN by default; optional local build)
  img/               # Place screenshots/assets here
  js/                # Frontend scripts (app.js)
includes/
  auth.php           # Auth helpers (users + admin_accounts)
  config.php         # DB + app settings (APP_BASE_URL)
  header.php, footer.php, helpers.php
database.sql         # Schema + seed
index.php            # Landing
user_login.php       # User login
user_register.php    # User register
admin_login.php      # Admin login (separate session)
dashboard.php, quizzes.php, quiz.php, results.php, history.php, invite.php
```

## How timing and integrity work
- Per‑question timing is computed on the server; client time is ignored/clamped to 30s
- Sessions track resume state; refresh won’t bypass timing guards

## Configure Tailwind locally (optional)
The UI ships via Tailwind CDN and works out of the box. If you prefer a local CSS build:
- Install Node.js
- Add your Tailwind pipeline and output to `assets/css/`
- Update `includes/header.php` to load the local CSS instead of the CDN link

## Troubleshooting
- Blank/unstyled pages → Check `APP_BASE_URL` in `includes/config.php` and verify paths
- DB connection failed → Verify MySQL is running and credentials match your XAMPP setup
- Admin access denied → Use the seeded admin credentials (above) or set `is_admin = 1` on your user
- 404s under `/admin/` → Ensure you’re logged in via `admin_login.php` or you have `is_admin = 1`

## Security checklist (production)
- Change default DB user/password and the seeded admin credentials
- Set a proper `APP_BASE_URL` (or use a virtual host)
- Serve over HTTPS; set `session.cookie_secure=1`
- Disable verbose error display in production

## Contributing
PRs and issues are welcome. Please keep changes focused and include a short description of the user impact.

## License
MIT
