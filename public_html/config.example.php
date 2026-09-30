<?php
// =====================================================================
//  CONFIG EXAMPLE — copy this file to config.php and fill in real values.
//  config.php itself is never committed to the repository (see .gitignore),
//  because it holds your live database password and API tokens.
// =====================================================================
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'config.php') { http_response_code(403); exit; }

// ---------------- DATABASE ----------------
define('DB_HOST',    'localhost');
define('DB_NAME',    'your_database_name');
define('DB_USER',    'your_database_user');
define('DB_PASS',    'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// ---------------- SITE ----------------
define('SITE_NAME',     'AcademiaFest');
define('SITE_URL',      '');   // e.g. 'https://www.academiafest.com' (empty = auto-detect)
define('APP_TIMEZONE',  'Asia/Kolkata');
define('SHOW_ERRORS',   true); // set to false once the site is live

// Branding (logo, name, contact details on the registration page, admit card and admin)
// Defaults are in includes/brand.php — uncomment a line here to change it.
// define('BRAND_NAME',     'AcademiaFest');
// define('BRAND_EMAIL',    'info@academiafest.com');
// define('BRAND_PHONE',    '+91 98765 43210');
// define('BRAND_WHATSAPP', '919876543210');
// define('BRAND_ADDRESS',  'New Delhi, India');
define('EVENT_TIME_TEXT', '10:00 AM - 5:00 PM');   // default time on the admit card (used when an event has no timing)

// Course options on the registration form (unchanged)
define('COURSE_OPTIONS', ['BBA', 'BCA', 'B.Tech', 'Fashion Designing', 'MBBS', 'Law', 'MBA', 'PGDM']);

// Pass ID format: AF-2026-00001
define('PASS_PREFIX', 'AF');

// ---------------- WHATSAPP (OMINIFLOW / WPBOX) ----------------
define('WA_API_BASE',      'https://whatsapp.ominiflow.com');
define('WA_API_TOKEN',     'your-whatsapp-api-token'); // put your own token here
define('WA_TEMPLATE_NAME', 'admit_card_pdf');
define('WA_TEMPLATE_LANG', 'en_US');
define('WA_USE_PDF_HEADER', true);
define('WA_BODY_VARS',     ['event', 'pass_id', 'date', 'venue']); // options: name, event, pass_id, date, venue
define('WA_DEBUG',         true);   // true = show the real API error on screen (set false when live)
define('WA_COUNTRY_CODE',  '91');
define('WA_PDF_SUBDIR',    '/storage/admit_cards');
define('WA_PDF_MAX_BYTES', 8 * 1024 * 1024);
define('WA_PDF_KEEP_SECS', 3 * 24 * 3600);

// ---------------- GOOGLE SHEETS LIVE SYNC ----------------
// URL: https://your-site.com/admin/sheets_sync.php?token=THE_TOKEN_BELOW
// In Google Sheets: =IMPORTDATA("the URL above")
define('SHEETS_SYNC_TOKEN', 'change-me-to-any-long-random-value');
