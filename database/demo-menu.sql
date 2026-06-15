-- brunchindetroit.com — demo menu data (Phase 3C)
-- Run AFTER schema.sql, seed.sql, and demo-venues.sql.
-- Import (PowerShell): mysql -u root -p brunchindetroit < database/demo-menu.sql
--   or with explicit creds: mysql -u root -ppassword brunchindetroit < database/demo-menu.sql

USE brunchindetroit;

-- ---------------------------------------------------------------------------
-- Ensure the 9 major allergens exist (matches seed.sql). Idempotent so this
-- demo file is self-sufficient even if seed.sql's allergen rows were not yet
-- loaded. We do NOT modify seed.sql; this just guarantees referential
-- integrity for the demo menu data below.
-- ---------------------------------------------------------------------------
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

-- ---------------------------------------------------------------------------
-- Resolve demo venue ids by slug so this file is id-stable.
-- ---------------------------------------------------------------------------
SET @sweet_maple_id  = (SELECT id FROM venues WHERE slug = 'sweet-maple-cafe' LIMIT 1);
SET @garden_rooftop  = (SELECT id FROM venues WHERE slug = 'garden-rooftop' LIMIT 1);
SET @corktown_biscuit = (SELECT id FROM venues WHERE slug = 'corktown-biscuit-bar' LIMIT 1);

-- ---------------------------------------------------------------------------
-- Resolve allergen ids by slug (seed.sql defines all 9).
-- ---------------------------------------------------------------------------
SET @peanuts        = (SELECT id FROM allergens WHERE slug = 'peanuts' LIMIT 1);
SET @tree_nuts      = (SELECT id FROM allergens WHERE slug = 'tree-nuts' LIMIT 1);
SET @dairy          = (SELECT id FROM allergens WHERE slug = 'dairy' LIMIT 1);
SET @eggs           = (SELECT id FROM allergens WHERE slug = 'eggs' LIMIT 1);
SET @wheat_gluten   = (SELECT id FROM allergens WHERE slug = 'wheat-gluten' LIMIT 1);
SET @soy            = (SELECT id FROM allergens WHERE slug = 'soy' LIMIT 1);
SET @fish           = (SELECT id FROM allergens WHERE slug = 'fish' LIMIT 1);
SET @shellfish      = (SELECT id FROM allergens WHERE slug = 'shellfish' LIMIT 1);
SET @sesame         = (SELECT id FROM allergens WHERE slug = 'sesame' LIMIT 1);

-- ===========================================================================
-- RESEED: clear prior demo menu data for the three demo venues so this file
-- can be run repeatedly. Foreign keys cascade to allergen statuses / dietary
-- tags automatically. Scope is limited to these three venue ids.
-- ===========================================================================
DELETE mias
FROM menu_item_allergen_statuses mias
INNER JOIN menu_items mi ON mi.id = mias.menu_item_id
WHERE mi.venue_id IN (@sweet_maple_id, @garden_rooftop, @corktown_biscuit);

DELETE midt
FROM menu_item_dietary_tags midt
INNER JOIN menu_items mi ON mi.id = midt.menu_item_id
WHERE mi.venue_id IN (@sweet_maple_id, @garden_rooftop, @corktown_biscuit);

DELETE FROM menu_items
WHERE venue_id IN (@sweet_maple_id, @garden_rooftop, @corktown_biscuit);

DELETE FROM menu_categories
WHERE venue_id IN (@sweet_maple_id, @garden_rooftop, @corktown_biscuit);

-- ===========================================================================
-- CATEGORIES
-- ===========================================================================
INSERT INTO menu_categories (venue_id, name, sort_order) VALUES
  (@sweet_maple_id,  'Brunch Plates',   10),
  (@sweet_maple_id,  'Sides & Sweets',  20),
  (@garden_rooftop,  'Brunch Plates',   10),
  (@garden_rooftop,  'Drinks',          20),
  (@corktown_biscuit,'Brunch Plates',   10),
  (@corktown_biscuit,'Sides & Sweets',  20);

