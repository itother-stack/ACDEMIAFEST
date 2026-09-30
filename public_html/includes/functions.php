<?php
// =====================================================================
//  Shared helpers: Pass/QR data + WhatsApp (OminiFlow / WPBox)
//  Used by both the registration form (register/index.php) and the admin (admin/pass.php)
// =====================================================================
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/brand.php';

// Upgrade the database automatically if this version of the code needs new tables/columns
ensure_schema($pdo);

function site_base_url() {
    if (SITE_URL !== '') return rtrim(SITE_URL, '/');
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    return ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
}

/** Values are stored HTML-escaped; decode them once before displaying */
function clean_text($s) {
    $s = (string)$s;
    for ($i = 0; $i < 3 && preg_match('/&[a-zA-Z0-9#]+;/', $s); $i++) {
        $s = html_entity_decode($s, ENT_QUOTES, 'UTF-8');
    }
    return $s;
}

function make_pass_id($regId, $createdAt = null) {
    $year = $createdAt ? date('Y', strtotime($createdAt)) : date('Y');
    return PASS_PREFIX . '-' . $year . '-' . str_pad((string)$regId, 5, '0', STR_PAD_LEFT);
}

/**
 * Builds the admit card data from a registration row (+ its event).
 * Same output after form submit and in the admin "View Pass" page.
 */
function build_pass_data(array $reg, $evt, $passId) {
    $pos = function ($val, $default) {
        return ($val !== null && $val !== '') ? max(0, min(100, (int)$val)) : $default;
    };
    $eventName  = !empty($evt['event_name'])    ? $evt['event_name']    : 'EDUCATION EXPO';
    $eventDate  = !empty($evt['event_date'])    ? $evt['event_date']    : 'TBD';
    $eventVenue = !empty($evt['event_venue'])   ? $evt['event_venue']   : 'TBD';
    $eventTime  = !empty($evt['event_time'])    ? $evt['event_time']    : EVENT_TIME_TEXT;

    $qrRawText = "=== " . strtoupper(BRAND_NAME) . " ADMIT CARD ===\n"
               . "Reg No: {$passId}\n"
               . "Event: {$eventName}\n"
               . "Name: {$reg['student_name']} ({$reg['gender']})\n"
               . "Mobile: {$reg['mobile_number']}\n"
               . "WhatsApp: {$reg['whatsapp_number']}\n"
               . "Email: {$reg['email_id']}\n"
               . "City: {$reg['city']}\n"
               . "Parent's No: " . ($reg['parent_mobile'] ?: 'N/A') . "\n"
               . "Interest: " . ($reg['interested_in'] ?: 'N/A') . "\n"
               . "Courses: " . ($reg['courses'] ?: 'N/A') . "\n"
               . "Date: {$eventDate}\n"
               . "Time: {$eventTime}\n"
               . "Venue: {$eventVenue}\n"
               . "Source: " . ($reg['source'] ?: 'N/A');

    return [
        'pass_id'      => $passId,
        'student_name' => clean_text($reg['student_name']),
        'event_name'   => clean_text($eventName),
        'event_date'   => clean_text($eventDate),
        'event_venue'  => clean_text($eventVenue),
        'event_time'   => clean_text($eventTime),
        'qr_image_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' . urlencode(clean_text($qrRawText)),
        // Custom Entry Pass template for this event (optional) — background image +
        // where the name / registration number / QR code sit on it, as % of width/height.
        'entry_bg'     => !empty($evt['entry_pass_bg']) ? $evt['entry_pass_bg'] : null,
        'entry_pos'    => [
            'name_x'  => $pos($evt['entry_pass_name_x']  ?? null, 50),
            'name_y'  => $pos($evt['entry_pass_name_y']  ?? null, 50),
            'reg_x'   => $pos($evt['entry_pass_reg_x']   ?? null, 50),
            'reg_y'   => $pos($evt['entry_pass_reg_y']   ?? null, 58),
            'qr_x'    => $pos($evt['entry_pass_qr_x']    ?? null, 85),
            'qr_y'    => $pos($evt['entry_pass_qr_y']    ?? null, 40),
            'qr_size' => $pos($evt['entry_pass_qr_size'] ?? null, 16),
        ],
    ];
}

