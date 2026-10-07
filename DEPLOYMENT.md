# Deploying UniRide online

UniRide is a **PHP + MySQL** app. It needs a host that runs PHP *and* gives you
a MySQL database. **Vercel and GitHub Pages do NOT run PHP/MySQL** — that is why
your earlier Vercel deploy showed nothing. Use one of the hosts below instead.

The app reads its database credentials from environment variables
(`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`). Locally it falls back to
XAMPP defaults, so nothing changes for local development.

---

## Option A — InfinityFree (easiest, free, no credit card, uses phpMyAdmin)

Closest to your XAMPP workflow. No build config, you just upload files and import
the SQL. Good for a course demo.

1. Create a free account at https://infinityfree.net and create a hosting account
   (you get a free `*.rf.gd` / `*.infinityfreeapp.com` subdomain, or attach your own).
2. In the control panel open **MySQL Databases** and create a database. Note the
   generated **DB name, DB user, host, and password** (free hosts prefix these,
   e.g. `if0_12345678_uniride2`).
3. Open **phpMyAdmin** for that database and **Import** the `uniride2.sql` file
   from this repo. (The dump's name is `uniride2`; the tables import into the DB
   you created.)
4. Tell the app the credentials. InfinityFree does not expose OS env vars, so
   create a file named `.env` in the site root (copy `.env.example`) with the
   values from step 2:

   ```
   DB_HOST=sqlXXX.infinityfree.com
   DB_PORT=3306
   DB_NAME=if0_12345678_uniride2
   DB_USER=if0_12345678
   DB_PASS=your_generated_password
   ```

5. Upload **all the project files** (including `.env`) to the `htdocs/` folder via
   the **File Manager** or FTP.
6. Visit your subdomain. Done.

> Keep GitHub as your source-of-truth repo for version history; InfinityFree
> deploys by upload, not from GitHub.

---

## Option B — Railway (auto-deploys from GitHub, free trial credit)

This matches what you originally wanted: push to GitHub, Railway builds and hosts.

1. Push this folder to a new GitHub repository (see `README.md` → *Publishing to GitHub*).
2. At https://railway.app create a project → **Deploy from GitHub repo** → pick your repo.
3. In the same project click **New → Database → MySQL**. Railway provisions MySQL
   and exposes variables like `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`,
   `MYSQLUSER`, `MYSQLPASSWORD`.
4. Open your **web service → Variables** and map them to the names the app uses:

   ```
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_NAME=${{MySQL.MYSQLDATABASE}}
   DB_USER=${{MySQL.MYSQLUSER}}
   DB_PASS=${{MySQL.MYSQLPASSWORD}}
   ```

5. Import the schema into Railway's MySQL **once**. Easiest way: open the MySQL
   service → **Data** / **Query**, or connect with the provided connection string
   and run `uniride2.sql`. For example, locally:

   ```bash
   mysql -h <MYSQLHOST> -P <MYSQLPORT> -u <MYSQLUSER> -p<MYSQLPASSWORD> <MYSQLDATABASE> < uniride2.sql
   ```

6. Railway starts the app with the included `Procfile`
   (`php -S 0.0.0.0:$PORT -t .`). Open the generated public URL.

> The built-in PHP server is single-threaded — fine for a demo/low traffic. For
> real production load, move to nginx + php-fpm or a VPS.

---

## Option C — Render / any VPS / cPanel shared host

Any host with PHP 8.1+ and MySQL works. Set the five `DB_*` environment variables
(or a `.env` file), import `uniride2.sql`, point the web root at this folder, and
make sure the `pdo_mysql` and `mbstring` PHP extensions are enabled
(`composer.json` already declares them).

---

## After deploying — security checklist (do before sharing publicly)

The app is fine for a demo as-is, but for a public site:

- Use a **dedicated MySQL user**, not `root`.
- Keep real credentials in env vars / `.env` only — never commit them (`.gitignore`
  already excludes `.env`).
- Serve over **HTTPS** (all the hosts above give you HTTPS automatically).
- The forgot-password flow shows the reset link on screen (no email configured).
  Wire up SMTP before real use.
- `uploads/profile/` stores user uploads — ensure your host keeps that folder
  writable and persistent.
