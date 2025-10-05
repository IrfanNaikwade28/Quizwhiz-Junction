# Quizwhiz-Junction Security and Architecture Notes

Key improvements added:

- Database singleton (`lib/Database.php`) with mysqli exceptions and utf8mb4.
- Secure session handling (`lib/Init.php`) using HttpOnly and SameSite=Lax cookies.
- Authentication utilities (`lib/Auth.php`) with password_hash/password_verify and legacy password auto-upgrade.
- CSRF protection helper (`lib/Helpers.php`) and output escaping.
- Converted destructive actions (delete user, remove quiz, restart quiz, submit answers) to POST with CSRF tokens.
- XSS hardening across rendered outputs.

Configuration:

- Default DB credentials use localhost/root/blank and database `exam`.
- Optionally configure via environment variables: DB_HOST, DB_USER, DB_PASS, DB_NAME.

Legacy Data:

- Existing plaintext passwords in `user` and `admin` tables will be auto-upgraded to strong hashes upon the next successful login.

Schema Notes:

- On first run, the app attempts to widen password columns to `VARCHAR(255)` for compatibility with password_hash.
