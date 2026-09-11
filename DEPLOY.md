# Deploying to Namecheap shared hosting

The working copy is around 1 GB, but almost none of that belongs on a server:
git history, `node_modules`, dev-only Composer packages and their test fixtures.
The build script strips all of it.

## 1. Build the package

```powershell
npm run build                                   # compile CSS/JS first
powershell -ExecutionPolicy Bypass -File scripts\build-deploy-package.ps1
```

It prints each step's size and leaves a zip at
`%TEMP%\twins-deploy\twins-african-deploy.zip`.

Add `-OptimiseImages` to downscale the uploaded photos in the package. Admin
uploads arrive straight off a phone or camera at several megabytes each, which
no browser needs; capping them at 1920px on the long edge took them from 60 MB
to 12 MB and makes the live site noticeably faster to load:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\build-deploy-package.ps1 -OptimiseImages
```

Code only, if you would rather move the uploaded photos separately over FTP:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\build-deploy-package.ps1 -SkipUploads
```

Nothing in your project is touched — the package is staged in a temp folder,
your originals in `storage/app/public` are left at full resolution, and your
local `vendor/` keeps its dev dependencies so tests still run.

Roughly what the steps save, starting from a ~1 GB working copy:

| Step | Size |
| --- | --- |
| Code, config and uploads (no `.git`, no `node_modules`) | 135 MB |
| After downscaling photos and clearing caches and logs | 24 MB |
| Plus production-only `vendor/` | 78 MB |
| **Zipped, ready to upload** | **37 MB** |

## 2. Set up the database

In cPanel → **MySQL Databases**, create a database and a user, and add the user
to the database with all privileges. Note the names: cPanel prefixes them, so
`twins` becomes something like `cpaneluser_twins`.

Export your local data and import it in **phpMyAdmin**:

```bash
mysqldump -u root -p tour > twins.sql
```

## 3. Upload

In cPanel → **File Manager**, upload the zip and extract it.

**Do not extract into `public_html`.** Laravel's `public/` folder is the only
part that should be web-reachable; everything else — including `.env` with your
database and mail passwords — must sit above it.

Recommended layout:

```
/home/youruser/
├── twinsafrican/          ← extract the zip here
│   ├── app/  config/  storage/  vendor/  …
│   └── public/            ← contents go to public_html
└── public_html/           ← move the contents of twinsafrican/public here
```

Move everything from `twinsafrican/public/` into `public_html/`, then edit
`public_html/index.php` and repoint the two require paths one level up:

```php
require __DIR__.'/../twinsafrican/vendor/autoload.php';
$app = require_once __DIR__.'/../twinsafrican/bootstrap/app.php';
```

If your host only allows a single directory, the alternative is to set the
domain's document root to `twinsafrican/public` in cPanel → **Domains**, which
is cleaner and needs no edit to `index.php`.

## 4. Configure

Create `.env` with the block below. Use it as the whole file rather than editing
`.env.example` line by line — that template ships Laravel's defaults, including
`DB_CONNECTION=sqlite`, and leaving that in place makes **every page a 500**.

```dotenv
APP_NAME="TWINS AFRICAN Travel"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cpaneluser_twins
DB_USERNAME=cpaneluser_twins
DB_PASSWORD=…

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=…
MAIL_PASSWORD=…
MAIL_FROM_ADDRESS="…"
MAIL_FROM_NAME="${APP_NAME}"
MAIL_TIMEOUT=15
```

Three of these are load-bearing and easy to miss:

- **`DB_CONNECTION=mysql`** — the default is `sqlite`. Sessions and the cache
  both live in the database, so a wrong value breaks the site outright, not just
  the pages that read content.
- **`APP_KEY`** — left blank here and generated on the server in step 5. Without
  it Laravel cannot decrypt the session cookie and returns a 500.
- **`APP_DEBUG=false`** — with it on, any error page shows your database
  credentials to whoever triggered it.

The `.env` is deliberately **not** in the zip, so your Gmail app password and
database password are never sitting in a file on your desktop or in a download.
`APP_KEY` is not in `.env.example` either — generate one on the server (step 5).

## 5. Finish on the server

Via cPanel → **Terminal** (or SSH):

