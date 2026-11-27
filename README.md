# Subka Sahara Welfare Foundation website

The website and admin panel for Subka Sahara Welfare Foundation (SSWF), Gilgit-Baltistan. It follows the *SSWF Website Master Plan* (25 Nov 2026).

- **Public site:** Laravel 13 with Blade, Livewire 4, Alpine.js and Tailwind CSS 4, styled with the "Karakoram Hope" tokens in `resources/css/app.css`.
- **Admin panel:** Filament 5 at `/admin`, with four roles: Super Admin, Editor, Finance and Services.
- **Content language:** English. Posts marked Urdu show right-to-left in Noto Nastaliq Urdu and Noto Naskh Arabic. These fonts load only on Urdu content.

## Requirements

- PHP 8.3 or newer (8.4 recommended) with these extensions: curl, exif, fileinfo, gd, intl, mbstring, openssl, pdo_mysql, sodium, zip.
- Composer 2
- Node.js 20 or newer
- MySQL 8. Locally, the MariaDB that comes with XAMPP also works.

## Set up locally

```sh
composer install
npm install
cp .env.example .env          # then set DB_* for your database
php artisan key:generate
php artisan migrate --seed    # tables, starting settings, admin roles and the plan's starting content
php artisan storage:link      # makes uploaded photos visible
php artisan sswf:create-admin # first Super Admin, asks for email, name and password
npm run build                 # or `npm run dev` while editing
php artisan serve
```

The site runs at http://localhost:8000 and the admin panel at http://localhost:8000/admin.

Photos are resized to WebP in the background. Run `php artisan queue:work` alongside the server. Until it runs, pages show the original upload.

### Windows note

If XAMPP's PHP 8.2 comes first on your PATH, `php` runs 8.2 and Composer will refuse to install. Put PHP 8.4 ahead of `C:\xampp\php` in the system PATH, then check with `php -v`.

## Where things are

| What | Where |
| --- | --- |
| Design tokens (colors, radius, fonts) | `resources/css/app.css` (`@theme`) |
| Self-hosted fonts | `vite.config.js` (`fonts`), included with `@fonts` |
| Global layout | `resources/views/components/layouts/app.blade.php` |
| Emergency strip, header, mobile bar, donate band, footer, page header | `resources/views/components/site/` |
| Menu structure and districts | `config/sswf.php` |
| Content models (programs, projects, updates, news, partners) | `app/Models/` |
| Admin screens for content | `app/Filament/Resources/` |
| Public pages for programs, projects and news | `app/Http/Controllers/`, `resources/views/{programs,projects,news}/` |
| Projects filter (program, district, status) | `app/Livewire/ProjectBrowser.php` |
| Starting content from the plan | `database/seeders/ContentSeeder.php` (never overwrites edits) |
| About, Leadership, Transparency pages | `resources/views/about/`, `app/Http/Controllers/AboutController.php` |
| Leadership team (Editors) | Admin → Content → Leadership; advisers start hidden until confirmed |
| Chairman's message | Admin → Settings → Contact & social; hidden until the approved text is entered |
| Reports, audited accounts, certificates and the spending chart (Finance) | Admin → Transparency |
| Contact form messages (Editors) | Admin → Content → Messages; also emailed to the office address in Settings |
| Gallery albums, photos and YouTube videos (Editors) | Admin → Content → Gallery; albums show once they have content |
| Privacy, Refund and Terms text (Editors) | Admin → Content → Pages; seeded from the plan's drafts, awaiting legal review |
| 404 page | `resources/views/errors/404.blade.php` |
| Contact, bank and impact settings | `app/Settings/`, edited at Admin → Settings (Super Admin only) |
| Starting settings values | `database/settings/` |
| Roles | `app/Enums/Role.php`, seeded by `DatabaseSeeder` |

Anything the plan lists under "Facts to confirm" starts empty and is filled in from Admin → Settings once confirmed. That includes the ambulance and WhatsApp numbers, the account title, IBAN, SWIFT code and the Fitrana amount. While the ambulance number is empty, the site shows the office phone. While WhatsApp is empty, WhatsApp buttons are hidden.

## Donations

