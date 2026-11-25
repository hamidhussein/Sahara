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
| Contact, bank and impact settings | `app/Settings/`, edited at Admin → Settings (Super Admin only) |
| Starting settings values | `database/settings/` |
| Roles | `app/Enums/Role.php`, seeded by `DatabaseSeeder` |

Anything the plan lists under "Facts to confirm" starts empty and is filled in from Admin → Settings once confirmed. That includes the ambulance and WhatsApp numbers, the account title, IBAN, SWIFT code and the Fitrana amount. While the ambulance number is empty, the site shows the office phone. While WhatsApp is empty, WhatsApp buttons are hidden.

## Tests

```sh
php artisan test
```

## Pre-launch page

The earlier coming-soon page lives in `public/coming-soon/` and is served at `/coming-soon` until launch.