-- Re-resolve category ids for the items below.
SET @sm_plates  = (SELECT id FROM menu_categories WHERE venue_id = @sweet_maple_id  AND name = 'Brunch Plates'  ORDER BY id DESC LIMIT 1);
SET @sm_sides   = (SELECT id FROM menu_categories WHERE venue_id = @sweet_maple_id  AND name = 'Sides & Sweets' ORDER BY id DESC LIMIT 1);
SET @gr_plates  = (SELECT id FROM menu_categories WHERE venue_id = @garden_rooftop  AND name = 'Brunch Plates'  ORDER BY id DESC LIMIT 1);
SET @gr_drinks  = (SELECT id FROM menu_categories WHERE venue_id = @garden_rooftop  AND name = 'Drinks'         ORDER BY id DESC LIMIT 1);
SET @cb_plates  = (SELECT id FROM menu_categories WHERE venue_id = @corktown_biscuit AND name = 'Brunch Plates' ORDER BY id DESC LIMIT 1);
SET @cb_sides   = (SELECT id FROM menu_categories WHERE venue_id = @corktown_biscuit AND name = 'Sides & Sweets' ORDER BY id DESC LIMIT 1);

-- ===========================================================================
-- MENU ITEMS
-- ===========================================================================
INSERT INTO menu_items (venue_id, category_id, name, description, price, sort_order, is_published) VALUES
  -- Sweet Maple Cafe
  (@sweet_maple_id, @sm_plates, 'Chicken & Waffles',
   'Crispy fried chicken over a Belgian waffle with maple syrup and whipped honey butter.', 18.00, 10, 1),
  (@sweet_maple_id, @sm_plates, 'Shrimp & Grits',
   'Saut\u00e9ed shrimp over creamy cheddar grits with andouille and scallions.', 19.50, 20, 1),
  (@sweet_maple_id, @sm_sides, 'Blueberry Pancakes',
   'Fluffy buttermilk pancakes with wild blueberries and warm maple syrup.', 12.00, 10, 1),
  (@sweet_maple_id, @sm_sides, 'Side of Bacon',
   'Thick-cut hardwood-smoked bacon, four slices.', 5.00, 20, 1),

  -- The Garden Rooftop
  (@garden_rooftop, @gr_plates, 'Avocado Toast',
   'Smashed avocado on toasted sourdough with radish, microgreens, and chili oil.', 14.00, 10, 1),
  (@garden_rooftop, @gr_plates, 'Rooftop Brunch Bowl',
   'Quinoa, roasted sweet potato, kale, black beans, avocado, and tahini dressing.', 16.00, 20, 1),
  (@garden_rooftop, @gr_drinks, 'Mimosa Flight',
   'A flight of four mimosas: classic orange, grapefruit, mango, and pomegranate.', 16.00, 10, 1),

  -- Corktown Biscuit Bar
  (@corktown_biscuit, @cb_plates, 'Biscuit Breakfast Sandwich',
   'House buttermilk biscuit with fried egg, cheddar, and your choice of bacon or sausage.', 11.00, 10, 1),
  (@corktown_biscuit, @cb_sides, 'Loaded Breakfast Potatoes',
   'Crispy breakfast potatoes with peppers, onions, and melted cheddar.', 7.00, 10, 1),
  (@corktown_biscuit, @cb_sides, 'House Coffee',
   'Locally roasted drip coffee, bottomless with refills.', 4.00, 20, 1);

-- ===========================================================================
-- Re-resolve item ids for allergen status rows below.
-- ===========================================================================
SET @sm_chick_waffle  = (SELECT id FROM menu_items WHERE venue_id = @sweet_maple_id  AND name = 'Chicken & Waffles'      ORDER BY id DESC LIMIT 1);
SET @sm_shrimp_grits  = (SELECT id FROM menu_items WHERE venue_id = @sweet_maple_id  AND name = 'Shrimp & Grits'         ORDER BY id DESC LIMIT 1);
SET @sm_blueberry     = (SELECT id FROM menu_items WHERE venue_id = @sweet_maple_id  AND name = 'Blueberry Pancakes'     ORDER BY id DESC LIMIT 1);
SET @sm_bacon         = (SELECT id FROM menu_items WHERE venue_id = @sweet_maple_id  AND name = 'Side of Bacon'          ORDER BY id DESC LIMIT 1);
SET @gr_avo_toast     = (SELECT id FROM menu_items WHERE venue_id = @garden_rooftop  AND name = 'Avocado Toast'          ORDER BY id DESC LIMIT 1);
SET @gr_brunch_bowl   = (SELECT id FROM menu_items WHERE venue_id = @garden_rooftop  AND name = 'Rooftop Brunch Bowl'    ORDER BY id DESC LIMIT 1);
SET @gr_mimosa        = (SELECT id FROM menu_items WHERE venue_id = @garden_rooftop  AND name = 'Mimosa Flight'          ORDER BY id DESC LIMIT 1);
SET @cb_biscuit_sand  = (SELECT id FROM menu_items WHERE venue_id = @corktown_biscuit AND name = 'Biscuit Breakfast Sandwich' ORDER BY id DESC LIMIT 1);
SET @cb_potatoes      = (SELECT id FROM menu_items WHERE venue_id = @corktown_biscuit AND name = 'Loaded Breakfast Potatoes' ORDER BY id DESC LIMIT 1);
SET @cb_coffee        = (SELECT id FROM menu_items WHERE venue_id = @corktown_biscuit AND name = 'House Coffee'          ORDER BY id DESC LIMIT 1);

