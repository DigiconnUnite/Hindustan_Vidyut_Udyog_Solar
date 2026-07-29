# Database & Entity Document

## Hindustan Vidyut Udyog Solar — MySQL Schema

Minimal schema covering the marketing site's content/leads and the admin
portal's team + job tracking. No over-normalization — kept to what the PRD
scope actually needs.

### Entity Overview

```
users (admin/staff login)
  └─< installation_jobs (assigned_to)
leads
  └── converts to → installation_jobs (nullable link)
installation_jobs
  └─< job_status_history
  └─< job_team_members (many-to-many with users, for multi-tech jobs)
products
services
site_settings (key/value)
```

### 1. `users`

Admin and staff/technician logins for the admin portal.

| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PK | |
| name | VARCHAR(100) | |
| email | VARCHAR(150) UNIQUE | login |
| password_hash | VARCHAR(255) | `password_hash()` output |
| role | ENUM('admin','staff') | |
| phone | VARCHAR(20) NULL | |
| is_active | TINYINT(1) DEFAULT 1 | deactivate instead of delete |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP | |
| updated_at | DATETIME NULL ON UPDATE CURRENT_TIMESTAMP | |

### 2. `leads`

Submissions from the public Contact/Quote form.

| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PK | |
| name | VARCHAR(100) | |
| phone | VARCHAR(20) | |
| email | VARCHAR(150) NULL | |
| address | VARCHAR(255) NULL | |
| message | TEXT NULL | |
| status | ENUM('new','contacted','converted','closed') DEFAULT 'new' | |
| converted_job_id | INT UNSIGNED NULL | FK → installation_jobs.id, set on convert |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP | |

### 3. `installation_jobs`

The core tracking entity for the admin portal.

| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PK | |
| lead_id | INT UNSIGNED NULL | FK → leads.id, nullable (job can be created manually) |
| customer_name | VARCHAR(100) | |
| customer_phone | VARCHAR(20) | |
| address | VARCHAR(255) | |
| system_size_kw | DECIMAL(5,2) NULL | |
| status | ENUM('new','site_survey','approved','installing','completed','cancelled') DEFAULT 'new' | |
| notes | TEXT NULL | |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP | |
| updated_at | DATETIME NULL ON UPDATE CURRENT_TIMESTAMP | |

### 4. `job_status_history`

Audit trail of status changes per job (for the "activity history" shown on
a job's detail screen).

| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PK | |
| job_id | INT UNSIGNED | FK → installation_jobs.id |
| status | ENUM(same as installation_jobs.status) | |
| changed_by | INT UNSIGNED | FK → users.id |
| note | VARCHAR(255) NULL | |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP | |

### 5. `job_team_members`

Join table: which technician(s) are assigned to which job.

| Column | Type | Notes |
|---|---|---|
| job_id | INT UNSIGNED | FK → installation_jobs.id |
| user_id | INT UNSIGNED | FK → users.id |
| assigned_at | DATETIME DEFAULT CURRENT_TIMESTAMP | |

Primary key: (`job_id`, `user_id`)

### 6. `products`

Public product listing (panels, inverters, batteries).

| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PK | |
| name | VARCHAR(150) | |
| category | VARCHAR(50) | e.g. panel, inverter, battery |
| description | TEXT NULL | |
| specs | TEXT NULL | plain text or simple JSON |
| image_path | VARCHAR(255) NULL | |
| is_active | TINYINT(1) DEFAULT 1 | |
| sort_order | INT DEFAULT 0 | |

### 7. `services`

Public services listing.

| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PK | |
| title | VARCHAR(150) | |
| description | TEXT NULL | |
| icon | VARCHAR(50) NULL | icon key used by frontend |
| sort_order | INT DEFAULT 0 | |

### 8. `site_settings`

Simple key/value store for editable site-wide content (phone number,
address, hero stats, social links) so devs don't hardcode them.

| Column | Type | Notes |
|---|---|---|
| `key` | VARCHAR(100) PK | |
| `value` | TEXT NULL | |

### SQL — Create Statements

```sql
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  phone VARCHAR(20) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE leads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(150) NULL,
  address VARCHAR(255) NULL,
  message TEXT NULL,
  status ENUM('new','contacted','converted','closed') NOT NULL DEFAULT 'new',
  converted_job_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE installation_jobs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lead_id INT UNSIGNED NULL,
  customer_name VARCHAR(100) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  address VARCHAR(255) NOT NULL,
  system_size_kw DECIMAL(5,2) NULL,
  status ENUM('new','site_survey','approved','installing','completed','cancelled') NOT NULL DEFAULT 'new',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (lead_id) REFERENCES leads(id)
);

ALTER TABLE leads
  ADD FOREIGN KEY (converted_job_id) REFERENCES installation_jobs(id);

CREATE TABLE job_status_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id INT UNSIGNED NOT NULL,
  status ENUM('new','site_survey','approved','installing','completed','cancelled') NOT NULL,
  changed_by INT UNSIGNED NOT NULL,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (job_id) REFERENCES installation_jobs(id),
  FOREIGN KEY (changed_by) REFERENCES users(id)
);

CREATE TABLE job_team_members (
  job_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (job_id, user_id),
  FOREIGN KEY (job_id) REFERENCES installation_jobs(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(50) NOT NULL,
  description TEXT NULL,
  specs TEXT NULL,
  image_path VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0
);

CREATE TABLE services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT NULL,
  icon VARCHAR(50) NULL,
  sort_order INT NOT NULL DEFAULT 0
);

CREATE TABLE site_settings (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT NULL
);
```

### Notes

- Soft "delete" via `is_active` flags on `users` and `products` — no hard
  deletes needed at this scale.
- `job_status_history` is append-only; current status also duplicated on
  `installation_jobs.status` for fast list-page queries (denormalized on
  purpose — avoids a join on every job list render).
