# API & Backend Contract Document

## Hindustan Vidyut Udyog Solar — Backend Contract

The site is server-rendered plain PHP — most "endpoints" are page routes
that render HTML, plus a small number of JSON endpoints for the bits of
admin UI that need AJAX (status change, technician assign) without a full
page reload. No REST framework, no SPA.

### 1. Routing Convention

No router library — plain PHP files at the project root, mapped 1:1 to
URLs via the webserver. The project root is the document root (see
[05-deployment-environment-document.md](05-deployment-environment-document.md)),
so `index.php`, `about.php`, etc. map directly to their URL paths.

```
/                     -> index.php                (Home)
/about.php            -> About
/services.php         -> Services
/products.php         -> Products
/contact.php          -> Support/Contact (GET form, POST submit)
/admin/login.php      -> Admin login (GET form, POST submit)
/admin/logout.php     -> Destroys session, redirect to login
/admin/index.php      -> Dashboard
/admin/jobs.php        -> Jobs list (GET), create (POST)
/admin/job.php?id=1   -> Job detail (GET), update (POST)
/admin/team.php       -> Team list (GET), create (POST)
/admin/team.php?id=1  -> Edit team member (GET/POST)
/admin/leads.php      -> Leads list (GET), status update (POST)
/admin/products.php   -> Products CRUD
/admin/services.php   -> Services CRUD
```

### 2. Auth & Sessions

- PHP native `session_start()`; on successful login store
  `$_SESSION['user_id']` and `$_SESSION['role']`.
- Every `/admin/*` file (except `login.php`) starts with an auth guard
  include that redirects to `/admin/login.php` if no session.
- Role check: pages/actions restricted to `admin` (e.g. Team management,
  Products/Services edit) check `$_SESSION['role'] === 'admin'`; `staff`
  can view jobs and update status/notes on jobs assigned to them only.
- Passwords stored with `password_hash()`, verified with
  `password_verify()`.
- CSRF: one shared token per session (`$_SESSION['csrf']`), rendered as a
  hidden field in every admin form, checked on POST.

### 3. Public Site Contracts

#### 3.1 Submit Contact / Quote Request
`POST /contact.php`

Request (form-encoded):
| Field | Required | Notes |
|---|---|---|
| name | yes | |
| phone | yes | |
| email | no | |
| address | no | |
| message | no | |

Server behavior: validate required fields server-side, insert into
`leads` (status `new`), redirect back to `/contact.php?sent=1` (POST-
redirect-GET) which renders a success message.

Response: HTML redirect + flash message. No JSON needed here.

#### 3.2 Product / Service Listing
`GET /products.php`, `GET /services.php`

Reads active rows from `products` / `services` ordered by `sort_order`,
renders server-side. No API needed — plain DB read in the page.

### 4. Admin Portal Contracts

#### 4.1 Login
`POST /admin/login.php`

| Field | Required |
|---|---|
| email | yes |
| password | yes |

Success: set session, redirect to `/admin/index.php`.
Failure: redirect back with `?error=1`, page shows generic "invalid
credentials" message (never reveal which field was wrong).

#### 4.2 Jobs List
`GET /admin/jobs.php?status=installing` — optional status filter,
server-rendered table.

#### 4.3 Create Job
`POST /admin/jobs.php`

| Field | Required | Notes |
|---|---|---|
| lead_id | no | if converting from a lead |
| customer_name | yes | |
| customer_phone | yes | |
| address | yes | |
| system_size_kw | no | |
| status | no | defaults to `new` |

On success: insert into `installation_jobs`, insert initial row into
`job_status_history`, redirect to `/admin/job.php?id={new_id}`.

#### 4.4 Update Job Status (AJAX)
`POST /admin/api/job-status.php` — returns JSON, called via `fetch()` from
the job detail page so the status can update without a full reload.

Request (JSON or form-encoded):
```json
{ "job_id": 12, "status": "installing", "note": "Panels delivered" }
```

Response:
```json
{ "ok": true, "status": "installing", "updated_at": "2026-07-29 10:15:00" }
```
Error response:
```json
{ "ok": false, "error": "Invalid status transition" }
```

Server behavior: validate `job_id` exists and caller has permission
(admin, or staff assigned to that job), update
`installation_jobs.status`, insert a `job_status_history` row with
`changed_by = $_SESSION['user_id']`.

#### 4.5 Assign Technician (AJAX)
`POST /admin/api/job-assign.php`

Request:
```json
{ "job_id": 12, "user_id": 4, "action": "add" }
```
(`action`: `"add"` or `"remove"`)

Response:
```json
{ "ok": true }
```

Server behavior: insert/delete row in `job_team_members`. Admin-only.

#### 4.6 Team CRUD
`GET/POST /admin/team.php` (list + create), `GET/POST /admin/team.php?id=`
(edit). Standard form posts, admin-only. Deactivate = POST setting
`is_active = 0`, not a hard delete.

#### 4.7 Leads
`GET /admin/leads.php` — list with status filter.
`POST /admin/leads.php` (action=mark_contacted | convert):
- `mark_contacted`: sets `leads.status = 'contacted'`.
- `convert`: creates a row in `installation_jobs` from the lead's data,
  sets `leads.status = 'converted'` and `leads.converted_job_id`.

#### 4.8 Products / Services CRUD
`GET/POST /admin/products.php`, `/admin/products.php?id=`,
`/admin/services.php`, `/admin/services.php?id=` — standard form-based
CRUD, admin-only. Product image upload: `multipart/form-data`, stored
under `/public/uploads/products/`, path saved to `products.image_path`.

### 5. Validation & Error Conventions

- Server-side validation always required (never trust client JS alone).
- Form posts: on validation error, re-render the same page with the
  submitted values and an error list (no separate error page).
- AJAX endpoints: always return JSON with an `ok` boolean; HTTP 200 for
  handled errors (e.g. validation), HTTP 401/403 for auth failures, HTTP
  500 only for unexpected server errors.

### 6. What This Is Not

- Not a public REST API — no third-party or mobile app consumes these
  endpoints, so no API versioning, no API keys, no OpenAPI spec needed.
- The two `/admin/api/*.php` JSON endpoints exist purely to support
  in-page AJAX interactions on the Job detail screen — nothing more.
