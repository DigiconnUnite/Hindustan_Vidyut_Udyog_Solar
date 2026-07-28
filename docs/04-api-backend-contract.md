# API & Backend Contract Document
## Hindustan Vidyut Udyog — Solar Installation Platform

**Version:** 1.0
**Companion to:** [PRD](01-PRD.md) · [Database & Entity Document](02-database-entity-document.md) · [UI/UX Screen Document](03-ui-ux-screen-document.md)

This is a Blade + Livewire monolith, not a decoupled SPA. "API" here covers two layers:

1. **Route contract** — every controller/Livewire route, grouped by access level, with middleware.
2. **JSON endpoint contract** — the small number of routes hit via `fetch`/JS (public quote form, file upload progress, stage update), documented request/response shape, plus the Livewire component contract that acts as the internal API between Blade views and backend logic.

Entity, field, and enum names match the [Database & Entity Document](02-database-entity-document.md) exactly.

---

## 1. Route Map

Middleware shorthand: `auth` = logged in, `role:x,y` = restricted to listed roles, `guest` = not logged in.

### 1.1 Public routes (no auth)

| Method | Path | Handler | Purpose |
|---|---|---|---|
| GET | `/` | `HomeController@index` | Home page |
| GET | `/about` | `PageController@about` | About page |
| GET | `/services` | `PageController@services` | Services page |
| GET | `/products` | `ProductController@publicIndex` | Products listing (active only) |
| GET | `/gallery` | `PageController@gallery` | Gallery |
| GET | `/blog` | `BlogController@publicIndex` | Blog listing (published only) |
| GET | `/blog/{slug}` | `BlogController@publicShow` | Blog post detail |
| GET | `/contact` | `PageController@contact` | Contact page |
| POST | `/contact` | `ContactController@store` | Contact form submission |
| GET | `/get-a-quote` | `QuoteController@create` | Quote request form |
| POST | `/quote-request` | `QuoteController@store` | Submit quote request (JSON/fetch, see §2.1) |
| GET | `/login` | `Auth\LoginController@show` | `guest` | Login form |
| POST | `/login` | `Auth\LoginController@login` | `guest` | Authenticate |
| GET | `/register` | `Auth\RegisterController@show` | `guest` | Customer self-registration form |
| POST | `/register` | `Auth\RegisterController@store` | `guest` | Creates `users` row with `role = customer` |
| POST | `/logout` | `Auth\LoginController@logout` | `auth` | Logout |
| GET/POST | `/forgot-password`, `/reset-password/{token}` | Breeze defaults | `guest` | Password reset flow |

### 1.2 Post-login redirect

On successful login, `LoginController` redirects by `users.role`:

| role | redirect |
|---|---|
| admin | `/admin/dashboard` |
| staff | `/admin/dashboard` |
| technician | `/technician/jobs` |
| customer | `/portal/installation` |

### 1.3 Admin & Staff routes — prefix `/admin`, middleware `auth`, `role:admin,staff` (unless noted)

