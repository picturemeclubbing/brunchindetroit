-- brunchindetroit.com — schema skeleton (Phase 1)
-- Import via phpMyAdmin or: mysql -u user -p brunchindetroit < database/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS brunchindetroit
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE brunchindetroit;

-- ---------------------------------------------------------------------------
-- Admins
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(120) NOT NULL,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Site settings (key/value)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT NULL,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Neighborhoods
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS neighborhoods (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_neighborhoods_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Venues
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS venues (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(160) NOT NULL,
  name VARCHAR(200) NOT NULL,
  description TEXT NULL,
  address_line1 VARCHAR(200) NULL,
  address_line2 VARCHAR(200) NULL,
  city VARCHAR(100) NOT NULL DEFAULT 'Detroit',
  state VARCHAR(50) NOT NULL DEFAULT 'MI',
  zip VARCHAR(20) NULL,
  phone VARCHAR(40) NULL,
  website_url VARCHAR(500) NULL,
  instagram_url VARCHAR(500) NULL,
  facebook_url VARCHAR(500) NULL,
  neighborhood_id INT UNSIGNED NULL,
  price_range ENUM('$', '$$', '$$$', '$$$$') NULL,
  brunch_hours_note TEXT NULL,
  main_image_path VARCHAR(500) NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  featured_sort INT NOT NULL DEFAULT 0,
  menu_last_updated_at DATETIME NULL,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_venues_slug (slug),
  KEY idx_venues_neighborhood (neighborhood_id),
  KEY idx_venues_published_featured (is_published, is_featured),
  CONSTRAINT fk_venues_neighborhood
    FOREIGN KEY (neighborhood_id) REFERENCES neighborhoods (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS venue_hours (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  venue_id INT UNSIGNED NOT NULL,
  day_of_week TINYINT UNSIGNED NOT NULL COMMENT '0=Sunday .. 6=Saturday',
  hour_type ENUM('regular', 'brunch') NOT NULL DEFAULT 'regular',
  open_time TIME NULL,
  close_time TIME NULL,
  is_closed TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_venue_hours_venue (venue_id),
  CONSTRAINT fk_venue_hours_venue
    FOREIGN KEY (venue_id) REFERENCES venues (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS venue_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  venue_id INT UNSIGNED NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  caption VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_venue_images_venue (venue_id),
  CONSTRAINT fk_venue_images_venue
    FOREIGN KEY (venue_id) REFERENCES venues (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tags
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cuisine_tags (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cuisine_tags_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS venue_feature_tags (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_venue_feature_tags_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dietary_tags (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_dietary_tags_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS venue_tag_map (
  venue_id INT UNSIGNED NOT NULL,
  tag_type ENUM('cuisine', 'feature', 'dietary') NOT NULL,
  tag_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (venue_id, tag_type, tag_id),
  KEY idx_venue_tag_map_tag (tag_type, tag_id),
  CONSTRAINT fk_venue_tag_map_venue
    FOREIGN KEY (venue_id) REFERENCES venues (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Menu & allergens
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS menu_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  venue_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_menu_categories_venue (venue_id),
  CONSTRAINT fk_menu_categories_venue
    FOREIGN KEY (venue_id) REFERENCES venues (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  venue_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NULL,
  name VARCHAR(200) NOT NULL,
  description TEXT NULL,
  price DECIMAL(8, 2) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_menu_items_venue (venue_id),
  KEY idx_menu_items_category (category_id),
  CONSTRAINT fk_menu_items_venue
    FOREIGN KEY (venue_id) REFERENCES venues (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_menu_items_category
    FOREIGN KEY (category_id) REFERENCES menu_categories (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS allergens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_allergens_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_item_dietary_tags (
  menu_item_id INT UNSIGNED NOT NULL,
  dietary_tag_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (menu_item_id, dietary_tag_id),
  CONSTRAINT fk_midt_menu_item
    FOREIGN KEY (menu_item_id) REFERENCES menu_items (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_midt_dietary_tag
    FOREIGN KEY (dietary_tag_id) REFERENCES dietary_tags (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_item_allergen_statuses (
  menu_item_id INT UNSIGNED NOT NULL,
  allergen_id INT UNSIGNED NOT NULL,
  status ENUM(
    'contains',
    'does_not_contain',
    'may_contain',
    'cross_contact_risk',
    'unknown'
  ) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY (menu_item_id, allergen_id),
  KEY idx_mias_allergen_status (allergen_id, status),
  CONSTRAINT fk_mias_menu_item
    FOREIGN KEY (menu_item_id) REFERENCES menu_items (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_mias_allergen
    FOREIGN KEY (allergen_id) REFERENCES allergens (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Blog
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(160) NOT NULL,
  title VARCHAR(255) NOT NULL,
  excerpt TEXT NULL,
  body LONGTEXT NULL,
  featured_image_path VARCHAR(500) NULL,
  category_id INT UNSIGNED NULL,
  author_admin_id INT UNSIGNED NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_posts_slug (slug),
  KEY idx_blog_posts_category (category_id),
  KEY idx_blog_posts_published (is_published, published_at),
  CONSTRAINT fk_blog_posts_category
    FOREIGN KEY (category_id) REFERENCES blog_categories (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_blog_posts_author
    FOREIGN KEY (author_admin_id) REFERENCES admins (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Gallery (filter by venue/location/date — no gallery_tags in MVP)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS galleries (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(160) NOT NULL,
  title VARCHAR(255) NOT NULL,
  venue_id INT UNSIGNED NULL,
  event_date DATE NULL,
  location_label VARCHAR(200) NULL,
  description TEXT NULL,
  cover_image_path VARCHAR(500) NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_galleries_slug (slug),
  KEY idx_galleries_venue (venue_id),
  KEY idx_galleries_event_date (event_date),
  CONSTRAINT fk_galleries_venue
    FOREIGN KEY (venue_id) REFERENCES venues (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gallery_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  gallery_id INT UNSIGNED NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  caption VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_gallery_images_gallery (gallery_id),
  CONSTRAINT fk_gallery_images_gallery
    FOREIGN KEY (gallery_id) REFERENCES galleries (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
