<?php
// =====================================================================
//  admin/auth.php — Included at the top of every admin page
//  Login check + database + CSRF protection (added to all POST forms automatically)
// =====================================================================
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

if (!defined('ADMIN_PUBLIC_PAGE')) {
    if (empty($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
    // Log out after 2 hours of inactivity
    if (!empty($_SESSION['admin_last']) && time() - $_SESSION['admin_last'] > 7200) {
        session_unset(); session_destroy();
        header("Location: login.php?expired=1");
        exit();
    }
    $_SESSION['admin_last'] = time();
}

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

/**
 * A form bigger than post_max_size arrives completely empty ($_POST and $_FILES are blank).
 * Show a clear message instead of a confusing "session expired" error.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)
    && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    require_once __DIR__ . '/../includes/system_checks.php';
    $limit = ini_get('post_max_size');
    if ((int)$_SERVER['CONTENT_LENGTH'] > ini_size_to_bytes($limit)) {
        http_response_code(413);
        echo "<div style='padding:24px;font-family:sans-serif;max-width:640px;margin:40px auto;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#991b1b'>"
           . "<strong>Upload too large.</strong><br>The selected files total " . round($_SERVER['CONTENT_LENGTH'] / 1048576, 1) . "MB, but the server allows only {$limit} per form."
           . "<br>Please upload fewer or smaller images at a time, or increase <code>post_max_size</code> and <code>upload_max_filesize</code> in your hosting PHP settings."
           . "<br><br><a href='javascript:history.back()'>&larr; Go back</a></div>";
        exit();
    }
}

/** CSRF check for every POST request */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = (string)($_POST['_csrf'] ?? ($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')));
    if (!hash_equals($_SESSION['admin_csrf'], $sent)) {
        http_response_code(403);
        if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Session expired. Please refresh the page.']);
        } else {
            echo "<div style='padding:20px;font-family:sans-serif'>Your session has expired. Please <a href=''>refresh the page</a> and try again.</div>";
        }
        exit();
    }
}

require_once __DIR__ . '/_layout.php';

/**
 * Output filter for every admin page:
 *  - adds the CSRF hidden field to every POST <form>
 *  - adds the shared AcademiaFest admin layout (top bar, mobile menu, responsive tables)
 * Non-HTML output (CSV export, JSON) is left untouched.
 */
$__csrf_tok = $_SESSION['admin_csrf'];
$__with_chrome = !defined('ADMIN_PUBLIC_PAGE');
ob_start(function ($html) use ($__csrf_tok, $__with_chrome) {
    if (stripos($html, '</head>') === false) return $html;
    $field = '<input type="hidden" name="_csrf" value="' . $__csrf_tok . '">';
    $html = preg_replace('/(<form\b[^>]*method\s*=\s*["\']?post["\']?[^>]*>)/i', '$1' . $field, $html);
    $html = preg_replace('~</head>~i', admin_head_html() . '<link rel="icon" href="' . brand_favicon_uri() . '">' . '</head>', $html, 1);
    if ($__with_chrome) {
        $html = preg_replace('/(<body\b[^>]*>)/i', '$1' . admin_topbar_html(), $html, 1);
        $pos = strripos($html, '</body>');
        if ($pos !== false) $html = substr($html, 0, $pos) . admin_foot_html() . substr($html, $pos);
    }
    return $html;
});

function admin_csrf() { return $_SESSION['admin_csrf']; }
