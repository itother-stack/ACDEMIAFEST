<?php
// =====================================================================
//  includes/brand.php — AcademiaFest branding shared by the registration
//  page, the admit card and the admin panel (same as the main website).
//  Any value can be overridden by defining it in config.php.
// =====================================================================
if (!defined('BRAND_NAME'))     define('BRAND_NAME', 'AcademiaFest');
if (!defined('BRAND_TAGLINE'))  define('BRAND_TAGLINE', 'Education Events');
if (!defined('BRAND_EMAIL'))    define('BRAND_EMAIL', 'info@academiafest.com');
if (!defined('BRAND_PHONE'))    define('BRAND_PHONE', '+91 90153 91313');
if (!defined('BRAND_WHATSAPP')) define('BRAND_WHATSAPP', '919015391313');   // digits only, with country code
if (!defined('BRAND_ADDRESS'))  define('BRAND_ADDRESS', 'Laxmi Niwas, Baily Road, Patna');

/**
 * The AcademiaFest "A" logo mark (gold gradient icon, transparent background).
 * $color/$mask are kept as accepted (unused) parameters so existing calls
 * like brand_logo_svg(46) keep working unchanged.
 */
function brand_logo_svg($size = 36, $color = '#c9a227', $mask = '#ffffff') {
    $w = (int)$size;
    $h = (int)round($size * 0.89); // logo-icon.png is 158x141
    return '<img src="/logo-icon.png" width="' . $w . '" height="' . $h . '" alt="' . htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') . ' logo" style="display:inline-block;width:' . $w . 'px;height:' . $h . 'px;object-fit:contain;">';
}

/** The full logo lockup (icon + "ACADEMIAFEST" + tagline) — for spots with no separate brand text next to it */
function brand_logo_full($width = 220) {
    $w = (int)$width;
    $h = (int)round($width * (211 / 298));
    return '<img src="/logo-full.png" width="' . $w . '" height="' . $h . '" alt="' . htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;width:' . $w . 'px;height:' . $h . 'px;object-fit:contain;">';
}

/** Favicon URL (the logo icon) */
function brand_favicon_uri() {
    return '/logo-icon.png';
}
