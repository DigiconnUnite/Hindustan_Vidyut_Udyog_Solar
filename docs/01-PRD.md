# Project Requirement Document (PRD)
## Hindustan Vidyut Udyog — Solar Installation Website & Admin Platform

**Version:** 1.0
**Status:** Draft for build
**Owner:** Hindustan Vidyut Udyog

---

## 1. Overview

Hindustan Vidyut Udyog installs residential rooftop solar power systems. This project delivers a single web platform with three faces:

1. **Public marketing website** — attracts visitors, explains services/products, captures quote requests.
2. **Admin panel** — internal tool for Admin, Sales/Office Staff, and Technicians to manage leads, customers, and installation jobs from first contact to handover.
3. **Customer portal** — lets an existing customer log in and track their own installation's progress.

All three are one Laravel application with role-based access — not three separate systems.

### 1.1 Business context

- Residential solar installation is a multi-week process with distinct physical stages (site survey, government paperwork, panel installation, inspection). Today this is presumably tracked informally (calls, spreadsheets, WhatsApp). The platform's core value is turning that into a trackable pipeline visible to staff, technicians, and the customer.
- Leads arrive via the public website's quote form (and can also be entered manually by staff from phone/walk-in enquiries).

### 1.2 Goals

- Give visitors a fast, trustworthy, mobile-friendly site that converts interest into a quote request.
- Give staff one place to manage leads and convert them into tracked installation jobs.
- Give technicians a simple, focused view of only their assigned jobs, with the ability to update progress and upload site photos from the field.
- Give customers visibility into their own installation without needing to call the office.
- Run reliably on affordable shared (cPanel-style) hosting — no dependency on Redis, dedicated queue workers, or SSH-only tooling.

### 1.3 Out of scope for v1

- Payments, invoicing, GST billing, or any online payment gateway.
- Multi-language support (English only for v1).
- SMS gateway / WhatsApp API integration (email notifications only for v1; noted as a future enhancement).
- Native mobile apps (site is responsive web only).
- Real-time chat/support widget.

---

## 2. Roles & Stakeholders

| Role | Who | Primary purpose |
|---|---|---|
| **Admin** | Business owner / manager | Full access: all leads, jobs, customers, team, content, settings, user management. |
| **Staff** | Sales / office employees | Manage leads, convert to jobs, assign technicians, manage customers, update job stages, manage blog/products content. |
| **Technician** | Field installation crew | View only their assigned jobs, update job stage/progress, upload site photos and documents. No access to leads, other customers, or settings. |
| **Customer** | Homeowner who has requested/purchased an installation | View their own installation's status, timeline, and documents. Read-only. |
| **Visitor** (unauthenticated) | Public website visitor | Browse marketing pages, submit a quote request, read blog. |

Role is stored as a single field per user (see [Database & Entity Document](02-database-entity-document.md)); access is enforced via middleware/policies (see [API & Backend Contract Document](04-api-backend-contract.md)).

---

## 3. Scope

### 3.1 Public website

Pages:
- **Home** — hero, services summary, why-us, featured products, testimonials, CTA to get a quote.
- **About** — company background, mission, certifications/experience.
- **Services** — residential solar installation process explained.
- **Products** — panel/system capacity options (e.g. 1kW, 3kW, 5kW, 10kW residential systems) with indicative details.
- **Gallery** — photos of completed installations.
- **Blog** — articles (solar savings, subsidies/government schemes, maintenance tips) for SEO and trust-building.
- **Contact** — company contact details, address, map, contact form.
- **Get a Quote** — lead capture form (name, phone, email, address, city, roof type, approximate monthly electricity bill/consumption) → creates a Lead in admin.
- **Login / Register** — shared login page; registration is for customers only (staff/technician/admin accounts are created by Admin, not self-registered).

### 3.2 Admin panel (Admin + Staff, with Technician getting a reduced view)

- **Dashboard** — KPI summary: new leads (this month), jobs by stage, upcoming installations, recently completed jobs.
- **Lead management** — list, filter, detail view, manual lead entry, convert lead → installation job.
- **Customer management** — list, detail (linked jobs, documents, contact info).
- **Installation job / pipeline tracking** — list filterable by stage, detail view showing stage timeline, assigned team, uploaded documents, and a control to advance/change stage.
- **Team management** — list of staff/technicians, assign technician(s) to a job.
- **Document management** — upload/view documents and photos per job, categorized by type.
- **Blog / content management** — create/edit/publish blog posts.
- **Product management** — create/edit panel/system offerings shown on the public Products page.
- **User & role management** (Admin only) — create staff/technician accounts, assign roles, deactivate users.
- **Settings** — company profile (contact info, address, social links), SEO defaults.

### 3.3 Technician view (subset of admin panel)

- "My Jobs" — jobs where the technician is assigned.
- Job detail — stage timeline, ability to advance stage (within permitted transitions), upload site/completion photos.
- No visibility into leads, customers not on their jobs, other technicians' jobs, blog, products, settings, or user management.

### 3.4 Customer portal

- **My Installation** — current stage, visual timeline of stages completed/pending, assigned technician's name (not contact-editable), notes visible to customer.
- **My Documents** — documents/photos uploaded to their job (site survey photos, agreement copy, completion photos).
- **Profile** — view/edit own contact details, change password.

---

## 4. Installation Pipeline (core domain concept)

Every installation job moves through a **fixed, ordered set of stages**:

```
Lead → Site Survey → Quotation → Agreement → Installation → Inspection → Handover
```

- Stage changes are logged with a timestamp and the staff/technician who made the change (audit trail).
- A job is always at exactly one current stage; history of all past stage transitions is retained.
- Stage names and order are identical across PRD, database, UI, and API docs — see the canonical enum in [Database & Entity Document §4](02-database-entity-document.md).

