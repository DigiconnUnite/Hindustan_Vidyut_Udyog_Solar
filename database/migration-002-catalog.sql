-- Migration 002 — product catalogue depth, solar kits, projects, newsletter.
-- Idempotent: safe to re-run. Apply after database/schema.sql.

-- --- products: price, brand, warranty, wattage, datasheet -----------------
-- MySQL has no "ADD COLUMN IF NOT EXISTS", so each add is guarded by a lookup
-- against information_schema and run through a prepared statement.
DROP PROCEDURE IF EXISTS add_column_if_missing;
DELIMITER //
CREATE PROCEDURE add_column_if_missing(
  IN tbl VARCHAR(64), IN col VARCHAR(64), IN definition VARCHAR(255)
)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
  ) THEN
    SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', definition);
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
  END IF;
END //
DELIMITER ;

CALL add_column_if_missing('products', 'slug',          'VARCHAR(160) NULL');
CALL add_column_if_missing('products', 'brand',         'VARCHAR(80) NULL');
CALL add_column_if_missing('products', 'price',         'DECIMAL(10,2) NULL');
CALL add_column_if_missing('products', 'mrp',           'DECIMAL(10,2) NULL');
CALL add_column_if_missing('products', 'wattage',       'INT UNSIGNED NULL');
CALL add_column_if_missing('products', 'warranty_years','TINYINT UNSIGNED NULL');
CALL add_column_if_missing('products', 'datasheet_path','VARCHAR(255) NULL');
CALL add_column_if_missing('products', 'in_stock',      "TINYINT(1) NOT NULL DEFAULT 1");
CALL add_column_if_missing('products', 'rating',        'DECIMAL(2,1) NULL');
CALL add_column_if_missing('products', 'review_count',  'INT UNSIGNED NOT NULL DEFAULT 0');

-- Where the lead came from, so admin can tell a calculator lead from a contact form.
CALL add_column_if_missing('leads', 'source',       "VARCHAR(40) NOT NULL DEFAULT 'contact'");
CALL add_column_if_missing('leads', 'system_kw',    'DECIMAL(5,2) NULL');
CALL add_column_if_missing('leads', 'monthly_bill', 'INT UNSIGNED NULL');

DROP PROCEDURE IF EXISTS add_column_if_missing;

-- Backfill slugs for any product that predates the column.
UPDATE products
SET slug = LOWER(REGEXP_REPLACE(REGEXP_REPLACE(name, '[^a-zA-Z0-9]+', '-'), '(^-|-$)', ''))
WHERE slug IS NULL OR slug = '';

-- --- solar kits -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS solar_kits (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  system_kw DECIMAL(5,2) NOT NULL,
  kit_type ENUM('ongrid','offgrid','hybrid') NOT NULL DEFAULT 'ongrid',
  price DECIMAL(10,2) NOT NULL,
  subsidy DECIMAL(10,2) NOT NULL DEFAULT 0,
  monthly_units INT UNSIGNED NULL,
  suits VARCHAR(160) NULL,
  includes TEXT NULL,          -- one component per line
  image_path VARCHAR(255) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0
);

-- --- completed projects ---------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  slug VARCHAR(170) NOT NULL UNIQUE,
  location VARCHAR(120) NOT NULL,
  system_kw DECIMAL(6,2) NOT NULL,
  segment ENUM('residential','commercial','industrial','institutional') NOT NULL DEFAULT 'residential',
  completed_on DATE NULL,
  summary VARCHAR(400) NULL,
  body TEXT NULL,
  image_path VARCHAR(255) NULL,
  monthly_savings INT UNSIGNED NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0
);

-- --- certifications -------------------------------------------------------
CREATE TABLE IF NOT EXISTS certifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  issuer VARCHAR(120) NULL,
  description VARCHAR(300) NULL,
  logo_path VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0
);

-- --- newsletter -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --- product reviews ------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  author VARCHAR(100) NOT NULL,
  location VARCHAR(100) NULL,
  rating TINYINT UNSIGNED NOT NULL,
  body VARCHAR(600) NOT NULL,
  is_approved TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- --- service areas --------------------------------------------------------
CREATE TABLE IF NOT EXISTS service_areas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  city VARCHAR(80) NOT NULL,
  state VARCHAR(80) NOT NULL DEFAULT 'Haryana',
  pincodes VARCHAR(400) NULL,
  sort_order INT NOT NULL DEFAULT 0
);

-- --- careers --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS job_openings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(140) NOT NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  department VARCHAR(80) NULL,
  location VARCHAR(120) NULL,
  employment_type VARCHAR(40) NULL DEFAULT 'Full-time',
  experience VARCHAR(60) NULL,
  description TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  posted_on DATE NULL,
  sort_order INT NOT NULL DEFAULT 0
);
