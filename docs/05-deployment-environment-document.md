# Deployment & Environment Document

## Hindustan Vidyut Udyog Solar

### 1. Hosting Assumptions

Standard shared/VPS PHP hosting — no containers, no special infra needed:

- PHP 8.1+ with `pdo_mysql`, `mbstring`, `fileinfo` extensions
- MySQL 5.7+/MariaDB 10.3+
- Apache (with `mod_rewrite`) or Nginx
- HTTPS via free provider SSL (Let's Encrypt / hosting panel AutoSSL)

### 2. Folder Structure

Flat, self-explanatory top-level folders. The whole project root is the
webserver document root (typical on shared PHP hosting) — no nested
`public/` split. Sensitive folders (`database/`, `config/`) are locked
down via `.htaccess`/Nginx `deny` rules instead of being moved outside
the root.

```
/
├── index.php               # Home page
├── about.php
├── services.php
├── products.php
├── contact.php
├── .htaccess               # deny access to database/, config/, storage/
│
├── config/                 # app config + bootstrap
│   ├── config.php          # loads .env, defines constants
│   └── db.php              # PDO connection helper
│
├── database/
│   └── schema.sql          # from 02-database-entity-document.md
│
├── components/             # shared PHP partials (header, footer, forms, cards)
│   ├── header.php
│   ├── footer.php
│   ├── nav.php
│   └── flash-message.php
│
├── assets/
│   ├── css/
│   │   ├── input.css       # Tailwind source
│   │   └── app.css         # Tailwind build output
│   ├── js/
│   │   └── app.js          # small vanilla JS (nav toggle, AJAX calls)
│   └── images/
│
├── storage/
│   └── uploads/
│       └── products/       # uploaded product images (writable)
│
├── admin/                  # admin portal (auth-gated)
│   ├── login.php
│   ├── logout.php
│   ├── index.php           # dashboard
│   ├── jobs.php
│   ├── job.php
│   ├── team.php
│   ├── leads.php
│   ├── products.php
│   ├── services.php
│   └── api/
│       ├── job-status.php
│       └── job-assign.php
│
├── tailwind.config.js
├── package.json            # only for the Tailwind CLI build step
├── .env.example
└── .gitignore
```

Everything a page needs is one `require` away: config from `config/`,
shared markup from `components/`, styles/scripts from `assets/`. No PHP
autoloading, no namespaces — plain `require_once` includes.

### 3. Environment Configuration

Use a simple `.env` file (loaded with a ~10-line parser, no Composer
dependency needed) or a plain `config.php` if the host can't guarantee
`.env` is unreadable via HTTP.

`.env.example`:
```
DB_HOST=localhost
DB_NAME=hvu_solar
DB_USER=hvu_solar_user
DB_PASS=change_me
APP_ENV=production
APP_URL=https://hindustanvidyutudyog.com
```

`config/config.php` loads these into constants/variables consumed by
`config/db.php` (PDO connection) — one place, no scattered credentials.

### 4. Build Step (Tailwind only)

Tailwind CSS needs a one-time/CI build; nothing else in the stack needs
Node.

```bash
npm install
npx tailwindcss -i assets/css/input.css -o assets/css/app.css --minify
```

Run this on deploy (or commit the compiled `app.css` if the host has no
Node available at all).

### 5. Deployment Steps

1. Push/pull code to server (Git or SFTP/zip upload — whichever the host
   supports).
2. Run the Tailwind build (step 4) if Node is available on the server;
   otherwise upload the pre-built `assets/css/app.css`.
3. Copy `.env.example` → `.env`, fill in real DB credentials (never
   commit `.env`).
4. Import `database/schema.sql` into the MySQL database (first deploy
   only; subsequent deploys use small numbered migration `.sql` files if
   the schema changes).
5. Set `storage/uploads/` writable by the webserver user.
6. Point the domain's document root at the project root (or copy the
   `.htaccess` deny rules to whatever the host uses to block direct
   access to `database/` and `config/`).
7. Verify HTTPS is active and HTTP redirects to HTTPS.
8. Smoke test: load Home, submit the Contact form, log into `/admin/`,
   confirm the new lead appears.

### 6. Backups

- Daily MySQL dump (`mysqldump`) via host's cron or panel backup feature,
  retained 7-14 days.
- `storage/uploads/` included in file backups (product images are not
  reproducible from the DB alone).

### 7. Security Basics

- Force HTTPS (redirect in `.htaccess`/Nginx config).
- Admin passwords hashed with `password_hash()`/`password_verify()` —
  never stored plain.
- Session cookies: `HttpOnly`, `Secure`, `SameSite=Lax`.
- CSRF token on every admin POST form (see API doc §2).
- File uploads (product images): validate extension + MIME type,
  regenerate filename, store outside of any executable-PHP-by-default
  quirk, cap file size.
- `.env`, `config/`, and `database/` denied via `.htaccess`/Nginx rules
  (they stay inside the document root for simplicity, but must never be
  servable as plain text/PHP source).
- MySQL user scoped to only the `hvu_solar` database, not root.

### 8. Environments

Two are enough at this scale:

| Env | Purpose | DB |
|---|---|---|
| **Local** | Development on a dev machine (XAMPP/Laragon/PHP built-in server) | local MySQL, seeded with sample data |
| **Production** | Live site | production MySQL, real data, daily backups |

No separate staging environment required for a site this size — test
locally, deploy to production directly, keep a recent DB backup before
any schema change.
