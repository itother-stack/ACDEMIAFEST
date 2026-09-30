<?php
// =====================================================================
//  admin/sheets_sync.php — Google Sheets LIVE SYNC (CSV)
//  URL:  https://your-site.com/admin/sheets_sync.php?token=TOKEN
//  One event only:  ...&event_id=5
//  In Google Sheets cell A1:  =IMPORTDATA("the URL above")
//  (No login needed — the token is the security, so never share this URL)
// =====================================================================
require_once __DIR__ . '/../includes/functions.php';

$provided_token = (string)($_GET['token'] ?? '');
if (!hash_equals(SHEETS_SYNC_TOKEN, $provided_token)) {
    http_response_code(403);
    die('403 Forbidden: Invalid Security Token.');
}

function sync_clean_value($s) {
    $s = (string)$s;
    for ($i = 0; $i < 3 && preg_match('/&[a-zA-Z0-9#]+;/', $s); $i++) $s = html_entity_decode($s, ENT_QUOTES, 'UTF-8');
    if ($s !== '' && in_array($s[0], ['=', '+', '-', '@'], true) && !preg_match('/^[+-]?\d/', $s)) $s = "'" . $s;
    return $s;
}

header("X-Content-Type-Options: nosniff");
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: inline; filename=live_sync.csv');
header('Cache-Control: no-store');

$sql = "SELECT sr.*, e.event_name FROM student_registrations sr LEFT JOIN events e ON sr.event_id = e.id";
$params = [];
if (!empty($_GET['event_id']) && ($eid = filter_var($_GET['event_id'], FILTER_VALIDATE_INT))) {
    $sql .= " WHERE sr.event_id = :eid";
    $params[':eid'] = $eid;
}
$sql .= " ORDER BY sr.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$out = fopen('php://output', 'w');
fputcsv($out, [
    'Registration ID', 'Pass ID', 'Event Name', 'Agent/Source', 'Status', 'Student Name', 'Gender', 'City',
    'Mobile Number', 'WhatsApp Number', 'Parent Mobile', 'Email ID', 'Ref 1 Name', 'Ref 1 Mobile',
    'Ref 2 Name', 'Ref 2 Mobile', 'Interested In', 'Courses', 'WhatsApp Status', 'Date Created', 'Last Updated'
]);
while ($r = $stmt->fetch()) {
    $passId = PASS_PREFIX . '-' . date('Y', strtotime($r['created_at'])) . '-' . str_pad((string)$r['id'], 5, '0', STR_PAD_LEFT);
    fputcsv($out, array_map('sync_clean_value', [
        $r['id'], $passId, $r['event_name'] ?: 'Unknown Event', $r['source'], $r['status'], $r['student_name'],
        $r['gender'], $r['city'], $r['mobile_number'], $r['whatsapp_number'], $r['parent_mobile'], $r['email_id'],
        $r['ref1_name'], $r['ref1_mobile'], $r['ref2_name'], $r['ref2_mobile'], $r['interested_in'], $r['courses'],
        $r['wa_status'] ?? '', $r['created_at'], $r['last_updated'],
    ]));
}
fclose($out);
