<?php
// db.php — Database connection (settings are in config.php)
require_once __DIR__ . '/config.php';

date_default_timezone_set(APP_TIMEZONE);
error_reporting(E_ALL);
ini_set('display_errors', SHOW_ERRORS ? '1' : '0');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    $pdo->exec("SET time_zone = '+05:30'");
} catch (PDOException $e) {
    http_response_code(500);
    die("<div style='padding:20px;background:#ffebee;color:#c62828;font-family:sans-serif;border:1px solid #c62828;'><strong>Database connection failed.</strong> Please check DB_HOST / DB_NAME / DB_USER / DB_PASS in config.php."
        . (SHOW_ERRORS ? '<br><small>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</small>' : '') . "</div>");
}
