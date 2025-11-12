# Quizwhiz Junction

A full-featured PHP + MySQL quiz web application with a desktop-first Tailwind CSS UI inspired by a purple gradient theme (lavender, violet, white; soft-rounded components; playful avatars).

## Features
- Auth: Register, Login, Logout (secure password hashing)
- Dashboard: Rank, points, badges, recent matches
- Quizzes: List, start attempts, 30s per-question timer, auto-advance, server-validated timing
- Results: Score, total_time, per-question breakdown
- History: Past attempts with points delta, avatars, time
- Invites: Generate/share invite code, copy-to-clipboard, reward points on redemption
- Rankings: Global and local ranking (by score desc, then total_time asc for ties)
- Admin: Manage quizzes, questions, options, users, ranking overview
- Desktop-first UI using Tailwind CDN with custom theme; gracefully scales down

## Setup
1. Create a MySQL database (e.g., `quizwhiz_junction`).
2. Import `database.sql` into your MySQL server.
3. Update DB credentials in `includes/config.php` to match your environment.
4. Start Apache and MySQL (e.g., via XAMPP) and open: `http://localhost/Quizwhiz-Junction/`.

Optional (for local Tailwind build):
- A ready-to-run UI is provided via Tailwind CDN with inline configuration. If you prefer a local build, install Node.js and run:
  - `npm install`
  - `npm run build` (outputs `assets/css/tailwind.css`)
  Update `includes/header.php` to use the local CSS instead of the CDN.

## Admin access
- After importing `database.sql`, you can create a user and update its `is_admin` field to `1` in the `users` table to access the admin area at `/admin/`.

## Notes on timing integrity
- Timing is validated server-side per question using a server timestamp started when the question is rendered. Submitted per-question time is computed on the server and clamped to a maximum of 30 seconds.
- Page refresh is resilient: the quiz resume state is tracked in the session with the active question and server start time; the remaining time is recalculated on render.

## Folder structure
- `includes/` shared PHP (DB config, auth, helpers, layouts/components)
- `assets/` static assets (JS, CSS, images)
- `admin/` admin pages
- Root PHP pages for public and authenticated app

## License
MIT