- **Donate page:** `/donate`, a Livewire form in four steps: type of giving, cause (optional), amount and details, payment. Links can preselect choices, e.g. `/donate?type=zakat&cause=winter-package&amount=10000`. The Zakat calculator is at `/donate/zakat-calculator`.
- **Funds:** each type of giving is its own fund. Zakat and Khums only go to causes Finance has ticked as eligible under Admin → Causes & appeals. A gift to any other cause is flagged under "Zakat/Khums to review", so Finance contacts the donor first.
- **Bank transfer, Raast, JazzCash and Easypaisa (manual mode):**
  - The Donate page shows SSWF's account details, the QR code issued by the provider, a copy button and numbered steps.
  - The donor sends the money and uploads a screenshot. It is stored privately, and only Finance and Super Admin can open it.
  - The donation waits under "Payments to confirm" until Finance finds it in the bank or wallet statement. Confirming emails the PDF receipt.
  - Each method appears once its account number is entered in Admin → Settings → Bank, wallets & Fitrana. Upload only QR codes issued by the bank or wallet provider.
- **Sample details:** local and test copies get clearly labelled sample JazzCash, Easypaisa and Raast numbers and placeholder QR codes from `SamplePaymentDetailsSeeder`, so the flow can be tried. The live site (`APP_ENV=production`) ignores them, and never seeds them, so donors are only ever shown real accounts.
- **Gateway mode (JazzCash, Easypaisa, card):** use this once a merchant account is approved. Set `PAYMENTS_JAZZCASH_MODE=gateway` and so on (see `.env.example`).
  - A donation is marked paid only after the provider's signed callback reaches `/payments/{method}/callback`.
  - Each provider needs a driver in `app/Payments/Gateways` that implements `PaymentGateway`, registered in `GatewayManager`. `FakeGateway` shows the expected shape and lets you try the flow locally.
- **WhatsApp confirmation:**
  - After Finance confirms a payment, a "Send receipt on WhatsApp" button opens WhatsApp (app or WhatsApp Web) on the donor's chat. The message is ready, and Finance presses Send. The same action is on every paid donation.
  - The message holds the receipt number and a permanent signed link to the PDF receipt (`/receipts/{reference}?signature=…`). The link only opens for confirmed donations, and only with the exact signature.
  - Phone numbers are converted to international format by `app/Support/PhoneNumber.php`. When the WhatsApp Business API and an SMS gateway are added, they send the same text automatically.
- **Editable message wording:** Admin → Settings → Donor messages (Super Admin) covers the WhatsApp confirmation, the receipt email, the "receipt received" email and the contact form reply.
  - Placeholders like `{name}`, `{amount}`, `{type}`, `{cause}`, `{reference}`, `{date}` and `{receipt_link}` are filled in for each donor, and there's a live preview with sample details.
  - A message can't be saved without its required placeholders (for example, WhatsApp must keep `{reference}` and `{receipt_link}`) or with an unknown one.
  - An empty box, or "Reset to default", uses the original wording. The defaults, the placeholder lists and the required placeholders are in `app/Messaging/MessageTemplates.php`.
  - The email layout, the details table, the PDF and the footer stay fixed, and donor-typed values are HTML-escaped.
- **Records:** nobody, including Super Admin, can create or delete donation records in the admin. Mistakes are handled with Reject, Mark refunded and Finance notes.
- **Emails:** emails and receipts are queued, so run `php artisan queue:work`. Locally they go to `storage/logs/laravel.log` unless you configure `MAIL_*`.

## SEO and sharing

- **Tags on every page:** a title, description, canonical link and Open Graph and Twitter tags, so WhatsApp and Facebook show a proper preview.
- **Share images:** programs, projects, news and albums use their own cover photo. Other pages use the default share image and description from Admin → Settings → Contact & social → "Search and sharing defaults".
- **Structured data:** the home page describes SSWF as an NGO (schema.org). News articles are described as `NewsArticle`.
- **`/sitemap.xml`:** generated from published content. It is cached for 6 hours and rebuilt when programs, projects, news, albums, pages or photos change. Drafts, empty albums and private pages are left out.
- **`/robots.txt`:** generated by the app. Outside `APP_ENV=production` it blocks everything, and every page carries `noindex`, so staging never appears in Google. On the live site it blocks the admin, payment and thank-you pages and points to the sitemap. Donor thank-you pages, payment pages and the 404 are `noindex` everywhere.

**On launch day** (plan 1.4):

