# Detroit Brunch (brunchindetroit.com) — Build Plan

PHP 8+ / MySQL / PDO / plain templates. Design reference: `deepsite-originals/home_index.html`.

## Phase status

| Phase | Scope | Status |
|-------|--------|--------|
| 1 | Folder structure, bootstrap/config, shared CSS, header/footer, static home shell | **Done** |
| 2 | PDO wiring, schema import, admin login + session guard | **Done** |
| 3 | Public directory + venue detail (menu + allergy filter) | Planned |
| 4 | Blog list/detail, gallery + modal | Planned |
| 5 | Admin CRUD + uploads | Planned |
| 6 | Settings/tags + about/contact/legal pages | Planned |
| 7 | Production hardening + deployment checklist | Planned |

## Architecture

- **Document root:** `public/`
- **Private code:** `app/` (config, bootstrap, helpers, views; controllers/models in later phases)
- **SQL:** `database/schema.sql`, `database/seed.sql`
- **Assets:** single stylesheet `public/assets/css/main.css`

## Layering (later phases)

`public/*.php` → bootstrap → controllers → models (PDO) → views/partials.

No public writes, no public accounts, no reviews/comments/ratings.

## Allergy rule (venue menus)

When a visitor selects an allergen, show only menu items where that allergen status is `does_not_contain`. Hide `contains`, `may_contain`, `cross_contact_risk`, and `unknown`.

## Gallery filtering (MVP)

Venue, location label, and event date only — no `gallery_tags` table.

## Branding

- Visible logo/header: **brunch in detroit**
- Titles, copyright, legal: **brunchindetroit.com**

## Local database + admin (Phase 2)

See **`docs/LOCAL_DATABASE.md`** for import steps.

```bash
cd F:\brunch
php -S localhost:8080 -t public
```

- Public home: `http://localhost:8080/`
- Admin login: `http://localhost:8080/admin/login.php`
- Default dev login: `admin@brunchindetroit.com` / `password` (change before production)

## Next phase (3) entry criteria

- Admin login verified
- Schema imported locally
- Ready to build public directory + venue detail with live data
