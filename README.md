## Quick start

- Create MySQL database `exam` and import `exam.sql` via phpMyAdmin.
- Ensure XAMPP Apache/PHP is running and this folder is under `htdocs`.
- Optional: set environment variables DB_HOST, DB_USER, DB_PASS, DB_NAME.
- Open `index.php` to use the app or `admin.php` for admin access.

For security and architecture details, see `SECURITY_NOTES.md`.

Note: For simplicity in a college project setup, CSRF checks and password hashing are disabled by default. To enable them, set environment variables `ENABLE_CSRF=true` and/or `USE_PASSWORD_HASHING=true` (for example in Apache httpd.conf or via a `.env` loader if you add one).
