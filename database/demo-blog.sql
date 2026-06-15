-- =============================================================================
-- brunchindetroit.com — demo blog data (Phase 4A)
-- Safe, idempotent import: demo posts are matched by slug with
-- INSERT ... ON DUPLICATE KEY UPDATE. Re-running this file refreshes only
-- the demo posts below by their known slugs.
--
-- Import (PowerShell, from project root F:\brunch):
--   mysql -u root brunchindetroit < database/demo-blog.sql
-- (Add -p after root if your local MySQL requires a password.)
-- =============================================================================

USE brunchindetroit;

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- Demo blog categories (matched by slug; safe to re-run)
-- ---------------------------------------------------------------------------
INSERT INTO blog_categories (name, slug, sort_order) VALUES
    ('Brunch Guides', 'brunch-guides', 10),
    ('Openings',      'openings',      20),
    ('Food Culture',  'food-culture',  30),
    ('Events',        'events',        40)
ON DUPLICATE KEY UPDATE
    name       = VALUES(name),
    sort_order = VALUES(sort_order);

-- Resolve category ids by slug so we never hard-code ids.
SET @cat_guides    := (SELECT id FROM blog_categories WHERE slug = 'brunch-guides' LIMIT 1);
SET @cat_openings  := (SELECT id FROM blog_categories WHERE slug = 'openings'      LIMIT 1);
SET @cat_culture   := (SELECT id FROM blog_categories WHERE slug = 'food-culture'  LIMIT 1);
SET @cat_events    := (SELECT id FROM blog_categories WHERE slug = 'events'        LIMIT 1);

-- Resolve the default admin id (the first admin is treated as the demo author).
SET @author_id := (SELECT id FROM admins ORDER BY id ASC LIMIT 1);

-- ---------------------------------------------------------------------------
-- Demo blog posts (matched by slug; safe to re-run)
-- ---------------------------------------------------------------------------

-- 1) Featured brunch guide
INSERT INTO blog_posts
    (slug, title, excerpt, body, featured_image_path, category_id, author_admin_id,
     is_published, is_featured, published_at, created_at, updated_at)
VALUES
    ('detroits-most-instagrammable-brunch-spots',
     'Detroit''s Most Instagrammable Brunch Spots',
     'From rooftop mimosas to flower-wall backdrops, these Detroit brunch spots serve up plates that look as good as they taste. Here''s where to point your camera first.',
     '<p>If brunch is half eating and half sharing, Detroit is ready for its close-up. Across the city, chefs and designers are building dining rooms and rooftops that beg to be photographed — and the menus hold up their end of the deal too.</p>\n\n<h2>Why presentation matters</h2>\n\n<p>The best brunch plates balance color, height, and texture. Think jewel-toned berry stacks, golden Benedicts, and pastel mimosa flights. At Detroit''s most photogenic spots, the room is part of the recipe.</p>\n\n<h2>What to order for the camera</h2>\n\n<ul>\n  <li><strong>Tower it up:</strong> stacked pancakes and French toast photograph better flat-lay style from above.</li>\n  <li><strong>Chase color:</strong> matcha pancakes, beet-cured salmon, and edible flowers all pop on camera.</li>\n  <li><strong>Use natural light:</strong> ask for a window seat — soft morning light flatters every plate.</li>\n</ul>\n\n<p>Whether you''re building a feed or just capturing a memory, these are the Detroit brunch rooms worth arriving early for.</p>',
     'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?auto=format&fit=crop&w=1600&q=80',
     @cat_guides, @author_id,
     1, 1,
     '2025-04-22 09:00:00',
     '2025-04-22 09:00:00',
     '2025-04-22 09:00:00')
ON DUPLICATE KEY UPDATE
    title                = VALUES(title),
    excerpt              = VALUES(excerpt),
    body                 = VALUES(body),
    featured_image_path  = VALUES(featured_image_path),
    category_id          = VALUES(category_id),
    author_admin_id      = VALUES(author_admin_id),
    is_published         = VALUES(is_published),
    is_featured          = VALUES(is_featured),
    published_at         = VALUES(published_at),
    updated_at           = VALUES(updated_at);

-- 2) Downtown guide
INSERT INTO blog_posts
    (slug, title, excerpt, body, featured_image_path, category_id, author_admin_id,
     is_published, is_featured, published_at, created_at, updated_at)
