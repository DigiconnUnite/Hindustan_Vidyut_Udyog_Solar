# Database migrations

Apply in order against the `hvu_solar` database:

```bash
mysql -u root hvu_solar < database/schema.sql
mysql --default-character-set=utf8mb4 -u root hvu_solar < database/migration-002-catalog.sql
mysql --default-character-set=utf8mb4 -u root hvu_solar < database/migration-003-seed.sql
```

`--default-character-set=utf8mb4` matters on 003 — the seed contains the rupee sign
and en-dashes, which mangle under the client's default latin1.

All three are idempotent, so re-running is safe.

- **schema.sql** — original tables (users, leads, jobs, products, services, settings).
- **002-catalog.sql** — adds price/brand/wattage/warranty/rating to `products`,
  source/system_kw/monthly_bill to `leads`, and creates `solar_kits`, `projects`,
  `certifications`, `newsletter_subscribers`, `product_reviews`, `service_areas`,
  `job_openings`.
- **003-seed.sql** — indicative pricing and demo content for the new tables.
  Replace the prices with the real rate card before going live.
