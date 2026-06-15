# Local database setup

## 0. Start MySQL (Windows)

This project expects MySQL on `127.0.0.1:3306` (see `app/config/config.php`).

If admin login shows **“actively refused”** or **connection failed**, MySQL is probably not running.

1. Press `Win + R`, type `services.msc`, press Enter.
2. Find **MySQL80** (or **MariaDB**).
3. Right-click → **Start** (set **Startup type** to Automatic if you use it often).

You need administrator rights to start the service. Alternatively start MySQL from XAMPP/WAMP control panel if you use those instead of MySQL80.

**Quick test** (from project folder):

```bash
php scripts/db-test.php
```

You should see: `Connected. admins table rows: 1` (after seed import).

## 1. Create database

In phpMyAdmin, MySQL Workbench, or the CLI:

```sql
CREATE DATABASE brunchindetroit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 2. Import files (order matters)

1. `database/schema.sql`
2. `database/seed.sql`

### phpMyAdmin

Select the `brunchindetroit` database → **Import** → choose each file.

### Windows (cmd) — if `mysql` is on your PATH

```bat
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS brunchindetroit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p brunchindetroit < database\schema.sql
mysql -u root -p brunchindetroit < database\seed.sql
```

## 3. Configure PHP

Edit `app/config/config.php` with your host, database name, user, and password.

## 4. Default admin (local dev only)

| Field | Value |
|-------|--------|
| Email | `admin@brunchindetroit.com` |
| Password | `password` |

**Change this password before any production deploy.**

## 5. Verify

1. Start the server: `php -S localhost:8080 -t public`
2. Open `http://localhost:8080/admin/login.php`
3. Sign in and confirm the dashboard loads with stat cards (zeros until you add content).

## 6. “Invalid email or password” (but DB is running)

That message means **MySQL connected**, but login did not match a row in `admins`.

| Cause | Fix |
|-------|-----|
| Only `schema.sql` imported | Import `database/seed.sql` |
| Empty `admins` table | Run reset script below |
| Wrong email | Use **`admin@brunchindetroit.com`** exactly |
| Old/corrupt password hash | Run reset script below |

From the project folder:

```bash
php scripts/reset-admin-password.php
```

Optional custom email/password:

```bash
php scripts/reset-admin-password.php admin@brunchindetroit.com password
```

Then sign in again at `/admin/login.php`.