---

## 5. Functional Requirements

### 5.1 Authentication & Authorization
- FR-1: Users log in with email + password (Laravel Breeze-based scaffolding).
- FR-2: Customers can self-register; Admin/Staff/Technician accounts are created only by an Admin.
- FR-3: Each authenticated user is redirected to a role-appropriate dashboard after login.
- FR-4: All admin/staff/technician/customer routes are protected by role middleware; unauthorized access returns 403.
- FR-5: Passwords are hashed (bcrypt/argon2 via Laravel default); password reset via emailed link.

### 5.2 Lead Management
- FR-6: Public quote form submissions create a Lead record without requiring login.
- FR-7: Staff can manually create a Lead (phone/walk-in enquiries).
- FR-8: Staff can filter/search leads by status, source, and date.
- FR-9: Staff can convert a Lead into an Installation Job and (if not already a user) create a linked Customer account.

### 5.3 Customer Management
- FR-10: Staff/Admin can view a list of customers with linked jobs.
- FR-11: Customer detail view shows contact info, all jobs, and job documents.

### 5.4 Installation Job / Pipeline
- FR-12: Staff/Admin can create a job (directly or via lead conversion).
- FR-13: Staff/Admin/Technician (per permission) can advance a job's stage; each change is recorded in stage history.
- FR-14: Staff/Admin can assign one or more technicians and a responsible staff member to a job.
- FR-15: Job list is filterable by current stage, assigned technician, and date range.
- FR-16: Job detail displays the full stage timeline with timestamps and who made each change.

### 5.5 Team Assignment
- FR-17: Admin/Staff can view all technicians and their current job load (count of active jobs).
- FR-18: Assigning/removing a technician from a job is logged.

### 5.6 Documents
- FR-19: Staff/Technician can upload documents/photos to a job, tagged by type (site photo, agreement, subsidy paperwork, completion photo, other).
- FR-20: Uploaded files are validated for type (images/PDF) and size before storage.
- FR-21: Customers can view (not delete) documents attached to their own job.

### 5.7 Content (Blog & Products)
- FR-22: Staff/Admin can create, edit, publish/unpublish blog posts.
- FR-23: Staff/Admin can create/edit product entries (panel/system options) shown publicly.

### 5.8 Notifications
- FR-24: An email is sent to Staff/Admin when a new lead is submitted via the public form.
- FR-25: An email is sent to the Customer when their job's stage changes.
- FR-26: All notification emails are queued (database queue driver) rather than sent synchronously, to avoid blocking page responses.

### 5.9 User & Role Management
- FR-27: Admin can create, edit, deactivate staff/technician/customer accounts and change roles.

---

## 6. Non-Functional Requirements

- **NFR-1 Performance:** Public pages should be usable on 3G/4G mobile connections common in Tier-2/3 Indian cities; use optimized images and minimal JS payload.
- **NFR-2 Responsive design:** Mobile-first; a majority of quote-form traffic is expected from phones. Admin panel must be usable on tablets (technicians in the field).
- **NFR-3 Security:** RBAC enforced server-side on every route (not just hidden UI); CSRF protection (Laravel default); file upload MIME/size validation; rate-limiting on the public quote form to deter spam/bots.
- **NFR-4 Hosting constraints:** Must run on shared cPanel-style hosting — MySQL database, no Redis, no long-running processes. Background work uses Laravel's `database` queue driver drained by a cPanel Cron Job running `php artisan schedule:run` every minute, which in turn runs `queue:work --stop-when-empty`.
- **NFR-5 SEO:** Public pages have editable meta title/description; clean slugs for blog posts; sitemap.xml generation.
- **NFR-6 Backups:** Database and uploaded files (storage) should be included in the hosting provider's/cPanel's regular backup routine (operational note, not application code).
- **NFR-7 Auditability:** Stage changes, document uploads, and team assignments are timestamped and attributed to a user — no silent/anonymous changes.

---

## 7. Tech Stack

| Layer | Choice |
|---|---|
| Backend framework | Laravel (latest stable) |
| Frontend templating | Blade |
| Interactivity | Livewire |
| CSS | Tailwind CSS |
| Auth scaffolding | Laravel Breeze (Blade + Livewire stack) |
| Database | MySQL (as provided by shared hosting) |
| Queue | Laravel `database` driver, cron-drained |
| File storage | Local disk (`storage/app/public`) via Laravel filesystem, symlinked to `public/storage` |
| Hosting target | Shared cPanel hosting (no Redis, no SSH-only assumptions) |

---

## 8. Assumptions & Constraints

- Hosting provider offers cPanel with Cron Jobs, a MySQL database, and standard PHP (Laravel-compatible version). No guaranteed SSH access — deployment docs (future, not part of this doc set) should assume Git deploy or zip upload + `composer install` via cPanel's terminal/Softaculous if available.
- No Redis/Memcached available — sessions and cache use `database`/`file` drivers, not `redis`.
- Email sending uses SMTP (e.g. cPanel mail or a transactional provider) configured via `.env`.
- Single business (one company, one set of teams) — no multi-tenancy needed.

---

## 9. Success Criteria (v1 "done")

- A visitor can browse the public site on mobile and desktop and submit a quote request successfully.
- A submitted quote request appears as a Lead in the admin panel and triggers a staff notification.
- Staff can convert a Lead into an Installation Job, assign a technician, and the job appears correctly in the pipeline.
- A technician can log in, see only their assigned job(s), advance the stage, and upload a photo.
- A customer can log in and see their job's current stage and uploaded documents, matching what staff/technician recorded.
- All of the above work correctly on shared cPanel hosting with the cron-driven queue, with no Redis dependency.
