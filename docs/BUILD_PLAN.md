# Detroit Brunch (brunchindetroit.com) — Build Plan

PHP 8+ / MySQL / PDO / plain templates. Design reference: `deepsite-originals/home_index.html`.

## Phase status

| Phase | Scope | Status |
|-------|--------|--------|
| 1 | Folder structure, bootstrap/config, shared CSS, header/footer, static home shell | **Done** |
| 2 | PDO wiring, schema import, admin login + session guard | **Done** |
| 3 | Public directory + venue detail (menu + allergy filter) | Planned |
| 4A | Blog list/detail **with SEO + sharing metadata** | Planned |
| 4B | Gallery + modal | Planned |
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

---

## Phase 4A — Blog list + article detail (SEO & sharing)

The public blog list page (`public/blog.php`) and article detail page
(`public/article.php`) must be built with SEO and social-sharing metadata
**from the start**. Do not overbuild an SEO system, but the pages must be
ready for search engines and social sharing.

### Canonical URL foundation

There is currently **no canonical/site-URL helper** in the project. Config
has `'site_domain' => 'brunchindetroit.com'` but no scheme/protocol helper.

Before building the blog pages, add a small, safe canonical helper:

- A `canonical_url(string $path = ''): string` helper (in `app/helpers/`)
  that returns `https://brunchindetroit.com/<path>` (or the dev base URL
  when `environment === 'local'`).
- Used by both `blog.php` and `article.php` to build canonical + OG + Twitter URLs.
- Keep it simple — no routing, no query parsing, just safe string building.

### Header partial changes (required)

`app/views/partials/header.php` currently supports `$pageTitle` and
`$metaDescription` only. Extend it carefully to accept **optional** SEO
variables without breaking existing pages:

- `$canonicalUrl` — renders `<link rel="canonical">` when set.
- `$ogTitle`, `$ogDescription`, `$ogType`, `$ogUrl`, `$ogImage` — render Open Graph tags.
- `$twitterCard` (default `summary`), plus `$twitterTitle`,
  `$twitterDescription`, `$twitterImage` — render Twitter/X card tags.
- `$jsonLd` — raw JSON-LD string rendered inside
  `<script type="application/ld+json">`.

Rules for the header update:

- **Every new variable is optional.** Existing pages (home, directory, venue
  detail, admin) must keep working with their current defaults.
- **Do not output broken/empty meta tags** when data is missing (e.g. skip
  `og:image` entirely when no image is set).
- **Escape all values** with `e()` in meta tag attributes.
- **Do not redesign the visible site header/nav.** Only the `<head>` block changes.

### Blog list page (`public/blog.php`) metadata

- **Title:** `News & Blogs | DetroitBrunch.com`
- **Meta description:** `Detroit brunch guides, restaurant stories, food
  culture, openings, and local dining news from DetroitBrunch.com.`
- **Canonical URL:** built via the new `canonical_url('blog.php')` helper.
- **Open Graph:**
  - `og:title` = page title
  - `og:description` = meta description
  - `og:type` = `website`
  - `og:url` = canonical URL
  - `og:image` = featured post image **if one exists** for the page hero/lead post
- **Twitter/X card:**
  - `twitter:card` = `summary_large_image`
  - `twitter:title`
  - `twitter:description`
  - `twitter:image` = if available
- **No JSON-LD required** on the list page for this phase.

### Article detail page (`public/article.php`) metadata

Each article detail page must include dynamic, per-article metadata:

- **Title:** `ARTICLE TITLE | DetroitBrunch.com` (uses article title)
- **Meta description:** article excerpt (sanitized/truncated if needed)
- **Canonical URL:** `article.php?slug=<POST_SLUG>` (via the helper)
- **Open Graph:**
  - `og:title` = article title
  - `og:description` = article excerpt
  - `og:type` = `article`
  - `og:url` = canonical article URL
  - `og:image` = featured image if available
  - `article:published_time` = ISO 8601 if `published_at` exists
- **Twitter/X card:**
  - `twitter:card` = `summary_large_image`
  - `twitter:title`
  - `twitter:description`
  - `twitter:image` = if available
- **JSON-LD structured data (required)** — `schema.org/BlogPosting`:
  - `headline`
  - `description`
  - `image` (only if available)
  - `datePublished` (only if `published_at` exists)
  - `author` → `name` (if available)
  - `publisher` → `name` = `DetroitBrunch.com`
  - `mainEntityOfPage` → `@type: WebPage`, `@id` = canonical URL

### Share features on article detail

Add a small, non-intrusive **“Share this article”** area to the article
detail page:

- **Copy Link** button — small vanilla JavaScript (no library).
  Copies the canonical URL to clipboard and shows brief "Copied!" feedback.
- **Facebook share** link — `https://www.facebook.com/sharer/sharer.php?u=<canonical-url>`
- **X / Twitter share** link — `https://twitter.com/intent/tweet?text=<title>&url=<canonical-url>`

Rules:

- **No third-party share widgets** (no AddThis, ShareThis, etc.).
- **No tracking scripts** (no analytics, pixels).
- **No comments, no ratings** in this phase.
- All share URLs must URL-encode their parameters.

### Implementation notes (applies to both pages)

- Use escaped values in all meta tags and JSON-LD string fields.
- Do not output broken empty meta tags if data is missing.
- Keep it simple and compatible with PHP templates (plain `<?= e(...) ?>`).
- The header partial's new variables must all default safely so existing
  pages continue to render without changes.

### Out of scope for Phase 4A

- No SEO admin UI
- No sitemap.xml / robots.txt generation (those are Phase 7)
- No image optimization / responsive `srcset` for OG images
- No real comments/reviews/Ratings/RSVP
- No structured data beyond the single `BlogPosting` on article detail

### Files in scope for Phase 4A

- `app/helpers/` — add canonical URL helper (new file)
- `app/views/partials/header.php` — extend `<head>` with optional SEO vars
- `public/blog.php` — new, with full SEO metadata
- `app/views/blog.php` (or inline) — blog list view markup
- `public/article.php` — new, with dynamic SEO + JSON-LD + share buttons
- `app/views/article.php` (or inline) — article detail view markup
- `public/assets/css/main.css` — append share-button styles only
- Optional: `public/assets/js/share.js` — tiny Copy Link script (or inline)

### Files NOT in scope for Phase 4A

- Database schema, seed files, admin CRUD
- Home, directory, venue detail, venue tabs, menu
- Header/footer visible layout (only the `<head>` metadata block changes)
- Gallery pages (Phase 4B)