// ---------------------------------------------------------------------
//  WHATSAPP
// ---------------------------------------------------------------------

/** Returns 91XXXXXXXXXX, or null if the number is invalid */
function wa_normalize_phone($raw) {
    $d = preg_replace('/\D+/', '', (string)$raw);
    $cc = WA_COUNTRY_CODE;
    if (strlen($d) === 10) return $cc . $d;
    if (strlen($d) === 11 && $d[0] === '0') return $cc . substr($d, 1);
    if (strlen($d) === strlen($cc) + 10 && strpos($d, $cc) === 0) return $d;
    return null;
}

function wa_mask_phone($waPhone) {
    return $waPhone ? '+' . substr($waPhone, 0, strlen(WA_COUNTRY_CODE)) . 'XXXXXX' . substr($waPhone, -4) : '';
}

/** Template variables: no newlines, no long spaces, never empty */
function wa_clean_param($v, $fallback = '-') {
    $v = clean_text($v);
    $collapsed = preg_replace('/\s+/u', ' ', $v);
    if ($collapsed !== null) $v = $collapsed;
    $v = trim($v);
    if (function_exists('mb_substr') && mb_strlen($v, 'UTF-8') > 200) {
        $v = mb_substr($v, 0, 197, 'UTF-8') . '...';
    }
    return $v === '' ? $fallback : $v;
}

/** Sends the approved template with the PDF as the document header */
function wa_api_send_template(array $p, $pdfUrl, $pdfFilename) {
    $date = wa_clean_param($p['date'], 'TBD');
    $ts = strtotime($date);
    if ($ts) $date = date('d M Y', $ts);

    $vals = [
        'name'    => wa_clean_param($p['name']),
        'event'   => wa_clean_param($p['event']),
        'pass_id' => wa_clean_param($p['pass_id']),
        'date'    => $date,
        'venue'   => wa_clean_param($p['venue'], 'TBD'),
    ];
    $bodyParams = [];
    foreach (WA_BODY_VARS as $key) {
        if (isset($vals[$key])) $bodyParams[] = ['type' => 'text', 'text' => $vals[$key]];
    }

    $components = [];
    if (WA_USE_PDF_HEADER) {
        $components[] = [
            'type'       => 'header',
            'parameters' => [[
                'type'     => 'document',
                'document' => ['link' => $pdfUrl, 'filename' => $pdfFilename],
            ]],
        ];
    }
    if ($bodyParams) $components[] = ['type' => 'body', 'parameters' => $bodyParams];

    $payload = [
        'token'             => WA_API_TOKEN,
        'phone'             => $p['phone'],
        'template_name'     => WA_TEMPLATE_NAME,
        'template_language' => WA_TEMPLATE_LANG,
        'components'        => $components,
    ];

    $ch = curl_init(rtrim(WA_API_BASE, '/') . '/api/wpbox/sendtemplatemessage');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . WA_API_TOKEN,
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $resp = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    $json = is_string($resp) ? json_decode($resp, true) : null;
    $apiSaysError = false;
    if (is_array($json)) {
        if (isset($json['status']) && ($json['status'] === false ||
            in_array(strtolower((string)$json['status']), ['error', 'failed', 'fail'], true))) {
            $apiSaysError = true;
        }
        if (isset($json['error'])) $apiSaysError = true;
    }
    $ok = ($resp !== false) && $http >= 200 && $http < 300 && !$apiSaysError;

    error_log('[WA-ADMIT] ' . $p['pass_id'] . ' phone=***' . substr($p['phone'], -4)
        . ' http=' . $http . ' ok=' . ($ok ? '1' : '0')
        . ($err ? ' curl_err=' . $err : '')
        . ' resp=' . substr((string)$resp, 0, 500));

    return ['ok' => $ok, 'http' => $http, 'err' => $err, 'body' => substr((string)$resp, 0, 300)];
}

/**
 * Validates and stores the PDF uploaded by the browser, then sends it on WhatsApp.
 * $wa = [phone, pass_id, name, event, date, venue, reg_id]
 * Return: ['ok'=>bool, 'code'=>int, 'message'=>string]
 */