```bash
cd ~/twinsafrican
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If there is no terminal, Namecheap's **Setup PHP App** offers a command runner;
failing that, `key:generate` can be replaced by pasting your local `APP_KEY`.

`storage:link` is required — uploaded photos are served through it, and without
it every admin-uploaded image 404s.

## 6. Check permissions

```bash
chmod -R 775 storage bootstrap/cache
```

Laravel writes sessions, caches and logs here. Nothing else needs to be
writable.

## 7. Verify

```bash
php artisan mail:test you@example.com
```

Then load the site and check: the homepage, a package page, `/admin` login, and
that an uploaded photo appears.

---

## Troubleshooting a 500

`APP_DEBUG=false` shows a blank error page on purpose, so start by reading the
actual error rather than guessing:

```bash
cd ~/twinsafrican
tail -n 40 storage/logs/laravel.log
```

If that file is empty or missing, Laravel failed before logging was ready — the
cause is almost always permissions or `.env`. Check cPanel → **Metrics → Errors**
for the PHP-level message instead.

Ordered by how often each one is the answer:

**1. `DB_CONNECTION` is still `sqlite`.** The most likely cause on a first
deploy, because `.env.example` ships that default and `config/database.php`
falls back to it. Sessions and the cache both live in MySQL, so every request
fails, not just content pages. The log says:

```
Database file at path [cpaneluser_twins] does not exist.
Ensure this is an absolute path to the database.
```

— SQLite reading your MySQL database *name* as a filename. Set
`DB_CONNECTION=mysql` in `.env`, then
`php artisan config:clear && php artisan config:cache`.

**2. `APP_KEY` is empty.** The log says *No application encryption key has been
specified*. Run `php artisan key:generate`.

**3. `storage` or `bootstrap/cache` is not writable.** The log will be missing
entirely, since Laravel cannot write it. Run `chmod -R 775 storage
bootstrap/cache` (step 6).

**4. The migrations have not run.** The log names a missing `sessions`, `cache`
or `tours` table. Run `php artisan migrate --force`.

**5. The *web* PHP is older than 8.2.** Filament and Laravel 12 both require
8.2. Checking with `php -v` in the terminal is not enough — cPanel gives the
shell and the web server separate PHP versions, and they are routinely
different. This is what broke the first deployment of this site: the CLI was on
8.2.33 while the domain was served by 8.0.

The giveaway is in the error log, in the `include_path` of any PHP error:

```
include_path='.:/opt/alt/php80/usr/share/pear:…'
                        ^^^^^
```

Fix it in cPanel → **Select PHP Version** (or **MultiPHP Manager**), pick the
domain, set **8.2**, and enable `zip`, `gd`, `intl`, `bcmath`, `mbstring` and
`pdo_mysql`. To confirm what the web is actually running:

```bash
echo '<?php echo PHP_VERSION;' > ~/yourdomain.com/phpcheck.php
curl -s https://yourdomain.com/phpcheck.php; echo
rm ~/yourdomain.com/phpcheck.php
```

**6. A stale config cache.** If you edited `.env` after running `config:cache`,
the old values are still in force. `php artisan config:clear`, fix `.env`, then
`config:cache` again.

**7. `index.php` points at the wrong paths.** Only if you moved `public/` into
`public_html` by hand — re-check the two `require` lines from step 3. A mistake
here gives a *blank white page* or a "failed to open stream" error rather than
Laravel's 500.

To see the real exception in the browser while you are fixing it, set
`APP_DEBUG=true`, run `php artisan config:clear`, reload the page, then **set it
straight back to `false`** and re-cache. Leaving it on in production exposes
your database and mail passwords on every error page.

## The live layout

`twinsafricantravel.com` is an addon domain, so its web root is
`~/twinsafricantravel.com` rather than `~/public_html`. The account has no way
to move that, so the application sits beside it instead:

```
/home/snowhdxr/
├── twinsafrican_app/          ← the application. NOT web-reachable.
│   ├── app/  bootstrap/  config/  database/  resources/  routes/
│   ├── storage/  vendor/  artisan  .env
│   └── storage/app/public/    ← uploaded photos
└── twinsafricantravel.com/    ← the web root (what public/ used to hold)
    ├── index.php              ← rewritten to require ../twinsafrican_app
    ├── .htaccess  build/  css/  js/  assets/  fonts/  robots.txt
    └── storage → /home/snowhdxr/twinsafrican_app/storage/app/public
