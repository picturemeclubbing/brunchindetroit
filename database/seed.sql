-- brunchindetroit.com — seed skeleton (Phase 1)
-- Run after schema.sql. Replace admin password before production.

USE brunchindetroit;

INSERT INTO site_settings (setting_key, setting_value) VALUES
  ('site_domain', 'brunchindetroit.com'),
  ('site_name_display', 'brunch in detroit'),
  ('contact_email', 'hello@brunchindetroit.com'),
  ('allergy_disclaimer', 'Allergy information is provided for general guidance only and may be incomplete, outdated, or subject to preparation changes. brunchindetroit.com does not guarantee that any menu item is allergen-free. Always confirm ingredients and cross-contact risks directly with the restaurant before ordering.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO allergens (name, slug) VALUES
  ('Peanuts', 'peanuts'),
  ('Tree nuts', 'tree-nuts'),
  ('Dairy', 'dairy'),
  ('Eggs', 'eggs'),
  ('Wheat/gluten', 'wheat-gluten'),
  ('Soy', 'soy'),
  ('Fish', 'fish'),
  ('Shellfish', 'shellfish'),
  ('Sesame', 'sesame')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO dietary_tags (name, slug, sort_order) VALUES
  ('Vegan', 'vegan', 10),
  ('Vegetarian', 'vegetarian', 20),
  ('Gluten-Free', 'gluten-free', 30),
  ('Dairy-Free', 'dairy-free', 40),
  ('Keto', 'keto', 50)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  sort_order = VALUES(sort_order);


-- Default admin for local dev only (Phase 2): password `password` — change before production
INSERT INTO admins (email, password_hash, display_name) VALUES
  (
    'admin@brunchindetroit.com',
    '$2y$10$DsQzIDA3nFOSVqhlWrQOw.VBY2iH9BMl5xmxIZmodi3PlerpVhReu',
    'Site Admin'
  )
ON DUPLICATE KEY UPDATE
  password_hash = VALUES(password_hash),
  display_name = VALUES(display_name);

INSERT INTO neighborhoods (name, slug, sort_order) VALUES
  ('Downtown', 'downtown', 10),
  ('Midtown', 'midtown', 20),
  ('Corktown', 'corktown', 30),
  ('Eastern Market', 'eastern-market', 40)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Demo content (venues, blog, galleries) will be added in later phases.
