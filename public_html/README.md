# AcademiaFest Website + Event Registration — Setup Guide

## Folder structure (inside the server's public_html)
| Path | Purpose |
|---|---|
| `/` (index.html + assets/) | AcademiaFest main website (React build) |
| `/register/` | Student registration form + admit card + WhatsApp delivery |
| `/register/?event=ID` | Registration form with that event pre-selected |
| `/#events`, `/#about`, `/#founder`, `/#contact` | Direct links to pages of the main website |
| `/admin/` | Admin panel |
| `/api/home.php` | Public JSON with events + gallery for the website |
| `/storage/events/` | Event photos and logos |
| `/storage/gallery/` | "Moments from Our Events" photos |

## 1. Upload
Upload every file inside the `public_html` folder (including the hidden `.htaccess` files) to your hosting's `public_html`.

## 2. Branding
The registration page, the admit card and the admin panel all use the AcademiaFest logo and colours
(same as the main website) — no image files are needed.
Name, email, phone, WhatsApp number and address are set in `includes/brand.php`
(or override any of them in `config.php`, e.g. `define('BRAND_PHONE', '+91 ...');`).

## 3. Settings — `config.php`
Database login, WhatsApp token/template and the Google Sheets sync token.
When the site is live, set `SHOW_ERRORS` and `WA_DEBUG` to `false`.

## 4. Database
The database is created and upgraded **automatically** on the first page load.
It only adds missing tables/columns and fixes incompatible column types — existing data is never deleted.

Other ways to do the same:
- **Admin > System Check > Repair Database** (also shows server settings such as upload limits)
- **phpMyAdmin**: run `database_update.sql` (safe on an existing or empty database, safe to run more than once)
- **Fresh install**: open `/install.php` once to create the first admin account, then delete `install.php`

## 5. Managing the website from the admin panel
- **Upcoming Events** (home page cards): Admin > Create Event / Manage Events > "Website Details" — photo, type, city, timing, description, seats and display order. Only **Active** events appear on the website and in the form.
- **Moments from Our Events**: Admin > Event Gallery — upload many photos at once, edit title/caption/tag, show/hide, reorder, delete. When no photos exist, the default design tiles are shown.
- **Register buttons**: every Register button on the website opens `/register/`; the button on an event card opens the form with that event pre-selected.
- **Images**: JPG, PNG or WEBP up to 20MB. Large phone photos are resized to 1920px and compressed automatically. The server's own limit (`upload_max_filesize` / `post_max_size`) must also be large enough — see System Check.

## 6. Changing the website design (React source)
The source code is in the `website-source/` folder (`src/App.jsx`).
```
cd website-source
npm install
npm run build
```
Then copy the files from `dist/` (`index.html`, `assets/`, `founder.webp`) into `public_html`, replacing the old ones.
Local testing: start the PHP backend with `php -S localhost:8000` inside `public_html`, then run `npm run dev`.

## Requirements
PHP 7.4+ (8.x recommended), MySQL 5.7+/8.x or MariaDB 10.3+, PHP extensions: pdo_mysql, curl, fileinfo, gd (for image resizing), and HTTPS (WhatsApp needs a public link to the PDF).
