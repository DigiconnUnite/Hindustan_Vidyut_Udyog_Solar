# UI/UX Screen Document

## Hindustan Vidyut Udyog Solar — Website & Admin Portal

### 1. Design Direction

Drawn from the attached references (SolarSphere landing page, Eduplex and
DoDo admin dashboards):

- **Palette**: Light background (off-white / very light green tint), one
  strong accent color — solar green (`#2F8F4E`-ish) or amber/gold
  (`#F5A623`-ish) for CTAs and highlights, dark charcoal for headings, not
  pure black.
- **Style**: Soft rounded cards (`rounded-2xl`), subtle shadows, generous
  white space, large friendly headline type on the public site.
- **Public site hero**: Full-width nature/rooftop-solar photo background
  with a translucent/glass stat card overlay (inspired by SolarSphere's
  "30% Reduced Carbon Footprint / 20% Reduced Energy Bills" floating cards).
- **Admin dashboard**: Left sidebar nav (dark or light, icon + label),
  top bar with page title/search, stat cards row, list/table below — same
  structural pattern as the Eduplex/DoDo references, simplified (no course
  or student-specific widgets, just jobs/team/leads stat cards).
- **Typography**: One sans-serif family (e.g. Inter/Poppins via Tailwind
  default stack), bold large headings, comfortable body line-height.

Tailwind config: define `primary` (green) and `accent` (amber) colors once
in `tailwind.config.js`; no other custom design tokens needed at this size.

### 2. Public Marketing Site — Screens

#### 2.1 Header / Footer (shared)
- **Header**: Logo left, nav links (Home, About, Services, Products,
  Support) center/right, "Get a Quote" button (accent color) far right.
  Sticky on scroll. Mobile: hamburger menu.
- **Footer**: Company info + logo, quick links, contact details (phone,
  email, address), social icons, copyright line.

#### 2.2 Home
1. Hero: headline ("Power Your Home With Solar"), subtext, "Get a Free
   Quote" CTA, background image, 2-3 floating stat cards (systems
   installed, years of experience, customer rating).
2. Trust strip: logos/certifications or quick numbers row.
3. Services preview: 3-4 service cards with icon, title, short text, link
   to Services page.
4. Featured products: 3 product cards (panel/inverter/battery) with image,
   name, key spec, "View Products" link.
5. Why choose us: 3-4 value props (quality, warranty, support, pricing) as
   icon + text blocks.
6. Testimonials: carousel or 2-3 static cards with customer quote, name.
7. CTA band: "Ready to go solar?" + quote button, full-width accent-tinted
   section.

#### 2.3 About
1. Hero/banner with page title.
2. Company story (text + image).
3. Mission/values (icon grid, 3-4 items).
4. Team section: photo grid of key team members (name, role) — optional,
   can be simple.
5. Certifications/partners logos row.
6. CTA band linking to Contact.

#### 2.4 Services
1. Page intro.
2. Service list: card or alternating image/text rows per service (Site
   Survey, Installation, Maintenance/AMC, System Upgrade) — pulled from
   `services` table.
3. CTA band → quote form.

#### 2.5 Products
1. Page intro + optional category filter (Panels / Inverters / Batteries)
   — simple client-side JS filter, no backend filtering needed.
2. Product grid: image, name, short spec line, "Enquire" button (scrolls
   to / opens contact form with product pre-filled).
3. Pulled from `products` table.

#### 2.6 Support / Contact
1. Page intro.
2. Two-column layout: contact form (name, phone, email, address, message)
   left; contact details + embedded map + FAQ accordion right.
3. Form submits to `leads` table (see API doc).
4. Success state: inline confirmation message after submit (no page
   reload if using simple fetch, otherwise a redirect-with-flash message).

#### 2.7 Gallery (optional, only if content exists)
- Simple image grid of completed installations, lightbox on click.

### 3. Admin Portal — Screens

All admin screens share: left sidebar (Dashboard, Jobs, Team, Leads,
Products, Services, Logout), top bar with page title and logged-in user
menu.

#### 3.1 Login
- Centered card, logo, email + password fields, "Sign in" button, error
  message on failure. No self-registration — accounts created by an admin.

#### 3.2 Dashboard (landing after login)
- Stat cards row: Active Jobs, New Leads, Team Members, Completed This
  Month.
- Recent jobs table (last 5-10) with status badges.
- Recent leads list (last 5) with quick "Convert to Job" action.

#### 3.3 Jobs — List
- Table: Customer, Address, Status (colored badge), Assigned Technician(s),
  Updated date.
- Filter by status (tabs or dropdown).
- "New Job" button → create form.
- Row click → Job detail.

#### 3.4 Jobs — Detail
- Job info card (customer, address, system size, notes) — editable inline
  or via edit button.
- Status changer: dropdown/buttons to move to next status, adds an entry
  to `job_status_history`.
- Assigned technicians: multi-select / add-remove chips.
- Status history timeline (from `job_status_history`).

#### 3.5 Jobs — Create
- Simple form: customer name, phone, address, system size, initial status,
  optional link to originating lead.

#### 3.6 Team — List
- Table: Name, Role, Phone, Status (active/inactive), Actions (edit,
  deactivate).
- "Add Team Member" button.

#### 3.7 Team — Add/Edit
- Form: name, email, phone, role (admin/staff), password (on create only),
  active toggle.

#### 3.8 Leads — List
- Table: Name, Phone, Message preview, Status, Date.
- Row actions: Mark contacted, Convert to Job (pre-fills Job create form).

#### 3.9 Products / Services — List + Edit
- Simple table + add/edit form per entity (name/title, description,
  image upload for products, active toggle, sort order). Minimal CRUD,
  no rich content editor.

### 4. Component Inventory (shared, keep small)

- Button (primary/accent, outline)
- Input, Textarea, Select (Tailwind form styles)
- Card (white, rounded-2xl, shadow-sm)
- Status badge (color-coded by status)
- Sidebar nav item
- Stat card (icon, number, label)
- Table (striped rows, sticky header)
- Alert/flash message (success/error)

### 5. Responsive Notes

- Public site: mobile-first, hamburger nav, stacked hero on mobile, product
  grid collapses to 1-2 columns.
- Admin portal: sidebar collapses to icon-only or off-canvas drawer under
  md breakpoint; tables scroll horizontally on small screens rather than
  reflowing into cards (keeps implementation simple).
