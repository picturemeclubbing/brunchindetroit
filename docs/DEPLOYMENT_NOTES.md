# Deployment Notes — brunchindetroit.com

InMotion Hosting / cPanel-friendly layout for this project.

## Requirements

- PHP 8.0 or newer
- MySQL or MariaDB
- Apache with `mod_rewrite` (recommended)
- PDO MySQL extension

## Configuration

1. Copy `app/config/config.example.php` to `app/config/config.php` on the server.
2. Set database credentials and `environment` to `production`.
3. Set `debug` to `false` in production so errors are not shown to visitors.
4. Optionally set `base_url` if the site is installed in a subdirectory (usually empty when the domain points at `public/`).

**Do not** commit `app/config/config.php` if it contains real passwords.

## Database

1. Create a database and user in cPanel → MySQL® Databases.
2. Import `database/schema.sql`, then `database/seed.sql`.
3. Change the default admin password before going live (seed is for development only).

## Upload directories

Uploaded images go under:

- `public/uploads/venues/`
- `public/uploads/blog/`
- `public/uploads/gallery/`

Ensure these folders are writable by the web server user. Placeholder `index.html` files block directory listing.

## Deployment option A (preferred)

Point the domain **document root** to the project’s `public/` folder.

Example layout on the server:

```
/home/username/brunch/
  app/
  database/
  public/          ← document root
  deepsite-originals/   (optional, do not deploy)
```

In cPanel: **Domains** → your domain → **Document Root** → set to `/home/username/brunch/public`.

`app/` and `database/` stay outside the web root and are protected by `app/.htaccess` if Apache ever exposes them.

## Deployment option B (fallback)

If you cannot change the document root:

1. Upload **only the contents** of `public/` into `public_html/` (or the domain’s web root).
2. Upload `app/` to a sibling path **outside** `public_html`, e.g. `/home/username/brunch_app/`, if the panel allows it.
3. Edit `public_html/index.php` (and other entry scripts) so `require_once` points to the correct `bootstrap.php` path, **or** place a copy of `app/` above `public_html` and keep the same relative structure:

```
/home/username/
  brunch_app/app/
  public_html/    ← contents of local public/
```

4. Update `require` paths in each `public_html/*.php` file to match where `app/bootstrap.php` lives.

5. Keep `database/` outside `public_html`; import SQL via phpMyAdmin.

**Important:** Never place `app/config/config.php` inside `public_html`.

## Security checklist

- [ ] `debug` = false in production
- [ ] Strong admin password
- [ ] `app/.htaccess` denies direct access
- [ ] `database/.htaccess` denies direct access (if under web root)
- [ ] No PHP execution in `uploads/` (`public/.htaccess` rule)
- [ ] Admin area behind session login (Phase 2+)

## Local development

```bash
cd F:\brunch
php -S localhost:8080 -t public
```

Apache/XAMPP: virtual host document root = `F:\brunch\public`.

## Default admin (development seed only)

After importing `seed.sql`:

- Email: `admin@brunchindetroit.com`
- Password: `password`

Change immediately on production. See `docs/LOCAL_DATABASE.md`.

## SSL

Use cPanel **SSL/TLS** or AutoSSL so `https://brunchindetroit.com` serves over HTTPS before launch.