VALUES
    ('where-to-find-weekend-brunch-in-downtown-detroit',
     'Where to Find Weekend Brunch in Downtown Detroit',
     'A neighborhood-by-neighborhood guide to the best weekend brunch tables across downtown Detroit, from classic eggs Benedict to skyline rooftop spreads.',
     '<p>Downtown Detroit wakes up slowly on weekends, and that''s a good thing. By the time the brunch crowd rolls in, kitchens are firing on all cylinders and the mimosas are already poured.</p>\n\n<h2>Downtown districts worth the trip</h2>\n\n<p>From the skyline views near the riverfront to the hidden patios of the theater district, downtown offers a brunch for every mood. Use this guide to plan your route before the wait lists fill up.</p>\n\n<h2>Pro tips for weekend brunch</h2>\n\n<ul>\n  <li>Arrive before 10am for the shortest waits.</li>\n  <li>Book rooftops and patios ahead in warm months.</li>\n  <li>Ask about off-menu specials — downtown chefs love to improvise.</li>\n</ul>\n\n<p>Wherever you land, downtown Detroit brunch rewards the early and the curious.</p>',
     'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=1600&q=80',
     @cat_guides, @author_id,
     1, 0,
     '2025-04-15 09:00:00',
     '2025-04-15 09:00:00',
     '2025-04-15 09:00:00')
ON DUPLICATE KEY UPDATE
    title                = VALUES(title),
    excerpt              = VALUES(excerpt),
    body                 = VALUES(body),
    featured_image_path  = VALUES(featured_image_path),
    category_id          = VALUES(category_id),
    author_admin_id      = VALUES(author_admin_id),
    is_published         = VALUES(is_published),
    is_featured          = VALUES(is_featured),
    published_at         = VALUES(published_at),
    updated_at           = VALUES(updated_at);

-- 3) First-timer dishes
INSERT INTO blog_posts
    (slug, title, excerpt, body, featured_image_path, category_id, author_admin_id,
     is_published, is_featured, published_at, created_at, updated_at)
VALUES
    ('best-brunch-dishes-for-first-time-visitors',
     'Best Brunch Dishes for First-Time Visitors',
     'New to Detroit brunch? Start here. These are the iconic plates, sweet and savory, that define a true Motor City brunch run.',
     '<p>If it''s your first time brunching in Detroit, the menu can feel overwhelming. Here''s the shortlist that tells the story of the city''s brunch scene in a single bite.</p>\n\n<h2>Sweet starters</h2>\n\n<p>Thick-cut French toast, berry-stacked pancakes, and warm cinnamon rolls set the tone. Detroit bakers have a sweet tooth, and it shows.</p>\n\n<h2>Savory classics</h2>\n\n<ul>\n  <li><strong>Eggs Benedict:</strong> the benchmark by which every brunch kitchen is judged.</li>\n  <li><strong>Chicken & waffles:</strong> a Motor City staple done a dozen ways.</li>\n  <li><strong>Shakshuka:</strong> increasingly popular, especially in Midtown.</li>\n</ul>\n\n<p>Order one sweet, one savory, and split everything — that''s the Detroit way.</p>',
     'https://images.unsplash.com/photo-1484723091739-30a097e8f929?auto=format&fit=crop&w=1600&q=80',
     @cat_guides, @author_id,
     1, 0,
     '2025-04-08 09:00:00',
     '2025-04-08 09:00:00',
     '2025-04-08 09:00:00')
ON DUPLICATE KEY UPDATE
    title                = VALUES(title),
    excerpt              = VALUES(excerpt),
    body                 = VALUES(body),
    featured_image_path  = VALUES(featured_image_path),
    category_id          = VALUES(category_id),
    author_admin_id      = VALUES(author_admin_id),
    is_published         = VALUES(is_published),
    is_featured          = VALUES(is_featured),
    published_at         = VALUES(published_at),
    updated_at           = VALUES(updated_at);

-- 4) Coffee + brunch pairings
INSERT INTO blog_posts
    (slug, title, excerpt, body, featured_image_path, category_id, author_admin_id,
     is_published, is_featured, published_at, created_at, updated_at)
VALUES
    ('detroit-coffee-and-brunch-pairings',
     'Detroit Coffee and Brunch Pairings',
     'The right cup makes the plate. A field guide to pairing Detroit''s best specialty coffee with its most beloved brunch dishes.',
     '<p>Coffee is not an afterthought at Detroit brunch — it''s a co-star. Here''s how to match the city''s best roasts with the plates they were born to accompany.</p>\n\n<h2>Pairing basics</h2>\n\n<p>Match intensity to intensity. A bright, fruity Ethiopian pairs beautifully with berry pancakes, while a chocolatey Brazilian brings out the best in a savory breakfast sandwich.</p>\n\n<h2>Three can''t-miss combos</h2>\n\n<ul>\n  <li><strong>Light roast + citrusy Eggs Benedict:</strong> the acid cuts the hollandaise.</li>\n  <li><strong>Medium roast + chicken & waffles:</strong> caramel notes complement the syrup.</li>\n  <li><strong>Dark roast + chocolate French toast:</strong> bitter meets sweet in the best way.</li>\n</ul>\n\n<p>Ask your barista what they''re excited about this week — they always know.</p>',
     'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1600&q=80',
     @cat_culture, @author_id,
     1, 0,
     '2025-04-01 09:00:00',
     '2025-04-01 09:00:00',
     '2025-04-01 09:00:00')