function wa_store_pdf_and_send(PDO $pdo, array $wa, $file) {
    if (!function_exists('curl_init')) return ['ok' => false, 'code' => 500, 'message' => 'PHP cURL extension is not enabled on this server.'];
    if (empty($wa['phone']))           return ['ok' => false, 'code' => 422, 'message' => 'WhatsApp number is not valid.'];

    if (!$file || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'code' => 400, 'message' => 'PDF upload failed.'];
    }
    $size = (int)$file['size'];
    if ($size <= 0 || $size > WA_PDF_MAX_BYTES) return ['ok' => false, 'code' => 413, 'message' => 'PDF size is not allowed.'];

    $fh = fopen($file['tmp_name'], 'rb');
    $head = $fh ? fread($fh, 5) : '';
    if ($fh) fclose($fh);
    if ($head !== '%PDF-') return ['ok' => false, 'code' => 415, 'message' => 'Uploaded file is not a valid PDF.'];

    $dir = dirname(__DIR__) . WA_PDF_SUBDIR;
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) return ['ok' => false, 'code' => 500, 'message' => 'Server storage is not writable.'];
    if (!file_exists($dir . '/index.html')) @file_put_contents($dir . '/index.html', '');

    // Remove old PDFs (privacy)
    foreach ((array)glob($dir . '/AdmitCard_*.pdf') as $old) {
        if (@filemtime($old) < time() - WA_PDF_KEEP_SECS) @unlink($old);
    }

    $safeId   = preg_replace('/[^A-Za-z0-9_-]/', '', $wa['pass_id']);
    $fileName = 'AdmitCard_' . $safeId . '_' . bin2hex(random_bytes(8)) . '.pdf';
    $target   = $dir . '/' . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $target)) return ['ok' => false, 'code' => 500, 'message' => 'Could not store PDF.'];
    @chmod($target, 0644);

    $pdfUrl = site_base_url() . WA_PDF_SUBDIR . '/' . $fileName;
    $result = wa_api_send_template($wa, $pdfUrl, 'AdmitCard_' . $safeId . '.pdf');

    // Save the delivery status
    if (!empty($wa['reg_id'])) {
        try {
            $pdo->prepare("UPDATE student_registrations SET wa_status = :s, wa_sent_at = " . ($result['ok'] ? 'NOW()' : 'wa_sent_at') . " WHERE id = :id")
                ->execute([':s' => $result['ok'] ? 'Sent' : 'Failed', ':id' => (int)$wa['reg_id']]);
        } catch (PDOException $e) { error_log('[WA-ADMIT] status update failed: ' . $e->getMessage()); }
    }

    if ($result['ok']) return ['ok' => true, 'code' => 200, 'message' => 'Sent to WhatsApp.'];

    $detail = 'HTTP ' . $result['http'];
    if (WA_DEBUG) {
        $detail .= ' | ' . ($result['err'] !== '' ? $result['err'] : $result['body']) . ' | PDF: ' . $pdfUrl;
    } else {
        @unlink($target);
    }
    return ['ok' => false, 'code' => 502, 'message' => 'WhatsApp API error: ' . $detail];
}

// ---------------------------------------------------------------------
//  IMAGE UPLOAD (event photo, logo, gallery)
//  - Accepts JPG / PNG / WEBP up to IMAGE_UPLOAD_MAX_BYTES
//  - Large photos are resized (max IMAGE_MAX_DIMENSION px) and compressed automatically
//  - File extension is decided from the real file content, never from the file name
// ---------------------------------------------------------------------
if (!defined('IMAGE_UPLOAD_MAX_BYTES')) define('IMAGE_UPLOAD_MAX_BYTES', 20 * 1024 * 1024);
if (!defined('IMAGE_MAX_DIMENSION'))    define('IMAGE_MAX_DIMENSION', 1920);

/** Human readable message for PHP upload error codes */
function upload_error_message($code) {
    switch ((int)$code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'The image is larger than the server upload limit (' . ini_get('upload_max_filesize') . '). Increase "upload_max_filesize" in your hosting PHP settings or upload a smaller image.';
        case UPLOAD_ERR_PARTIAL:    return 'The image was only partially uploaded. Please try again.';
        case UPLOAD_ERR_NO_TMP_DIR: return 'Server error: temporary upload folder is missing.';
        case UPLOAD_ERR_CANT_WRITE: return 'Server error: could not write the uploaded file to disk.';
        case UPLOAD_ERR_EXTENSION:  return 'Server error: a PHP extension blocked the upload.';
        default:                    return 'Image upload failed (error code ' . (int)$code . ').';
    }
}

