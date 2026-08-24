-- Migration 003 — seed data for the catalogue tables. Idempotent.
-- Prices are indicative Delhi-NCR turnkey figures; update from the real rate card.

UPDATE products SET
  brand = 'Waaree', price = 13500, mrp = 16200, wattage = 545,
  warranty_years = 25, rating = 4.8, review_count = 126
WHERE category = 'panel' AND price IS NULL;

UPDATE products SET
  brand = 'Luminous', price = 48000, mrp = 56000, wattage = 5000,
  warranty_years = 5, rating = 4.6, review_count = 84
WHERE category = 'inverter' AND price IS NULL;

UPDATE products SET
  brand = 'Exide', price = 185000, mrp = 210000, wattage = 5000,
  warranty_years = 10, rating = 4.7, review_count = 41
WHERE category = 'battery' AND price IS NULL;

-- Solar kits ---------------------------------------------------------------
-- `includes` is one component per line; the kit page splits on newlines.
INSERT INTO solar_kits (name, slug, system_kw, kit_type, price, subsidy, monthly_units, suits, includes, is_featured, sort_order) VALUES
  ('1 kW On-Grid Rooftop Kit', '1kw-ongrid', 1.00, 'ongrid', 65000, 30000, 120,
   '1–2 BHK · bill up to ₹1,000',
   '2 × 545W Mono PERC panels\n1 kW on-grid inverter\nMounting structure & rails\nDC/AC cabling + protection\nNet-metering paperwork\n5-year workmanship warranty', 0, 1),
  ('2 kW On-Grid Rooftop Kit', '2kw-ongrid', 2.00, 'ongrid', 122000, 60000, 240,
   '2 BHK · bill ₹1,000–2,000',
   '4 × 545W Mono PERC panels\n2 kW on-grid inverter\nMounting structure & rails\nDC/AC cabling + protection\nNet-metering paperwork\n5-year workmanship warranty', 1, 2),
  ('3 kW On-Grid Rooftop Kit', '3kw-ongrid', 3.00, 'ongrid', 178000, 78000, 360,
   '3 BHK · bill ₹2,000–3,000 · full subsidy',
   '6 × 545W Mono PERC panels\n3 kW on-grid inverter\nMounting structure & rails\nDC/AC cabling + protection\nNet-metering paperwork\n5-year workmanship warranty', 1, 3),
  ('5 kW On-Grid Rooftop Kit', '5kw-ongrid', 5.00, 'ongrid', 290000, 78000, 600,
   'Large home / villa · bill ₹4,000–6,000',
   '10 × 545W Mono PERC panels\n5 kW on-grid inverter\nMounting structure & rails\nDC/AC cabling + protection\nNet-metering paperwork\n5-year workmanship warranty', 1, 4),
  ('5 kW Hybrid Kit with Backup', '5kw-hybrid', 5.00, 'hybrid', 465000, 78000, 600,
   'Homes with frequent power cuts',
   '10 × 545W Mono PERC panels\n5 kW hybrid inverter\n5 kWh lithium battery\nMounting structure & rails\nDC/AC cabling + protection\n5-year workmanship warranty', 0, 5),
  ('10 kW Commercial Kit', '10kw-commercial', 10.00, 'ongrid', 550000, 0, 1200,
   'Shops, clinics, small offices',
   '19 × 545W Mono PERC panels\n10 kW three-phase inverter\nMounting structure & rails\nDC/AC cabling + protection\nAccelerated-depreciation paperwork\n5-year workmanship warranty', 0, 6)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Completed projects -------------------------------------------------------
INSERT INTO projects (title, slug, location, system_kw, segment, completed_on, summary, monthly_savings, sort_order) VALUES
  ('5 kW Rooftop for a Sector 57 Villa', '5kw-sector-57-villa', 'Sector 57, Gurgaon', 5.00, 'residential', '2025-03-14',
   'A full 5 kW on-grid system on a south-facing terrace, commissioned with net metering in 11 days. The household bill dropped from ₹5,200 to under ₹400.', 4800, 1),
  ('3 kW PM Surya Ghar Install, Sector 83', '3kw-sector-83', 'Sector 83, Gurgaon', 3.00, 'residential', '2025-05-02',
   'Subsidy-assisted 3 kW rooftop under PM Surya Ghar. We handled the national-portal application end to end; the ₹78,000 subsidy was credited in seven weeks.', 2900, 2),
  ('25 kW Rooftop for a Manesar Unit', '25kw-manesar-factory', 'IMT Manesar', 25.00, 'industrial', '2025-01-20',
   'A 25 kW three-phase rooftop array across two sheds, cutting daytime grid draw for a components manufacturer and qualifying for accelerated depreciation.', 24000, 3),
  ('10 kW Hybrid System for a Clinic', '10kw-clinic-sohna-road', 'Sohna Road, Gurgaon', 10.00, 'commercial', '2024-11-08',
   'Hybrid system with battery backup so diagnostic equipment rides through outages, replacing a diesel genset that cost ₹18,000 a month to run.', 11500, 4),
  ('8 kW Rooftop for a Faridabad School', '8kw-faridabad-school', 'Sector 21, Faridabad', 8.00, 'institutional', '2025-06-17',
   'An 8 kW array over a school corridor doubling as a covered walkway, with a live generation display in the main hall for the students.', 7600, 5),
  ('2 kW Rooftop, Palam Vihar', '2kw-palam-vihar', 'Palam Vihar, Gurgaon', 2.00, 'residential', '2025-07-29',
   'Compact 2 kW system on a 200 sq ft terrace for a retired couple, sized to zero out their bill without exporting surplus.', 1850, 6)
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- Certifications -----------------------------------------------------------
INSERT INTO certifications (name, issuer, description, sort_order) VALUES
  ('ALMM Listed Modules', 'MNRE', 'We install only modules from the Approved List of Models & Manufacturers, which PM Surya Ghar subsidy claims require.', 1),
  ('MNRE Registered Vendor', 'Ministry of New & Renewable Energy', 'Registered to execute subsidised rooftop installations under the national portal.', 2),
  ('BIS Certified Components', 'Bureau of Indian Standards', 'Panels, inverters and cabling conform to the relevant IS specifications.', 3),
  ('ISO 9001:2015', 'Quality Management', 'Documented survey, installation and handover process, audited annually.', 4),
  ('DISCOM Empanelled', 'DHBVN / BSES', 'Empanelled for net-metering applications across Gurgaon and Delhi NCR.', 5),
  ('IEC 61215 / 61730', 'International Electrotechnical Commission', 'Modules tested for performance and safety under international standards.', 6)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Service areas ------------------------------------------------------------