1. Add the site to Google Search Console and submit `https://<domain>/sitemap.xml`.
2. Create or claim the Google Business Profile.
3. Check a WhatsApp and Facebook preview with Facebook's Sharing Debugger.

## Admin security: two-factor login

- **Required for every admin** (`ADMIN_TWO_FACTOR_REQUIRED=true`, the default). At their next sign-in, an admin without it is sent to "Set up two-factor authentication" before they can open anything else.
- **Two choices:**
  - an **authenticator app** (Google or Microsoft Authenticator, and others), with one-time recovery codes for a lost phone
  - **codes by email**, which needs working `MAIL_*` settings
- **After setup:** each admin manages it on their profile page (top-right menu → Profile). The secret and recovery codes are stored encrypted.
- **Password reset:** "Forgot password" on the login page emails a reset link.
- **Tests:** the automated tests switch the requirement off (`phpunit.xml`) and test two-factor sign-in on purpose.

## Backups

**Admin → Settings → Backups** (Super Admin) controls backups without touching the server:

- **Back up now:** makes a backup in the background.
- **Run restore check:** proves the newest backup can be restored.
- **Download:** any backup, through a signed link that's valid for 30 minutes.
- **Automatic backups:** on or off, every day or once a week, at a chosen time (Pakistan time).
- **Retention:** how many days every backup is kept, at least 7.
- **Alert email**, and the **weekly restore check** on or off.
- **Status:** the latest backup, the last restore check result, and warnings when there is no off-site copy or no archive password.

Destinations (`BACKUP_DISKS`) and the archive password (`BACKUP_ARCHIVE_PASSWORD`) stay in `.env`, because they are server secrets. Both buttons need the **queue worker** running; on Forge, add a daemon for `php artisan queue:work`. Automatic backups need the **scheduler**.

The default settings and the commands behind them:

- **Default schedule, Pakistan time** (`routes/console.php` reads it from the settings):
  - 01:30 `sswf:backup`, which runs `backup:run` and then `backup:clean`. It backs up the database and `storage/app/public` and `storage/app/private` (photos, documents, QR codes and payment receipts).
  - 07:00 `sswf:backup --monitor`
  - every Sunday 03:00 `sswf:check-backup`
- **Kept** 30 days by default, then weekly for 8 weeks, monthly for a year and yearly for 2 years.
- **Alerts:** an email goes to the alert address only when a backup fails, is too old (more than a day for daily backups, 8 days for weekly ones), or the restore check fails. Without an alert address set in the admin, `BACKUP_NOTIFY_EMAIL` is used.
- **Scheduler:** the server must run Laravel's scheduler every minute. On Forge, switch on "Scheduler". Elsewhere, add a cron entry for `php artisan schedule:run`.
- **Before launch:**
  1. Set `BACKUP_DISKS` to include an **off-site** disk, for example `backups,s3`. A backup kept only on the same server is lost with it.
  2. Set a strong `BACKUP_ARCHIVE_PASSWORD`, because archives contain payment receipts. Store the password somewhere safe outside the server.
  3. Run `php artisan backup:run`, then `php artisan sswf:check-backup`.
- **`php artisan sswf:check-backup`** restores the newest backup's database into a temporary `<database>_restore_check` database, compares 16 key tables with the live ones, and checks every uploaded file is in the archive. It then removes the scratch database; add `--keep` to inspect it. It never touches live data.
- **Real restore after a disaster:**
  1. Unzip the newest archive with the archive password.
  2. Import `db-dumps/*.sql` into the database.
  3. Copy `public/` and `private/` back into `storage/app/`.
  4. Run `php artisan storage:link` and `php artisan optimize`.

## Tests

```sh
php artisan test
```

### UI audit

With the site running (`php artisan serve`), run:

```sh
node scripts/ui-audit.mjs http://127.0.0.1:8000 storage/ui-audit
```

This opens the installed Google Chrome through Playwright and checks the main pages at 360 px and 1440 px. It looks for:

- accessibility problems (axe-core, including colour contrast)
- sideways scrolling
- tap targets under 44 px
- text under 14 px
- photos without alt text, broken photos
- console errors and failed requests

It also opens the mobile menu and goes through the Donate steps. Screenshots and `report.json` are saved to the output folder.

## Pre-launch page

The earlier coming-soon page lives in `public/coming-soon/` and is served at `/coming-soon` until launch.
