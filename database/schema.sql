-- Hindustan Vidyut Udyog Solar — database schema
-- See docs/02-database-entity-document.md for the full entity writeup.

CREATE TABLE IF NOT EXISTS users (
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

CREATE TABLE IF NOT EXISTS leads (
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

CREATE TABLE IF NOT EXISTS installation_jobs (
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
  ADD CONSTRAINT fk_leads_converted_job
  FOREIGN KEY (converted_job_id) REFERENCES installation_jobs(id);

CREATE TABLE IF NOT EXISTS job_status_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id INT UNSIGNED NOT NULL,
  status ENUM('new','site_survey','approved','installing','completed','cancelled') NOT NULL,
  changed_by INT UNSIGNED NOT NULL,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (job_id) REFERENCES installation_jobs(id),
  FOREIGN KEY (changed_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS job_team_members (
  job_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (job_id, user_id),
  FOREIGN KEY (job_id) REFERENCES installation_jobs(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(50) NOT NULL,
  description TEXT NULL,
  specs TEXT NULL,
  image_path VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT NULL,
  icon VARCHAR(50) NULL,
  sort_order INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS site_settings (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT NULL
);

-- Seed data --------------------------------------------------------------

INSERT INTO users (name, email, password_hash, role, phone) VALUES
  ('Admin', 'admin@hvusolar.com', '$2y$12$FUW81KuVaP8ujSmo24F6/e4nVjgDj9UZMEsR8i/7S.MKxd4nTnjDa', 'admin', '9999999999')
ON DUPLICATE KEY UPDATE name = name;
-- Default password: "password" (change immediately after first login).

INSERT INTO services (id, title, description, icon, sort_order) VALUES
  ('1.','Site Survey', '
  site assessment of your roof and energy needs to design the right system.', 'clipboard', 1),
  ('2.','Installation', 'End-to-end installation by certified technicians, completed in days not weeks.', 'wrench', 2),
  ('3.','Maintenance & AMC', 'Annual maintenance contracts to keep your system running at peak efficiency.', 'shield', 3),
  ('4.','System Upgrade', 'Expand or upgrade an existing solar system as your energy needs grow.', 'arrow-up', 4)
ON DUPLICATE KEY UPDATE title = title;

INSERT INTO products (name, category, description, specs, sort_order) VALUES
  ('Monocrystalline Solar Panel 540W', 'panel', 'High-efficiency monocrystalline panel for residential rooftops.', '540W | 21% efficiency | 25-year warranty', 1),
  ('Hybrid Solar Inverter 5kW', 'inverter', 'Grid-tied hybrid inverter with battery backup support.', '5kW | MPPT | Wi-Fi monitoring', 2),
  ('Lithium Battery Storage 5kWh', 'battery', 'Compact lithium-ion storage for backup power during outages.', '5kWh | 6000+ cycles | Wall-mounted', 3)
ON DUPLICATE KEY UPDATE name = name;

INSERT INTO site_settings (`key`, `value`) VALUES
  ('company_phone', '+91 98765 43210'),
  ('company_whatsapp', '+91 98765 43210'),
  ('company_email', 'info@hvusolar.com'),
  ('company_address', 'Hindustan Vidyut Udyog Solar, Industrial Area, India'),
  ('stat_systems_installed', '1200+'),
  ('stat_years_experience', '10+'),
  ('stat_customer_rating', '4.8/5')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- Social profile URLs. The footer hides any icon whose URL is empty or '#',
-- so replace these with the real profiles to make the icons appear.
-- `key = key` keeps values already set from being reset on a schema re-run.
INSERT INTO site_settings (`key`, `value`) VALUES
  ('social_facebook', 'https://www.facebook.com/'),
  ('social_twitter', 'https://x.com/'),
  ('social_youtube', 'https://www.youtube.com/')
ON DUPLICATE KEY UPDATE `key` = `key`;