INSERT INTO service_areas (city, state, pincodes, sort_order) VALUES
  ('Gurgaon', 'Haryana', '122001, 122002, 122003, 122004, 122009, 122011, 122018', 1),
  ('Delhi', 'Delhi NCR', '110001, 110017, 110030, 110037, 110070', 2),
  ('Faridabad', 'Haryana', '121001, 121002, 121003, 121006', 3),
  ('Noida', 'Uttar Pradesh', '201301, 201303, 201305, 201309', 4),
  ('Manesar', 'Haryana', '122050, 122051', 5),
  ('Rewari', 'Haryana', '123401, 123411', 6),
  ('Sonipat', 'Haryana', '131001, 131021', 7),
  ('Ghaziabad', 'Uttar Pradesh', '201001, 201009, 201014', 8)
ON DUPLICATE KEY UPDATE city = VALUES(city);

-- Careers ------------------------------------------------------------------
INSERT INTO job_openings (title, slug, department, location, employment_type, experience, description, posted_on, sort_order) VALUES
  ('Solar Installation Technician', 'solar-installation-technician', 'Operations', 'Gurgaon, Haryana', 'Full-time', '1–3 years',
   'Install rooftop mounting structures, modules and DC/AC wiring on residential and commercial sites. ITI in electrical or equivalent, comfortable working at height, two-wheeler licence preferred.', '2025-07-01', 1),
  ('Site Survey Engineer', 'site-survey-engineer', 'Engineering', 'Gurgaon, Haryana', 'Full-time', '2–4 years',
   'Assess rooftops for shading, structure and load, size systems, and produce the layout and BOM that the install team works from. Diploma or B.Tech in electrical, familiarity with PVsyst or similar.', '2025-07-15', 2),
  ('Sales Executive — Residential Solar', 'sales-executive-residential', 'Sales', 'Gurgaon, Haryana', 'Full-time', '1–3 years',
   'Convert enquiries into installs: consult homeowners, explain PM Surya Ghar subsidy and financing, and close. Strong Hindi and English, prior solar or consumer-durables sales an advantage.', '2025-08-01', 3),
  ('Subsidy & Documentation Coordinator', 'subsidy-documentation-coordinator', 'Operations', 'Gurgaon, Haryana', 'Full-time', '1–2 years',
   'Own the PM Surya Ghar national-portal applications and DISCOM net-metering paperwork end to end, and keep customers updated on where their subsidy stands.', '2025-08-10', 4)
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- Product reviews ----------------------------------------------------------
-- Joined by category so it seeds whatever product ids exist, and the NOT EXISTS
-- guard keeps a re-run from duplicating them.
INSERT INTO product_reviews (product_id, author, location, rating, body)
SELECT p.id, v.author, v.location, v.rating, v.body
FROM products p
JOIN (
  SELECT 'panel' AS cat, 'Rajesh Kumar' AS author, 'Sector 57, Gurgaon' AS location, 5 AS rating,
         'Six of these on my terrace since March. The bill went from ₹5,200 to ₹380. Output has held steady right through the summer.' AS body
  UNION ALL SELECT 'panel', 'Meena Sharma', 'Palam Vihar', 5,
         'Installation was clean and the team explained the net-metering process properly. No complaints after four months.'
  UNION ALL SELECT 'inverter', 'Anil Verma', 'Sohna Road', 4,
         'The Wi-Fi monitoring is genuinely useful — I can see generation per hour on my phone. Switchover to backup is quick.'
  UNION ALL SELECT 'battery', 'Deepak Singh', 'Sector 83, Gurgaon', 5,
         'Runs the fridge, fans and lights through a three-hour cut without any noticeable drop. Worth it during the monsoon.'
) v ON v.cat = p.category
WHERE NOT EXISTS (SELECT 1 FROM product_reviews pr WHERE pr.product_id = p.id AND pr.author = v.author);

-- Finance defaults ---------------------------------------------------------
INSERT INTO site_settings (`key`, `value`) VALUES
  ('finance_rate', '9.5'),
  ('finance_max_months', '84'),
  ('default_tariff', '8.0')
ON DUPLICATE KEY UPDATE `key` = `key`;
