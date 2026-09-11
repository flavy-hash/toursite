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
| Tests | PHPUnit — 307 feature tests |

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
| `/about` | About Us — editable in the panel |
| `/about/team` | Our Team — editable page plus the team list |
| `/faq` | Safari FAQ — grouped questions, with FAQPage structured data |
| `/reviews` | Traveller reviews, rating summary, and the review form (modal) |
| `/inquiry` | Booking enquiry form, pre-selects a package via `?tour=slug` |
| `POST /faq/ask` | A visitor's question, into the FAQ moderation queue |
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
- **Pages** — the content of `/about`, `/about/team` and `/faq`: banner, headline,
  opening paragraph and any number of body sections, each with an optional photo.
  Edit-only, because each page's slug has to match a route that exists — the map
  lives in `Page::ROUTES`.
- **Team** — the people on `/about/team`. Drag to reorder, hide someone without
  deleting them, and a member with no photo falls back to their initials.
- **Homepage Video** — the YouTube panel on the homepage. Paste a video ID or any
  YouTube link (watch, youtu.be, embed or Shorts) and the ID is extracted for you;
  the surrounding wording is editable, and switching it off removes the section
  rather than leaving an empty player.
- **FAQ** — the questions on `/faq`. Each has a question, an answer and an optional
  section heading; questions sharing a heading are grouped under it, and a question
  with none falls under "General". Drag to reorder, hide one without losing the
  answer, and filter by section.

  Visitors can ask their own through a popup on `/faq`. Those arrive here with no
  answer, unpublished, sorted to the top of the list and counted by a badge on the
  sidebar. **Answer** writes the reply, sets a section and publishes in one step.
  An unanswered question can never reach the page — the publish toggle is hidden
  on it, the form refuses to publish without an answer, and the page filters on
  the answer rather than trusting the flag.
- **Subscribers** — newsletter list with unsubscribe/resubscribe.

**Enquiries** — booking enquiries with a one-click Confirm and a status pipeline
(new → contacted → quoted → booked → closed). The sidebar badge counts unhandled ones.
Confirming emails the guest their booking confirmation; a row that could not be emailed
is flagged **Not yet emailed** and offers **Resend confirmation**.

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

### Search engines

Every page emits one connected `@graph` — the organisation and the website,
cross-referenced by `@id` — plus a `BreadcrumbList` on inner pages, so a crawler
reads one business with one site rather than guessing that two loose blocks
belong together. The organisation carries a square logo and, once there are
published reviews, an `aggregateRating` drawn from the same rows the reviews page
shows. No rating is claimed while there are none: an `aggregateRating` with a zero
count is an error, not a neutral statement.

Page-specific structured data is pushed by the views themselves — `TouristTrip`
with an `Offer` on a package, `FAQPage` on `/faq`, `ItemList` on `/tours`.

**Sitelinks — the block of sub-pages under a search result — cannot be requested
or marked up.** Google picks them from the site's structure, its internal links
and each page's own title and description. What the site can do is make that
choice easy, which is what the breadcrumbs, the sitemap, the connected graph and
a distinct title and description on every page are for.

Seeders run only while their table is empty, so re-running `db:seed` will never
overwrite or duplicate content edited in the panel.

### Images

Uploads go to the public disk (`storage/app/public`) and are served through the
`/storage` symlink. Image URLs are deliberately root-relative so they resolve against
whatever host is serving the request — see `App\Support\Media`.

### Video in a package gallery

A package's Gallery tab has a **Videos** field alongside the photos. Uploads accept
MP4, WebM, OGG and MOV; on the package page videos render in the same grid as the
photos, ahead of them, with a play badge, and open in the same viewer.

They are kept in a separate `gallery_videos` column rather than mixed into `gallery`
so the photo field keeps its image editor and thumbnails. `Tour::gallery_items`
merges the two for the page.

Filament allows 100 MB per file, but **your server's own limit is usually lower and
wins**. If a video will not upload, that is almost always why — check
`upload_max_filesize` and `post_max_size` in cPanel → **Select PHP Version → Options**,
and raise `max_execution_time` if a large file times out mid-upload.

Artwork committed under `/public` (the original seeded photography) can be moved onto
the upload disk so it becomes editable in the panel:

```bash
php artisan media:import-legacy --dry-run   # preview
php artisan media:import-legacy
```

## Email

Three emails go out:

| Email | Trigger | Type |
| --- | --- | --- |
| Welcome | Someone subscribes | Marketing |
| Newsletter | **Subscribers → Send newsletter** | Marketing |
| Booking confirmed | An enquiry is marked **Booked** | Transactional |

Marketing email carries a signed, non-expiring unsubscribe link. The booking
confirmation does not — a guest who has just booked must receive it whether or not they
take the newsletter — and shows contact details in its footer instead.

**Booking confirmations** are sent by an observer on `Inquiry`, so every route into
"booked" behaves the same: the Confirm button on the list, the view and edit screens,
the bulk action, and the status dropdown on the form. `confirmation_sent_at` records
when the guest was told, which makes the send happen once even if an enquiry is reopened
and confirmed again.

A booking is never rolled back because mail failed. If the send fails, the enquiry is
still booked, the row reads **Not yet emailed**, and **Resend confirmation** retries it
once the mail settings are fixed.

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
php artisan mail:test you@example.com             # the welcome email
php artisan mail:test you@example.com --booking   # the booking confirmation
```

Neither writes anything to the database, so you can preview them freely.

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

- `/contact`, `/destinations/*` and `/planning` are linked in the navigation but have
  no routes yet — they 404.
- **Email needs SMTP credentials.** Subscribing sends a welcome email and the admin
  can broadcast a newsletter, but `MAIL_MAILER=log` ships as the default, which writes
  mail to `storage/logs/laravel.log` and transmits nothing. See **Email** above.
- Nobody is notified when a **new** enquiry or review arrives — staff have to look.
  (Confirming a booking does email the guest; see **Email** above.)
- Tour pages show a rating and review count from columns on the package itself; these
  are not yet derived from the reviews table.
- The seeded reviews are sample content (`source: sample`) — delete them once genuine
  reviews arrive.

## Before deploying

- Set `APP_ENV=production`, `APP_DEBUG=false` and a real `APP_URL`.
- Replace the placeholder contact details and WhatsApp number in `config/site.php`.
- Configure a real mail driver.
- `npm run build` and `php artisan config:cache route:cache view:cache`.