/**
 * Resize/compress an image in place when it is larger than IMAGE_MAX_DIMENSION.
 * Silently does nothing if the GD extension is not available.
 */
function optimize_image($path, $mime) {
    if (!extension_loaded('gd')) return;
    $info = @getimagesize($path);
    if (!$info) return;
    [$w, $h] = $info;
    $max = IMAGE_MAX_DIMENSION;
    $needsResize = $w > $max || $h > $max;
    $isBigFile = filesize($path) > 1024 * 1024;
    if (!$needsResize && !$isBigFile) return;

    @ini_set('memory_limit', '512M');
    switch ($mime) {
        case 'image/jpeg': $src = @imagecreatefromjpeg($path); break;
        case 'image/png':  $src = @imagecreatefrompng($path); break;
        case 'image/webp': $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false; break;
        default: $src = false;
    }
    if (!$src) return;

    // Respect phone camera rotation (EXIF orientation)
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($path);
        $o = (int)($exif['Orientation'] ?? 1);
        if ($o === 3) $src = imagerotate($src, 180, 0);
        elseif ($o === 6) $src = imagerotate($src, -90, 0);
        elseif ($o === 8) $src = imagerotate($src, 90, 0);
        $w = imagesx($src); $h = imagesy($src);
        $needsResize = $w > $max || $h > $max;
    }

    $scale = $needsResize ? min($max / $w, $max / $h) : 1;
    $nw = max(1, (int)round($w * $scale));
    $nh = max(1, (int)round($h * $scale));
    $dst = imagecreatetruecolor($nw, $nh);
    if ($mime !== 'image/jpeg') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

    $tmp = $path . '.tmp';
    $saved = false;
    if ($mime === 'image/jpeg') $saved = imagejpeg($dst, $tmp, 82);
    elseif ($mime === 'image/png') $saved = imagepng($dst, $tmp, 8);
    elseif ($mime === 'image/webp' && function_exists('imagewebp')) $saved = imagewebp($dst, $tmp, 82);
    imagedestroy($src);
    imagedestroy($dst);

    // Keep the optimized file only if it is actually smaller or it was resized
    if ($saved && is_file($tmp) && ($needsResize || filesize($tmp) < filesize($path))) {
        @rename($tmp, $path);
    } else {
        @unlink($tmp);
    }
}

/**
 * Save an uploaded image.
 * @param array  $file    One entry of $_FILES
 * @param string $subdir  e.g. '/storage/events'
 * @return string|null    Public path of the saved image, or null when no file was chosen
 * @throws Exception
 */
function save_uploaded_image($file, $subdir, $maxBytes = IMAGE_UPLOAD_MAX_BYTES) {
    if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception(upload_error_message($file['error']));
    if ($file['size'] > $maxBytes) throw new Exception('The image is larger than ' . round($maxBytes / 1048576) . 'MB. Please choose a smaller image.');

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $ext   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext || @getimagesize($file['tmp_name']) === false) throw new Exception('Only JPG, PNG and WEBP images are allowed.');

    $dir = dirname(__DIR__) . $subdir;
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) throw new Exception('Upload folder is not writable: ' . $subdir);

    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) throw new Exception('Could not save the image. Please check folder permissions.');
    @chmod($dir . '/' . $name, 0644);
    optimize_image($dir . '/' . $name, $mime);
    return $subdir . '/' . $name;
}

/** Delete a file, but only inside our /storage folder */
function delete_public_file($publicPath) {
    if (!$publicPath || strpos($publicPath, '/storage/') !== 0 || strpos($publicPath, '..') !== false) return;
    $full = dirname(__DIR__) . $publicPath;
    if (is_file($full)) @unlink($full);
}

/** Split a multiple-file $_FILES entry into a list of single files */
function normalize_files_array($f) {
    if (!$f || !is_array($f['name'])) return $f ? [$f] : [];
    $out = [];
    foreach ($f['name'] as $i => $n) {
        $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $out;
}
