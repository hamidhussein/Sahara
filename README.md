# Sab Ka Sahara Welfare Foundation website

The website and admin panel for Sab Ka Sahara Welfare Foundation (SSWF), Gilgit-Baltistan. It follows the *SSWF Website Master Plan* (25 Nov 2026).

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
| Contact, bank and impact settings | `app/Settings/`, edited at Admin → Settings (Super Admin only) |
| Starting settings values | `database/settings/` |
| Roles | `app/Enums/Role.php`, seeded by `DatabaseSeeder` |

Anything the plan lists under "Facts to confirm" starts empty and is filled in from Admin → Settings once confirmed. That includes the ambulance and WhatsApp numbers, the account title, IBAN, SWIFT code and the Fitrana amount. While the ambulance number is empty, the site shows the office phone. While WhatsApp is empty, WhatsApp buttons are hidden.

## Donations

- **Donate page:** `/donate`, a Livewire form in four steps: type of giving, cause (optional), amount and details, payment. Links can preselect choices, e.g. `/donate?type=zakat&cause=winter-package&amount=10000`. The Zakat calculator is at `/donate/zakat-calculator`.
- **Funds:** each type of giving is its own fund. Zakat and Khums only go to causes Finance has ticked as eligible under Admin → Causes & appeals. A gift to any other cause is flagged under "Zakat/Khums to review", so Finance contacts the donor first.
- **Bank transfer / Raast:** the donor uploads a receipt. It is stored privately and only Finance and Super Admin can open it. The donation waits under "Transfers to confirm" until Finance checks the bank statement. Confirming emails the PDF receipt.
- **Online methods (JazzCash, Easypaisa, card):** each one is hidden until it's switched on in `.env` (see `.env.example`). A donation is marked paid only after the provider's signed callback reaches `/payments/{method}/callback`. Each provider needs a driver in `app/Payments/Gateways` that implements `PaymentGateway`, registered in `GatewayManager`. `FakeGateway` shows the expected shape and lets you try the whole flow locally.
- **Records:** nobody, including Super Admin, can create or delete donation records in the admin. Mistakes are handled with Reject, Mark refunded and Finance notes.
- **Emails:** emails and receipts are queued, so run `php artisan queue:work`. Locally they go to `storage/logs/laravel.log` unless you configure `MAIL_*`.

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