ON DUPLICATE KEY UPDATE
    title                = VALUES(title),
    excerpt              = VALUES(excerpt),
    body                 = VALUES(body),
    featured_image_path  = VALUES(featured_image_path),
    category_id          = VALUES(category_id),
    author_admin_id      = VALUES(author_admin_id),
    is_published         = VALUES(is_published),
    is_featured          = VALUES(is_featured),
    published_at         = VALUES(published_at),
    updated_at           = VALUES(updated_at);

-- 5) Rooftop watch (Openings category)
INSERT INTO blog_posts
    (slug, title, excerpt, body, featured_image_path, category_id, author_admin_id,
     is_published, is_featured, published_at, created_at, updated_at)
VALUES
    ('rooftop-brunch-spots-to-watch',
     'Rooftop Brunch Spots to Watch',
     'Skyline views, sunset mimosas, and fresh seasonal menus — these are the Detroit rooftop brunch destinations making moves this season.',
     '<p>When the weather turns warm, Detroit brunch goes up. Rooftops across the city are opening for the season with fresh menus, signature cocktails, and skyline views that rival any plate.</p>\n\n<h2>What to expect this season</h2>\n\n<p>Expect more low-ABV cocktails, more local produce, and more shade structures that make midday brunch comfortable even in peak sun. Reservations are strongly recommended — rooftops fill fast.</p>\n\n<h2>What to bring</h2>\n\n<ul>\n  <li>Sunglasses and sunscreen — the sun is real up there.</li>\n  <li>A camera; the skyline does the work for you.</li>\n  <li>Patience for the elevator on the way down.</li>\n</ul>\n\n<p>If you''ve never done a Detroit rooftop brunch, this is the year to start.</p>',
     'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=1600&q=80',
     @cat_openings, @author_id,
     1, 0,
     '2025-03-25 09:00:00',
     '2025-03-25 09:00:00',
     '2025-03-25 09:00:00')
ON DUPLICATE KEY UPDATE
    title                = VALUES(title),
    excerpt              = VALUES(excerpt),
    body                 = VALUES(body),
    featured_image_path  = VALUES(featured_image_path),
    category_id          = VALUES(category_id),
    author_admin_id      = VALUES(author_admin_id),
    is_published         = VALUES(is_published),
    is_featured          = VALUES(is_featured),
    published_at         = VALUES(published_at),
    updated_at           = VALUES(updated_at);

-- 6) Planning guide (Events)
INSERT INTO blog_posts
    (slug, title, excerpt, body, featured_image_path, category_id, author_admin_id,
     is_published, is_featured, published_at, created_at, updated_at)
VALUES
    ('how-to-plan-a-brunch-day-in-detroit',
     'How to Plan a Brunch Day in Detroit',
     'The ultimate itinerary builder: map, timing, reservations, and backup plans for a perfect Detroit brunch day from morning to afternoon.',
     '<p>A great brunch day doesn''t happen by accident. With a little planning, you can hit two or three of Detroit''s best spots without the long waits.</p>\n\n<h2>The 9am rule</h2>\n\n<p>The single biggest brunch hack in Detroit: be seated by 9am. Most crowds arrive between 10:30 and noon, which means early birds get the best tables and the freshest food.</p>\n\n<h2>Sample itinerary</h2>\n\n<ol>\n  <li><strong>9:00am:</strong> Coffee + pastry at a downtown cafe.</li>\n  <li><strong>10:30am:</strong> Savory brunch main at a neighborhood favorite.</li>\n  <li><strong>1:00pm:</strong> Rooftop or patio drinks to wind down.</li>\n</ol>\n\n<p>Leave buffer time between stops, confirm reservations the night before, and always have a backup spot in mind — Detroit brunch waits for no one.</p>',
     'https://images.unsplash.com/photo-1551218808-94e220e084d2?auto=format&fit=crop&w=1600&q=80',
     @cat_events, @author_id,
     1, 0,
     '2025-03-18 09:00:00',
     '2025-03-18 09:00:00',
     '2025-03-18 09:00:00')
ON DUPLICATE KEY UPDATE
    title                = VALUES(title),
    excerpt              = VALUES(excerpt),
    body                 = VALUES(body),
    featured_image_path  = VALUES(featured_image_path),
    category_id          = VALUES(category_id),
    author_admin_id      = VALUES(author_admin_id),
    is_published         = VALUES(is_published),
    is_featured          = VALUES(is_featured),
    published_at         = VALUES(published_at),
    updated_at           = VALUES(updated_at);