| Method | Path | Handler | Roles | Purpose |
|---|---|---|---|---|
| GET | `/admin/dashboard` | `Admin\DashboardController@index` | admin, staff | KPI dashboard |
| GET | `/admin/leads` | `Admin\LeadController@index` | admin, staff | Lead list (Livewire `LeadTable`) |
| GET | `/admin/leads/create` | `Admin\LeadController@create` | admin, staff | Manual lead entry form |
| POST | `/admin/leads` | `Admin\LeadController@store` | admin, staff | Create lead |
| GET | `/admin/leads/{lead}` | `Admin\LeadController@show` | admin, staff | Lead detail |
| PATCH | `/admin/leads/{lead}` | `Admin\LeadController@update` | admin, staff | Update status/assignment/notes |
| POST | `/admin/leads/{lead}/convert` | `Admin\LeadController@convert` | admin, staff | Convert to `installation_jobs` (+ customer user if needed) |
| GET | `/admin/customers` | `Admin\CustomerController@index` | admin, staff | Customer list |
| GET | `/admin/customers/{user}` | `Admin\CustomerController@show` | admin, staff | Customer detail |
| PATCH | `/admin/customers/{user}` | `Admin\CustomerController@update` | admin, staff | Edit contact info |
| GET | `/admin/jobs` | `Admin\JobController@index` | admin, staff | Job list/board (Livewire `JobPipelineBoard`) |
| POST | `/admin/jobs` | `Admin\JobController@store` | admin, staff | Create job directly (no lead) |
| GET | `/admin/jobs/{job}` | `Admin\JobController@show` | admin, staff | Job detail |
| PATCH | `/admin/jobs/{job}/stage` | `Admin\JobController@updateStage` | admin, staff (+ technician, see §1.4) | Advance/change stage — writes `job_stage_history` (see §2.3) |
| POST | `/admin/jobs/{job}/team` | `Admin\JobController@assignTeam` | admin, staff | Add a `job_team_assignments` row |
| DELETE | `/admin/jobs/{job}/team/{assignment}` | `Admin\JobController@removeTeam` | admin, staff | Sets `removed_at` |
| POST | `/admin/jobs/{job}/documents` | `Admin\JobController@uploadDocument` | admin, staff (+ technician) | Upload document/photo (see §2.2) |
| GET | `/admin/team` | `Admin\TeamController@index` | admin, staff | Technician/staff roster + workload |
| GET | `/admin/blog` | `Admin\BlogController@index` | admin, staff | Blog post list |
| GET/POST | `/admin/blog/create` | `Admin\BlogController@create/@store` | admin, staff | New post |
| GET/PATCH | `/admin/blog/{post}/edit` | `Admin\BlogController@edit/@update` | admin, staff | Edit post |
| DELETE | `/admin/blog/{post}` | `Admin\BlogController@destroy` | admin, staff | Delete post |
| GET | `/admin/products` | `Admin\ProductController@index` | admin, staff | Product list |
| GET/POST | `/admin/products/create` | `Admin\ProductController@create/@store` | admin, staff | New product |
| GET/PATCH | `/admin/products/{product}/edit` | `Admin\ProductController@edit/@update` | admin, staff | Edit product |
| GET | `/admin/users` | `Admin\UserController@index` | **admin only** | User & role management |
| POST | `/admin/users` | `Admin\UserController@store` | **admin only** | Create staff/technician account |
| PATCH | `/admin/users/{user}` | `Admin\UserController@update` | **admin only** | Edit role/status |
| GET/PATCH | `/admin/settings` | `Admin\SettingsController@edit/@update` | **admin only** | `company_settings` |

### 1.4 Technician routes — prefix `/technician`, middleware `auth`, `role:technician`

| Method | Path | Handler | Purpose |
|---|---|---|---|
| GET | `/technician/jobs` | `Technician\JobController@index` | Jobs where technician is actively assigned |
| GET | `/technician/jobs/{job}` | `Technician\JobController@show` | Job detail (reduced view — see UI doc §4.2); **policy enforces the job must include this technician** |
| PATCH | `/admin/jobs/{job}/stage` | *(shared with §1.3)* | Technician may call this only for permitted transitions (see §5 Authorization Matrix); policy checks assignment + allowed stage move |
| POST | `/admin/jobs/{job}/documents` | *(shared with §1.3)* | Technician may upload only to jobs they're assigned to |

### 1.5 Customer portal routes — prefix `/portal`, middleware `auth`, `role:customer`

| Method | Path | Handler | Purpose |
|---|---|---|---|
| GET | `/portal/installation` | `Portal\InstallationController@index` | Own job(s) status/timeline |
| GET | `/portal/documents` | `Portal\DocumentController@index` | Own job documents |
| GET/PATCH | `/portal/profile` | `Portal\ProfileController@edit/@update` | Own profile |

---

## 2. JSON Endpoint Contracts

These are the routes realistically called via `fetch`/JS rather than a full Blade page load (public form with client-side validation feedback, file upload with progress, Livewire-adjacent AJAX actions).

### 2.1 `POST /quote-request` (public)

**Request:**
```json
{
  "name": "Rakesh Sharma",
  "phone": "9812345678",
  "email": "rakesh@example.com",
  "address": "123 MG Road",
  "city": "Pune",
  "roof_type": "rcc",
  "monthly_bill_estimate": 3500
}
```

**Validation:**
- `name`: required, string, max 255
- `phone`: required, string, regex 10-digit Indian mobile
- `email`: nullable, valid email
- `address`, `city`: nullable, string, max 255
- `roof_type`: nullable, in `rcc,tin_shed,tiled,other`
- `monthly_bill_estimate`: nullable, numeric, min 0
- Rate limit: 5 requests/hour per IP (spam mitigation, per PRD NFR-3)