-- ===========================================================================
-- ALLERGEN STATUSES (all 9 allergens per item, varied for visible filtering)
-- ===========================================================================
INSERT INTO menu_item_allergen_statuses (menu_item_id, allergen_id, status) VALUES
  -- Chicken & Waffles (contains gluten, eggs, dairy; NO peanuts, tree nuts, soy, fish, sesame)
  (@sm_chick_waffle, @peanuts,      'does_not_contain'),
  (@sm_chick_waffle, @tree_nuts,    'does_not_contain'),
  (@sm_chick_waffle, @dairy,        'contains'),
  (@sm_chick_waffle, @eggs,         'contains'),
  (@sm_chick_waffle, @wheat_gluten, 'contains'),
  (@sm_chick_waffle, @soy,          'does_not_contain'),
  (@sm_chick_waffle, @fish,         'does_not_contain'),
  (@sm_chick_waffle, @shellfish,    'does_not_contain'),
  (@sm_chick_waffle, @sesame,       'does_not_contain'),

  -- Shrimp & Grits (contains dairy, shellfish; NO peanuts, tree nuts, soy, fish, sesame)
  (@sm_shrimp_grits, @peanuts,      'does_not_contain'),
  (@sm_shrimp_grits, @tree_nuts,    'does_not_contain'),
  (@sm_shrimp_grits, @dairy,        'contains'),
  (@sm_shrimp_grits, @eggs,         'does_not_contain'),
  (@sm_shrimp_grits, @wheat_gluten, 'may_contain'),
  (@sm_shrimp_grits, @soy,          'does_not_contain'),
  (@sm_shrimp_grits, @fish,         'does_not_contain'),
  (@sm_shrimp_grits, @shellfish,    'contains'),
  (@sm_shrimp_grits, @sesame,       'does_not_contain'),

  -- Blueberry Pancakes (contains gluten, eggs, dairy; NO peanuts, tree nuts, shellfish, sesame)
  (@sm_blueberry, @peanuts,      'does_not_contain'),
  (@sm_blueberry, @tree_nuts,    'does_not_contain'),
  (@sm_blueberry, @dairy,        'contains'),
  (@sm_blueberry, @eggs,         'contains'),
  (@sm_blueberry, @wheat_gluten, 'contains'),
  (@sm_blueberry, @soy,          'does_not_contain'),
  (@sm_blueberry, @fish,         'does_not_contain'),
  (@sm_blueberry, @shellfish,    'does_not_contain'),
  (@sm_blueberry, @sesame,       'does_not_contain'),

  -- Side of Bacon (contains: none of the major 9; clean side)
  (@sm_bacon, @peanuts,      'does_not_contain'),
  (@sm_bacon, @tree_nuts,    'does_not_contain'),
  (@sm_bacon, @dairy,        'does_not_contain'),
  (@sm_bacon, @eggs,         'does_not_contain'),
  (@sm_bacon, @wheat_gluten, 'does_not_contain'),
  (@sm_bacon, @soy,          'does_not_contain'),
  (@sm_bacon, @fish,         'does_not_contain'),
  (@sm_bacon, @shellfish,    'does_not_contain'),
  (@sm_bacon, @sesame,       'does_not_contain'),

  -- Avocado Toast (contains gluten; cross-contact dairy; NO peanuts/tree nuts/eggs/soy/fish/shellfish/sesame)
  (@gr_avo_toast, @peanuts,      'does_not_contain'),
  (@gr_avo_toast, @tree_nuts,    'does_not_contain'),
  (@gr_avo_toast, @dairy,        'cross_contact_risk'),
  (@gr_avo_toast, @eggs,         'does_not_contain'),
  (@gr_avo_toast, @wheat_gluten, 'contains'),
  (@gr_avo_toast, @soy,          'does_not_contain'),
  (@gr_avo_toast, @fish,         'does_not_contain'),
  (@gr_avo_toast, @shellfish,    'does_not_contain'),
  (@gr_avo_toast, @sesame,       'does_not_contain'),

  -- Rooftop Brunch Bowl (plant-based; NO major allergens; cross-contact sesame)
  (@gr_brunch_bowl, @peanuts,      'does_not_contain'),
  (@gr_brunch_bowl, @tree_nuts,    'does_not_contain'),
  (@gr_brunch_bowl, @dairy,        'does_not_contain'),
  (@gr_brunch_bowl, @eggs,         'does_not_contain'),
  (@gr_brunch_bowl, @wheat_gluten, 'does_not_contain'),
  (@gr_brunch_bowl, @soy,          'does_not_contain'),
  (@gr_brunch_bowl, @fish,         'does_not_contain'),
  (@gr_brunch_bowl, @shellfish,    'does_not_contain'),
  (@gr_brunch_bowl, @sesame,       'cross_contact_risk'),

  -- Mimosa Flight (contains: none of the 9 major allergens)
  (@gr_mimosa, @peanuts,      'does_not_contain'),
  (@gr_mimosa, @tree_nuts,    'does_not_contain'),
  (@gr_mimosa, @dairy,        'does_not_contain'),
  (@gr_mimosa, @eggs,         'does_not_contain'),
  (@gr_mimosa, @wheat_gluten, 'does_not_contain'),
  (@gr_mimosa, @soy,          'does_not_contain'),
  (@gr_mimosa, @fish,         'does_not_contain'),
  (@gr_mimosa, @shellfish,    'does_not_contain'),
  (@gr_mimosa, @sesame,       'does_not_contain'),

  -- Biscuit Breakfast Sandwich (contains gluten, eggs, dairy; may contain sesame)
  (@cb_biscuit_sand, @peanuts,      'does_not_contain'),
  (@cb_biscuit_sand, @tree_nuts,    'does_not_contain'),
  (@cb_biscuit_sand, @dairy,        'contains'),
  (@cb_biscuit_sand, @eggs,         'contains'),
  (@cb_biscuit_sand, @wheat_gluten, 'contains'),
  (@cb_biscuit_sand, @soy,          'does_not_contain'),
  (@cb_biscuit_sand, @fish,         'does_not_contain'),
  (@cb_biscuit_sand, @shellfish,    'does_not_contain'),
  (@cb_biscuit_sand, @sesame,       'may_contain'),

  -- Loaded Breakfast Potatoes (contains dairy; cross-contact gluten; otherwise clean)
  (@cb_potatoes, @peanuts,      'does_not_contain'),
  (@cb_potatoes, @tree_nuts,    'does_not_contain'),
  (@cb_potatoes, @dairy,        'contains'),
  (@cb_potatoes, @eggs,         'does_not_contain'),
  (@cb_potatoes, @wheat_gluten, 'cross_contact_risk'),
  (@cb_potatoes, @soy,          'does_not_contain'),
  (@cb_potatoes, @fish,         'does_not_contain'),
  (@cb_potatoes, @shellfish,    'does_not_contain'),
  (@cb_potatoes, @sesame,       'does_not_contain'),

  -- House Coffee (no allergens; unknown milk cross-contact handled by venue)
  (@cb_coffee, @peanuts,      'does_not_contain'),
  (@cb_coffee, @tree_nuts,    'does_not_contain'),
  (@cb_coffee, @dairy,        'unknown'),
  (@cb_coffee, @eggs,         'does_not_contain'),
  (@cb_coffee, @wheat_gluten, 'does_not_contain'),
  (@cb_coffee, @soy,          'does_not_contain'),
  (@cb_coffee, @fish,         'does_not_contain'),
  (@cb_coffee, @shellfish,    'does_not_contain'),
  (@cb_coffee, @sesame,       'does_not_contain');