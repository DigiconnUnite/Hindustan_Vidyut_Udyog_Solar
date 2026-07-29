# Project Requirement Document (PRD)

## Hindustan Vidyut Udyog Solar — Website & Admin Portal

### 1. Overview

Hindustan Vidyut Udyog Solar (HVU Solar) installs residential solar panel
systems. The company needs:

1. A **marketing website** to present the company, its services and
   products, and to capture leads (quote/contact requests).
2. A **minimal internal admin portal** to manage the installation team and
   track the status of installation jobs.

### 2. Tech Stack

- **Backend**: Plain PHP (no framework)
- **Database**: MySQL
- **Frontend styling**: Tailwind CSS
- **Rendering**: Server-rendered HTML (PHP includes/templates), small amount
  of vanilla JS for interactivity (status updates, filters)

No PHP framework, no JS framework, no build-heavy tooling. Keep the stack
boring and easy for a small team to maintain.

### 3. Goals

- Present HVU Solar credibly to homeowners considering solar installation.
- Convert visitors into leads via a quote/contact form.
- Give internal staff one simple place to see: who is on the team, and what
  stage every installation job is at.

### 4. Non-Goals (Out of Scope)

- No customer-facing login/portal (customers do not get accounts).
- No CRM-grade lead pipeline (no multi-stage sales automation).
- No CMS/page builder — content is edited by developers or via a very small
  settings table, not by non-technical staff.
- No analytics dashboards, invoicing, payments, or inventory management.
- No mobile app.

Keep the admin panel intentionally small — team + job tracking only.

### 5. User Roles

| Role | Access |
|---|---|
| **Admin** | Full access to admin portal: manage team members, manage/assign jobs, view leads, edit site content (products/services), manage settings |
| **Staff / Technician** | Login to admin portal, view jobs assigned to them, update job status/notes only |
| **Public visitor** | No login. Browses marketing site, submits contact/quote form |

### 6. Marketing Website — Pages

| Page | Purpose |
|---|---|
| **Home** | Hero, value props, stats (systems installed, years in business, etc.), featured services/products, testimonials, CTA to get a quote |
| **About** | Company story, mission, team photos, certifications |
| **Services** | List of services offered (site survey, installation, maintenance, AMC) with descriptions |
| **Products** | Solar panel / inverter / battery product listing with specs |
| **Gallery** (optional) | Photos of completed installations |
| **Support / Contact** | Contact form (quote request), phone/email/address, map, FAQ |

Every page shares a common header (nav) and footer (contact info, links,
social).

### 7. Admin Portal — Modules (Minimal)

Only two functional modules, plus login and basic content upkeep:

1. **Team Management**
   - List technicians/staff (name, role, phone, active/inactive)
   - Add / edit / deactivate a team member
2. **Installation Job Tracking**
   - List jobs with customer name, address, status, assigned technician(s)
   - Create a job (from a converted lead or manually)
   - Update job status (e.g. New → Site Survey → Approved → Installing →
     Completed)
   - Assign/reassign technician(s) to a job
   - Simple activity/status history per job
3. **Leads / Quote Requests** (read + convert only)
   - View submitted contact/quote form entries
   - Mark as contacted / convert to a job
4. **Content upkeep** (lightweight, admin-only)
   - Edit Products and Services lists shown on the public site

No separate CMS UI, no drag-and-drop builder — plain CRUD forms.

### 8. Success Criteria

- A visitor can learn about HVU Solar and submit a quote request in under 2
  minutes.
- An admin can see all active jobs and their status at a glance from one
  screen.
- An admin can assign a technician to a job in 2 clicks.
- The whole site runs on standard shared PHP/MySQL hosting with no special
  infrastructure.

### 9. Design Direction

Light, clean, minimal aesthetic. Green/amber accent color reflecting solar
and sustainability, generous white space, rounded cards, soft shadows.
Admin dashboard uses a sidebar + stat-card layout (see
[03-ui-ux-screen-document.md](03-ui-ux-screen-document.md) for details).
