<?php
// =====================================================================
//  includes/system_checks.php — Server environment checks
//  Used by install.php and admin/system_check.php
// =====================================================================

/** Convert php.ini size values like "8M" to bytes */
function ini_size_to_bytes($v) {
    $v = trim((string)$v);
    if ($v === '') return 0;
    $n = (float)$v;
    switch (strtolower(substr($v, -1))) {
        case 'g': $n *= 1024;
        case 'm': $n *= 1024;
        case 'k': $n *= 1024;
    }
    return (int)$n;
}

/** Returns [ [type, message], ... ] */
function environment_checks() {
    $log = [];
    $root = dirname(__DIR__);

    $log[] = version_compare(PHP_VERSION, '7.4', '>=')
        ? ['ok', 'PHP version ' . PHP_VERSION]
        : ['err', 'PHP ' . PHP_VERSION . ' is too old. Please select PHP 8.x in your hosting panel.'];

    foreach (['pdo_mysql' => 'Database', 'curl' => 'WhatsApp sending', 'fileinfo' => 'Image upload checks', 'gd' => 'Automatic image resizing', 'mbstring' => 'Text handling'] as $ext => $use) {
        $log[] = extension_loaded($ext)
            ? ['ok', "PHP extension '$ext' enabled ($use)"]
            : [$ext === 'gd' || $ext === 'mbstring' ? 'warn' : 'err', "PHP extension '$ext' is disabled ($use). Enable it in your hosting panel (PHP Configuration > Extensions)."];
    }

    $up = ini_get('upload_max_filesize');
    $post = ini_get('post_max_size');
    $log[] = ini_size_to_bytes($up) >= 8 * 1048576
        ? ['ok', "Upload limit per file: $up (form limit: $post)"]
        : ['warn', "Upload limit per file is only $up (form limit: $post). Increase 'upload_max_filesize' and 'post_max_size' to 64M in your hosting panel (PHP Configuration > PHP Options)."];

    foreach (['/storage/events', '/storage/gallery', '/storage/admit_cards', '/storage/main'] as $d) {
        $path = $root . $d;
        if (!is_dir($path)) @mkdir($path, 0755, true);
        $log[] = is_writable($path) ? ['ok', "Folder writable: $d"] : ['err', "Folder is NOT writable: $d (set permission to 755)"];
    }


    if (WA_API_TOKEN === '' ) $log[] = ['warn', 'WhatsApp API token is empty in config.php'];
    if (SHOW_ERRORS) $log[] = ['warn', 'SHOW_ERRORS is ON in config.php. Turn it off (false) after testing.'];
    if (file_exists($root . '/install.php')) $log[] = ['warn', 'install.php still exists. Delete it after setup for security.'];
    return $log;
}