**Success (201):**
```json
{ "success": true, "message": "Thanks! We'll contact you within 24 hours." }
```

**Error (422):**
```json
{ "success": false, "errors": { "phone": ["The phone field is required."] } }
```

**Side effect:** Inserts `quote_requests` row → inserts matching `leads` row (`source = website`, `status = new`) → queues an email notification to Staff/Admin (FR-24).

### 2.2 `POST /admin/jobs/{job}/documents` (auth: admin, staff, technician-if-assigned)

**Request:** `multipart/form-data`
```
file: <binary>
document_type: "site_photo" | "agreement" | "subsidy_paper" | "completion_photo" | "other"
description: "optional string"
```

**Validation:**
- `file`: required, mimes `jpg,jpeg,png,pdf`, max 10MB
- `document_type`: required, in enum (matches §4 of Database doc)

**Success (201):**
```json
{
  "success": true,
  "document": {
    "id": 42,
    "file_path": "/storage/jobs/204/site_photo_1.jpg",
    "document_type": "site_photo",
    "uploaded_by": "Ravi Kumar",
    "uploaded_at": "2026-07-28T10:15:00+05:30"
  }
}
```

**Error (422/403):** standard validation error shape, or `{ "success": false, "message": "You are not assigned to this job." }` for policy denial.

### 2.3 `PATCH /admin/jobs/{job}/stage` (auth: admin, staff, technician-if-permitted)

**Request:**
```json
{ "stage": "installation", "notes": "Panels mounted, wiring pending" }
```

**Validation:**
- `stage`: required, in canonical pipeline enum (`lead,site_survey,quotation,agreement,installation,inspection,handover`)
- Must be the current stage's immediate next stage, or any forward stage if performed by admin/staff (technician restricted to sequential moves only — see §5)
- `notes`: nullable, string, max 1000

**Success (200):**
```json
{
  "success": true,
  "job": { "id": 204, "current_stage": "installation" },
  "history_entry": { "stage": "installation", "changed_by": "Ravi Kumar", "changed_at": "2026-07-28T10:16:00+05:30" }
}
```

**Side effect:** Inserts `job_stage_history` row, updates `installation_jobs.current_stage`, queues customer notification email (FR-25). If new stage is `handover`, also sets `installation_jobs.actual_completion_date`.

**Error (422):**
```json
{ "success": false, "message": "Cannot skip from Site Survey directly to Installation." }
```

---

## 3. Livewire Component Contract

Internal "API" between Blade views and backend logic — documented so frontend/backend work stays in sync.

| Component | Public properties | Emits | Purpose |
|---|---|---|---|
| `LeadTable` | `search`, `statusFilter`, `sourceFilter`, `assignedFilter` | — | Filterable/searchable lead list (§1.3 leads index) |
| `LeadStatusUpdater` | `lead`, `status` | `lead-updated` | Inline status change on Lead Detail |
| `JobPipelineBoard` | `stageFilter`, `technicianFilter` | `job-stage-changed` | Kanban/list view of jobs, drag-or-click stage advance |
| `JobStageTimeline` | `job` (readonly) | — | Renders the 7-stage stepper (used in both Admin and Customer portal, with a `readonly` prop toggling the advance control) |
| `DocumentUploader` | `job`, `documentType` | `document-uploaded` | Drag/drop or picker upload, shows progress, refreshes document list on success |
| `TeamAssigner` | `job`, `selectedUserId`, `roleOnJob` | `team-updated` | Assign/remove technician or supervising staff on Job Detail |
| `BlogEditor` | `post`, `title`, `slug`, `body`, `status` | `post-saved` | Create/edit blog post with slug auto-generation |
| `ProductEditor` | `product`, `name`, `capacityKw`, `priceIndicative`, `isActive` | `product-saved` | Create/edit product |

**Convention:** every mutating Livewire action flashes a session success/error message (`session()->flash('status', ...)`) in addition to any emitted event, so server-rendered fallback (JS disabled) still communicates outcome.

---

## 4. Notification Events

| Trigger | Recipient | Channel | Queued? |
|---|---|---|---|
| New quote request / lead created | Staff (assigned, or all staff if unassigned) | Email | Yes — `database` queue |
| Lead converted to job | Assigned staff | Email | Yes |
| Job stage changed | Customer | Email | Yes |
| Technician assigned to job | Technician | Email | Yes |
| New staff/technician account created | New user (with temp password / set-password link) | Email | Yes |

