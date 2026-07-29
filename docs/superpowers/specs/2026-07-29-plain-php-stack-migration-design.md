# Plain PHP + MySQL + Tailwind CSS Migration — Design

**Date:** 2026-07-29

## Context

The project currently ships as a Laravel 13 + Livewire/Volt app (Breeze auth, admin panel, leads/jobs pipeline, customer portal, tests — see git history and old README). The user wants to discard this entirely and rebuild on plain PHP + MySQL (via PDO) + Tailwind CSS, matching their preferred stack. `docs/` (PRD, DB doc, UI doc, API doc) stays as the reference spec for what the site should eventually do, but nothing in it is Laravel-specific in content — only in stack assumptions.

## Decisions

- **Discard everything, fresh start.** Delete all Laravel/PHP-framework code: `app/`, `bootstrap/`, `config/`, `database/`, `routes/`, `resources/`, `tests/`, `vendor/`, `node_modules/`, `artisan`, `composer.json`, `composer.lock`, `phpunit.xml`, `vite.config.js`, `postcss.config.js`, `.phpunit.result.cache`, the Laravel-shaped `.env`/`.env.example`. Keep `docs/` and `.git`.
- **DB access:** PDO with prepared statements (no query builder/ORM).
- **Routing/structure:** plain multi-file PHP — one `.php` file per public URL, no front controller or router. Simplest, most host-compatible.
- **CSS:** Tailwind CLI (Node dev-dependency only, no bundler/framework) compiling `resources/css/app.css` → `public/css/app.css`.
- **V1 scope:** public marketing site only — Home, About, Services, Products, Gallery, Blog (index + post), Contact, Get-a-Quote. No login/admin/portal in this pass (matches the PRD's public site map in `docs/03-ui-ux-screen-document.md` §2).

## Structure

```
config.php.example        # DB credential template (config.php is gitignored)
database/
  schema.sql               # products, blog_posts, quote_requests, company_settings
includes/
  db.php                    # PDO connection (reads config.php)
  header.php / nav.php / footer.php
public/
  index.php                 # Home
  about.php
  services.php
  products.php
  gallery.php
  blog.php                  # index, ?slug= for detail (still one file, simplest per multi-file choice)
  contact.php                # form + POST handler
  get-a-quote.php             # form + POST handler
  css/app.css                # Tailwind build output (gitignored)
resources/css/app.css        # Tailwind source (@tailwind directives)
package.json                # tailwindcss CLI only
tailwind.config.js
```

`blog.php` handles both index and detail via `?slug=` query param rather than a second file — avoids a router while still being one URL family the PRD treats as one screen pair.

## Data

Only the four tables v1 needs, taken directly from `docs/02-database-entity-document.md` §2.8–2.11 (trimmed to public-site-relevant columns, no FKs to the not-yet-built `users`/`leads` tables):

- `products` (name, capacity_kw, description, image_path, price_indicative, is_active)
- `blog_posts` (title, slug, body, cover_image_path, status, published_at) — `author_id` dropped, no user system yet
- `quote_requests` (name, phone, email, address, city, roof_type, monthly_bill_estimate, created_at) — the Get-a-Quote submissions
- `company_settings` (key, value) — contact info shown in header/footer/Contact page

Contact page form also inserts into `quote_requests`-adjacent storage; per the PRD, simplest v1 approach is a second lightweight `contact_messages` table (name, email, message, created_at) rather than overloading `quote_requests` with non-quote fields.

## Out of scope (this pass)

Auth, admin panel, leads/jobs pipeline, technician/customer portal, roles — all deferred. `docs/` remains the reference for if/when those are rebuilt later on this same plain-PHP stack.

## Testing

Per Ponytail: no test framework. One manual smoke check after scaffolding — each page loads, the two forms insert a row and show a confirmation. No automated test harness for a stack this size unless requested later.
