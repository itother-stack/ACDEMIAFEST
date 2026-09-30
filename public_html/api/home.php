<?php
// =====================================================================
//  api/home.php — Public JSON for the home page (React)
//  { events: [...active events...], gallery: [...photos...] }
//  Public event details only — no student data is ever returned here
// =====================================================================
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', '0');

$out = ['events' => [], 'gallery' => [], 'banners' => []];

try {
    $rows = $pdo->query("SELECT id, event_name, event_date, event_venue, event_time, city, event_type, description,
                                seat_limit, photo_path, logo_path, offer_zone
                         FROM events WHERE is_active = 1
                         ORDER BY sort_order ASC, created_at DESC")->fetchAll();
    foreach ($rows as $r) {
        $out['events'][] = [
            'id'         => (int)$r['id'],
            'name'       => clean_text($r['event_name']),
            'type'       => clean_text($r['event_type']) ?: 'General Education Fair',
            'date'       => clean_text($r['event_date']),
            'time'       => clean_text($r['event_time']),
            'venue'      => clean_text($r['event_venue']),
            'city'       => clean_text($r['city']),
            'desc'       => clean_text($r['description']),
            'seat_limit' => clean_text($r['seat_limit']),
            'offer'      => clean_text($r['offer_zone']),
            'photo'      => $r['photo_path'] ?: null,
            'logo'       => $r['logo_path'] ?: null,
            'status'     => 'active',
            'register_url' => '/register/?event=' . (int)$r['id'],
        ];
    }
} catch (PDOException $e) { error_log('[API] events: ' . $e->getMessage()); }

try {
    $rows = $pdo->query("SELECT id, image_path, title, caption, tag FROM gallery_photos
                         WHERE is_active = 1 ORDER BY sort_order ASC, id DESC LIMIT 40")->fetchAll();
    foreach ($rows as $r) {
        $out['gallery'][] = [
            'id'      => (int)$r['id'],
            'image'   => $r['image_path'],
            'label'   => clean_text($r['title']),
            'scene'   => clean_text($r['caption']),
            'tag'     => clean_text($r['tag']),
        ];
    }
} catch (PDOException $e) { error_log('[API] gallery: ' . $e->getMessage()); }

try {
    $rows = $pdo->query("SELECT image_path FROM hero_banners
                         WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
    foreach ($rows as $r) $out['banners'][] = $r['image_path'];
} catch (PDOException $e) { error_log('[API] banners: ' . $e->getMessage()); }

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
