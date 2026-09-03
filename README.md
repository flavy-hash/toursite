# TWINS AFRICAN Travel

Website and admin panel for a Tanzanian tour operator — safaris in the Serengeti and
Ngorongoro, Kilimanjaro climbs and Zanzibar beach escapes.

Staff manage tour packages, enquiries, reviews, subscribers and the site navigation
from an admin panel; the public site is driven entirely from that data.

## Stack

| | |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Admin | Filament 5 |
| Front end | Blade + Tailwind CSS 4, built with Vite |
| Database | MySQL |
| Tests | PHPUnit — 127 feature tests |

## Getting started

```bash
git clone <your-repo> twins-african
cd twins-african

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Point the database settings in `.env` at a MySQL database, then:

```bash
php artisan migrate --seed     # schema + starter packages, navigation and reviews
php artisan storage:link       # serves uploaded images from /storage
npm run build                  # or: npm run dev
php artisan serve
```

Create an admin account — **both steps are needed**, since panel access is gated on
the `is_admin` flag rather than merely having an account:

```bash
php artisan make:filament-user
php artisan tinker --execute="App\Models\User::firstWhere('email','you@example.com')->update(['is_admin' => true]);"
```

The site runs at `http://127.0.0.1:8000`, the panel at `/admin`.

## Public routes

| Route | Purpose |
|---|---|
| `/` | Home — hero carousel, destinations, featured packages, reviews, newsletter |
| `/tours` | All packages, filterable by category, region, tier and difficulty |
| `/tours/{slug}` | One package — itinerary, inclusions, gallery, booking panel |
| `/reviews` | Traveller reviews, rating summary, and the review form (modal) |
| `/inquiry` | Booking enquiry form, pre-selects a package via `?tour=slug` |
| `POST /subscribe` | Newsletter sign-up |
| `/sitemap.xml` | Generated from published packages |

## Admin panel

Sign in at `/admin`.

**Tours**
- **Tour Packages** — every package. Tabbed editor for details, imagery, content and
  publishing, with a drag-reorderable day-by-day itinerary builder.
- **Kilimanjaro / Zanzibar / Southern Circuit** — the same packages narrowed to one
  region. Anything created inside a section is stamped with that region, so it lands
  under the matching navigation link automatically.

**Site**
- **Navigation** — labels, links, order and visibility for the header bar and the
  mobile tab bar, including each dropdown's heading, copy, photo and links.
- **Reviews** — moderation queue. Submissions arrive unpublished; Approve puts them
  live, Feature promotes them to the homepage.
- **Subscribers** — newsletter list with unsubscribe/resubscribe.

**Enquiries** — booking enquiries with a one-click Confirm and a status pipeline
(new → contacted → quoted → booked → closed). The sidebar badge counts unhandled ones.

The dashboard carries stat cards, an enquiries-over-time line chart, enquiries by
package, packages by category, and the latest enquiries.

## How content works

Most content lives in the database and is edited in the panel. `config/site.php`,
`config/tours.php` and `config/seo.php` hold **starter content and settings**:

- `config/tours.php` and the `nav` / `stories` keys in `config/site.php` are only read
  by the seeders. Once seeded, editing them changes nothing — the database is
  authoritative.
- `config/site.php` still owns the brand name, contact details, page header images,
  homepage destinations, pillars and footer links.
- `config/seo.php` owns titles, descriptions, the default share image and the
  organisation record used for structured data.

Seeders run only while their table is empty, so re-running `db:seed` will never
overwrite or duplicate content edited in the panel.

### Images

Uploads go to the public disk (`storage/app/public`) and are served through the
`/storage` symlink. Image URLs are deliberately root-relative so they resolve against
whatever host is serving the request — see `App\Support\Media`.

Artwork committed under `/public` (the original seeded photography) can be moved onto
the upload disk so it becomes editable in the panel:

```bash
php artisan media:import-legacy --dry-run   # preview
php artisan media:import-legacy
```

## Email

Subscribers get a welcome email on sign-up, and **Subscribers → Send newsletter** in the
panel broadcasts a message about chosen packages to everyone on the list. Every email
carries a signed, non-expiring unsubscribe link.

Out of the box `MAIL_MAILER=log`, so **nothing is delivered** — mail is written to
`storage/logs/laravel.log`. To send for real, fill in the SMTP block in `.env`:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=you@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS="hello@yourdomain.com"
```

Gmail needs an [App Password](https://myaccount.google.com/apppasswords) rather than
your normal password.

Check it end to end — this reports the transport actually in use and any connection
error, rather than failing silently:

```bash
php artisan mail:test you@example.com
```

`QUEUE_CONNECTION=sync` means mail sends during the request, so nothing extra is needed.
For production, set `QUEUE_CONNECTION=database` and run a worker so a slow mail server
never delays a page load:

```bash
php artisan queue:work
```

## Testing

```bash
php artisan test
```

Tests run against in-memory SQLite (see `phpunit.xml`), so they never touch your MySQL
data. Coverage spans the public pages, booking and review submission, moderation,
admin resources and actions, dashboard widgets, navigation, SEO output and the sitemap.

## Not yet built

- `/about`, `/contact`, `/destinations/*` and `/planning` are linked in the navigation
  but have no routes yet — they 404.
- **Email needs SMTP credentials.** Subscribing sends a welcome email and the admin
  can broadcast a newsletter, but `MAIL_MAILER=log` ships as the default, which writes
  mail to `storage/logs/laravel.log` and transmits nothing. See **Email** above.
- Enquiries and review submissions are stored but do not notify anyone by email yet.
- Tour pages show a rating and review count from columns on the package itself; these
  are not yet derived from the reviews table.
- The seeded reviews are sample content (`source: sample`) — delete them once genuine
  reviews arrive.

## Before deploying

- Set `APP_ENV=production`, `APP_DEBUG=false` and a real `APP_URL`.
- Replace the placeholder contact details and WhatsApp number in `config/site.php`.
- Configure a real mail driver.
- `npm run build` and `php artisan config:cache route:cache view:cache`.