All notifications implement Laravel's `ShouldQueue` and run via the `database` queue driver, drained by cron per PRD NFR-4:

```
* * * * * php /home/user/app/artisan schedule:run >> /dev/null 2>&1
```
with the scheduler (`routes/console.php` or `App\Console\Kernel`) running `queue:work --stop-when-empty --max-time=50` every minute.

---

## 5. Authorization Matrix

| Action | Admin | Staff | Technician | Customer |
|---|:---:|:---:|:---:|:---:|
| Submit quote request | ✅ (public) | ✅ | ✅ | ✅ |
| View all leads | ✅ | ✅ | ❌ | ❌ |
| Create/edit lead | ✅ | ✅ | ❌ | ❌ |
| Convert lead to job | ✅ | ✅ | ❌ | ❌ |
| View all customers | ✅ | ✅ | ❌ | ❌ |
| View all installation jobs | ✅ | ✅ | ❌ (own only) | ❌ |
| View own installation job | ✅ | ✅ | ✅ (if assigned) | ✅ (own only) |
| Create job directly | ✅ | ✅ | ❌ | ❌ |
| Advance job stage (any transition) | ✅ | ✅ | ❌ | ❌ |
| Advance job stage (sequential, own assigned job only) | ✅ | ✅ | ✅ | ❌ |
| Assign/remove team on job | ✅ | ✅ | ❌ | ❌ |
| Upload document to job | ✅ | ✅ | ✅ (own assigned job only) | ❌ |
| View documents on job | ✅ | ✅ | ✅ (own assigned job only) | ✅ (own job only) |
| Delete document | ✅ | ✅ | ❌ | ❌ |
| Publish/edit blog post | ✅ | ✅ | ❌ | ❌ |
| Manage products | ✅ | ✅ | ❌ | ❌ |
| Manage users & roles | ✅ | ❌ | ❌ | ❌ |
| Edit company settings | ✅ | ❌ | ❌ | ❌ |
| Edit own profile | ✅ | ✅ | ✅ | ✅ |

Implemented via Laravel **Policies** (`LeadPolicy`, `InstallationJobPolicy`, `JobDocumentPolicy`, `BlogPostPolicy`, `ProductPolicy`, `UserPolicy`) registered against the `role` column, plus an explicit assignment check (`job_team_assignments`) for technician-scoped actions. Every route in §1.3–1.5 is additionally wrapped in `role:` middleware as a first line of defense — policies are the authoritative check inside controllers/Livewire actions (defense in depth, per PRD NFR-3).

---

## 6. Validation Rules Summary

| Form | Key rules |
|---|---|
| Get a Quote / Contact | name required; phone required, 10-digit; email optional valid; rate-limited 5/hour/IP |
| Manual Lead entry | same as above, plus `source` required (defaults `phone` or `walk_in`) |
| Lead status update | `status` required, in enum |
| Job creation | `customer_id` required + must be role=customer; `address` required; `system_capacity_kw` nullable numeric |
| Stage update | `stage` required, in enum, must be a valid forward transition per §2.3 |
| Team assignment | `user_id` required, must be role in (staff, technician); `role_on_job` required, in enum |
| Document upload | `file` required, mimes jpg/jpeg/png/pdf, max 10MB; `document_type` required, in enum |
| Blog post | `title` required max 255; `slug` unique, auto-generated from title (editable); `body` required |
| Product | `name` required; `capacity_kw` required numeric > 0 |
| User creation | `email` required unique; `role` required, in enum |

---

## 7. Error Handling Conventions

- **Livewire actions:** validation errors render inline under fields (Livewire's native `$this->validate()` error bag); success feedback via flashed `status` session message shown as a dismissible Tailwind banner at the top of the panel.
- **JSON/fetch endpoints (§2):** consistent envelope —
  - Success: `{ "success": true, ...data }`
  - Validation error (422): `{ "success": false, "errors": { field: [messages] } }`
  - Authorization error (403): `{ "success": false, "message": "..." }`
  - Not found (404): `{ "success": false, "message": "Not found." }`
- **Server errors (500):** generic `{ "success": false, "message": "Something went wrong. Please try again." }` — details logged server-side only, never exposed in the response (security: no stack traces to client).