```

Two consequences worth remembering:

- `index.php` is **not** the stock Laravel one. It uses absolute paths into
  `twinsafrican_app` and calls `$app->usePublicPath(__DIR__)`, without which
  `asset()` and the Vite manifest lookup both fail. A redeploy that overwrites
  it with the packaged copy will take the site down.
- `php artisan storage:link` cannot span the split. The symlink is made by hand:
  `ln -sfn ~/twinsafrican_app/storage/app/public ~/twinsafricantravel.com/storage`

## Shipping an update

For a release that adds no Composer packages, `scripts/build-update-package.ps1`
builds a ~0.4 MB archive of just the changed code and compiled assets, instead
of the full 37 MB. It carries two folders: `app/` goes to `~/twinsafrican_app`,
`web/` goes to `~/twinsafricantravel.com`.

Three things bit us the first time and will again if forgotten:

**1. Regenerate the autoloader whenever a release adds a class.** `vendor/` is
installed with `--classmap-authoritative`, which switches PSR-4 lookup off
entirely — only classes already in the classmap can load. Copying in a new
model or controller is not enough; every page touching it returns 500 and
`db:seed` reports *Target class does not exist*. On the server:

```bash
cd ~/twinsafrican_app
composer dump-autoload --optimize --classmap-authoritative --no-dev
```

If composer is not installed: `curl -sS -o composer.phar
https://getcomposer.org/composer-stable.phar` first, then `php composer.phar
dump-autoload ...`. Run it **before** the seeders and before re-caching.

**2. `.env` has Windows line endings.** Every value ends with a stray carriage
return, so anything reading it with `grep`/`cut` gets `password\r` and fails
with a garbled error — the CR rewinds the terminal line, so the message reads
as nonsense. Pipe through `tr -d '\r'` when reading it from a shell script.

**3. Composite indexes containing a string column can exceed the key limit.**
This host caps keys at 1000 bytes; a `VARCHAR(255)` under utf8mb4 is 1020 on
its own. A migration that passes locally on MySQL 8 can fail here. Keep string
columns out of composite indexes, or give them an explicit prefix length.

The order that works:

```bash
cd ~/twinsafrican_app
composer dump-autoload --optimize --classmap-authoritative --no-dev
php artisan migrate --force
php artisan db:seed --class=SomeSeeder --force
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## Redeploying

Re-run the build script and upload the zip, extracting into
`~/twinsafrican_app`. **Do not overwrite**:

- `.env`
- `storage/app/public/` — your uploaded photos live here
- `~/twinsafricantravel.com/index.php` — the rewritten front controller above

Web assets (`build/`, `css/`, `js/`, `assets/`, `fonts/`) go from the package's
`public/` into `~/twinsafricantravel.com/`, not into the app folder.

Then clear the caches so the new code is used:

```bash
cd ~/twinsafrican_app
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## Notes

- **PHP 8.2 or newer** is required. Set it in cPanel → **Select PHP Version**,
  and enable the `zip`, `gd`, `intl` and `bcmath` extensions.
- **Queues**: `QUEUE_CONNECTION=sync` sends mail during the request, which adds
  a few seconds to newsletter sign-ups. Shared hosting rarely allows a permanent
  worker; a cPanel cron running `php artisan queue:work --stop-when-empty`
  every few minutes is the usual compromise.
- **Uploads** in `storage/app/public` are the bulk of the package size. If the
  File Manager rejects the upload, build with `-SkipUploads` and move that
  folder over FTP separately.
- **Rebuild the zip only with the script.** Windows PowerShell's own
  `Compress-Archive` writes paths with backslashes, which Linux extractors read
  as part of the file name — the archive then unpacks as 14,000 files in one
  folder instead of a directory tree. The script writes the entries itself to
  avoid that.
- **`storage/logs` must exist** on the server. It ships holding only a
  `.gitignore`, because a zip cannot carry an empty folder and Laravel cannot
  write its log without one.
