<?php
// ==========================================
// HARDENED SECURITY HEADERS (Core PHP Standard)
// ==========================================
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:; img-src 'self' https: data: blob:;");

require_once __DIR__ . '/auth.php';

// ==========================================
// SECURITY HELPERS
// ==========================================
function secure_clean_output($string) {
    if (empty($string)) return '';
    $decoded = $string;
    while (preg_match('/&[a-zA-Z0-9#]+;/', $decoded)) {
        $new_decoded = html_entity_decode($decoded, ENT_QUOTES, 'UTF-8');
        if ($new_decoded === $decoded) break; 
        $decoded = $new_decoded;
    }
    return htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
}

// Dynamic Query Builder for Filters
$whereClauses = ["1=1"];
$params = [];

$date_filter = $_GET['date_filter'] ?? '';
if ($date_filter === 'today') {
    $whereClauses[] = "DATE(sr.created_at) = CURDATE()";
} elseif ($date_filter === 'custom' && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $whereClauses[] = "DATE(sr.created_at) BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $_GET['start_date'];
    $params[':end_date'] = $_GET['end_date'];
}

if (!empty($_GET['gender'])) {
    $whereClauses[] = "sr.gender = :gender";
    $params[':gender'] = $_GET['gender'];
}
if (!empty($_GET['status'])) {
    $whereClauses[] = "sr.status = :status";
    $params[':status'] = $_GET['status'];
}
if (!empty($_GET['event_id'])) {
    $whereClauses[] = "sr.event_id = :event_id";
    $params[':event_id'] = $_GET['event_id'];
}
if (!empty($_GET['source'])) {
    $whereClauses[] = "sr.source = :source";
    $params[':source'] = $_GET['source'];
}

$whereSql = implode(" AND ", $whereClauses);

$sql = "
    SELECT sr.*, e.event_name 
    FROM student_registrations sr
    LEFT JOIN events e ON sr.event_id = e.id
    WHERE $whereSql 
    ORDER BY sr.last_updated DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$eventsList = $pdo->query("SELECT id, event_name FROM events ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$sourcesList = $pdo->query("SELECT DISTINCT source FROM student_registrations WHERE source IS NOT NULL AND source != '' ORDER BY source ASC")->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// EXPORT TO EXCEL (CSV) LOGIC
// ==========================================
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . preg_replace('/[^A-Za-z0-9]/', '', BRAND_NAME) . '_Registrations_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, [
        'Registration ID', 'Event Name', 'Agent/Source', 'Status', 'Student Name', 'Gender', 'City', 'Mobile Number', 
        'WhatsApp Number', 'Parent Mobile', 'Email ID', 'Ref 1 Name', 'Ref 1 Mobile', 'Ref 2 Name', 'Ref 2 Mobile', 
        'Interested In', 'Courses', 'WhatsApp Status', 'Date Created', 'Last Updated'
    ]);
    
    foreach ($registrations as $row) {
        $line = [];
        foreach (['id','event_name','source','status','student_name','gender','city','mobile_number','whatsapp_number','parent_mobile','email_id','ref1_name','ref1_mobile','ref2_name','ref2_mobile','interested_in','courses','wa_status','created_at','last_updated'] as $k) {
            $v = clean_text($row[$k] ?? '');
            if ($k === 'event_name' && $v === '') $v = 'Unknown Event';
            // Protect against Excel formula injection
            if ($v !== '' && in_array($v[0], ['=', '+', '-', '@'], true) && !preg_match('/^[+-]?\d/', $v)) $v = "'" . $v;
            $line[] = $v;
        }
        fputcsv($output, $line);
    }
    fclose($output);
    exit(); 
}

$total = count($registrations);
$males = 0; $females = 0; $completed = 0; $abandoned = 0;

foreach ($registrations as $row) {
    if ($row['gender'] === 'Male') $males++;
    if ($row['gender'] === 'Female') $females++;
    if ($row['status'] === 'Completed') $completed++;
    if ($row['status'] === 'Partial') $abandoned++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrations - <?= htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') ?> Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', system-ui, sans-serif; }
        .premium-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px -4px rgba(26, 42, 74, 0.05);
            border: 1px solid rgba(26, 42, 74, 0.04);
        }
        .dashboard-input {
            @apply border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c9a227]/50 focus:border-[#c9a227] transition-all;
        }
        
        /* Fixed Viewport Scrolling & Sticky Scrollbars */
        .custom-scroll {
            overflow-x: auto;
            overflow-y: auto; /* Enables vertical scrolling inside the box */
            max-height: 65vh; /* Prevents the table from stretching endlessly down the screen */
            padding-bottom: 8px; 
        }
        .custom-scroll::-webkit-scrollbar {
            height: 12px; 
            width: 12px; /* Allows for the vertical scrollbar width */
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #f1f5f9; 
            border-radius: 8px; 
            border: 1px solid #e2e8f0;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: #94a3b8; 
            border-radius: 8px; 
            border: 2px solid #f1f5f9; 
            cursor: pointer;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #64748b; 
        }
    </style>
</head>
<body class="p-4 md:p-8 relative min-h-screen">
    <div class="max-w-[100rem] mx-auto relative z-10">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-[#1a2a4a] tracking-tight mb-1">Registrations</h2>
                <p class="text-sm text-gray-500 font-medium">Manage and export student event registrations.</p>
            </div>
<div class="flex flex-wrap gap-2">
            <a href="admin_dashboard.php" class="inline-flex items-center gap-2 text-sm font-bold text-[#1a2a4a] hover:text-[#c9a227] bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 uppercase tracking-wide">
                Dashboard
            </a>
            <a href="view_events.php" class="inline-flex items-center gap-2 text-sm font-bold text-[#1a2a4a] hover:text-[#c9a227] bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 uppercase tracking-wide">
                Events
            </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="premium-card p-5 border-l-4 border-[#1a2a4a]">
                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Total Filtered</div>
                <div class="text-3xl font-extrabold text-[#1a2a4a]"><?= number_format($total) ?></div>
            </div>
            <div class="premium-card p-5 border-l-4 border-blue-500">
                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Male</div>
                <div class="text-3xl font-extrabold text-blue-600"><?= number_format($males) ?></div>
            </div>
            <div class="premium-card p-5 border-l-4 border-pink-500">
                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Female</div>
                <div class="text-3xl font-extrabold text-pink-600"><?= number_format($females) ?></div>
            </div>
            <div class="premium-card p-5 border-l-4 border-emerald-500">
                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Completed</div>
                <div class="text-3xl font-extrabold text-emerald-600"><?= number_format($completed) ?></div>
            </div>
            <div class="premium-card p-5 border-l-4 border-[#c9a227]">
                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Abandoned</div>
                <div class="text-3xl font-extrabold text-[#c9a227]"><?= number_format($abandoned) ?></div>
            </div>
        </div>

        <div class="premium-card p-6 mb-8">
            <h3 class="font-extrabold text-[#1a2a4a] text-lg mb-4 pb-3 border-b border-gray-100">Data Filters</h3>
            <form method="GET" class="flex flex-wrap gap-5 items-end">
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-[11px] font-bold text-[#1a2a4a] uppercase tracking-wider mb-1.5">Date Range</label>
                    <select name="date_filter" onchange="document.getElementById('custom_dates').style.display = this.value === 'custom' ? 'flex' : 'none'" class="dashboard-input w-full">
                        <option value="all" <?= ($date_filter === 'all') ? 'selected' : '' ?>>All Time</option>
                        <option value="today" <?= ($date_filter === 'today') ? 'selected' : '' ?>>Today</option>
                        <option value="custom" <?= ($date_filter === 'custom') ? 'selected' : '' ?>>Custom Range</option>
                    </select>
                </div>
                <div id="custom_dates" class="gap-3 flex-1 min-w-[250px]" style="display: <?= $date_filter === 'custom' ? 'flex' : 'none' ?>;">
                    <div class="flex-1">
                        <label class="block text-[11px] font-bold text-[#1a2a4a] uppercase tracking-wider mb-1.5">Start Date</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="dashboard-input w-full">
                    </div>
                    <div class="flex-1">
                        <label class="block text-[11px] font-bold text-[#1a2a4a] uppercase tracking-wider mb-1.5">End Date</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="dashboard-input w-full">
                    </div>
                </div>
                <div class="w-32">
                    <label class="block text-[11px] font-bold text-[#1a2a4a] uppercase tracking-wider mb-1.5">Gender</label>
                    <select name="gender" class="dashboard-input w-full">
                        <option value="">All</option>
                        <option value="Male" <?= (($_GET['gender']??'') === 'Male') ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= (($_GET['gender']??'') === 'Female') ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="w-36">
                    <label class="block text-[11px] font-bold text-[#1a2a4a] uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" class="dashboard-input w-full">
                        <option value="">All</option>
                        <option value="Completed" <?= (($_GET['status']??'') === 'Completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="Partial" <?= (($_GET['status']??'') === 'Partial') ? 'selected' : '' ?>>Partial</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[11px] font-bold text-[#1a2a4a] uppercase tracking-wider mb-1.5">Event</label>
                    <select name="event_id" class="dashboard-input w-full">
                        <option value="">All Events</option>
                        <?php foreach($eventsList as $evt): ?>
                            <option value="<?= $evt['id'] ?>" <?= (($_GET['event_id']??'') == $evt['id']) ? 'selected' : '' ?>><?= secure_clean_output($evt['event_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-[11px] font-bold text-[#1a2a4a] uppercase tracking-wider mb-1.5">Agent / Source</label>
                    <select name="source" class="dashboard-input w-full">
                        <option value="">All Agents</option>
                        <?php foreach($sourcesList as $src): ?>
                            <option value="<?= htmlspecialchars($src['source'], ENT_QUOTES, 'UTF-8') ?>" <?= (($_GET['source']??'') === $src['source']) ? 'selected' : '' ?>><?= htmlspecialchars($src['source'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto mt-2 md:mt-0">
                    <button type="submit" class="bg-[#1a2a4a] text-[#c9a227] font-extrabold px-6 py-2.5 rounded-lg text-sm hover:bg-[#152a5e] uppercase tracking-wide">Filter</button>
                    <a href="view_registrations.php" class="text-xs font-bold text-gray-500 hover:text-[#c9a227] uppercase tracking-wide px-2">Clear</a>
                    <button type="submit" name="export" value="excel" class="bg-emerald-600 text-white font-bold px-5 py-2.5 rounded-lg text-sm hover:bg-emerald-700 ml-auto">Export CSV</button>
                </div>
            </form>
        </div>

        <div class="premium-card overflow-hidden">
            <div class="custom-scroll relative">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="bg-slate-50 text-[#1a2a4a] uppercase text-[11px] font-extrabold tracking-wider sticky top-0 z-20 outline outline-1 outline-gray-200 shadow-sm">
                        <tr>
                            <th class="px-4 py-4">Status</th>
                            <th class="px-4 py-4 text-center">Admit Card</th>
                            <th class="px-4 py-4">Event</th>
                            <th class="px-4 py-4">Source</th>
                            <th class="px-4 py-4">Student Name</th>
                            <th class="px-4 py-4">Gender</th>
                            <th class="px-4 py-4">City</th>
                            <th class="px-4 py-4">Mobile Number</th>
                            <th class="px-4 py-4">WhatsApp Number</th>
                            <th class="px-4 py-4">Parent Mobile</th>
                            <th class="px-4 py-4">Email ID</th>
                            <th class="px-4 py-4">Ref 1 Name</th>
                            <th class="px-4 py-4">Ref 1 Mobile</th>
                            <th class="px-4 py-4">Ref 2 Name</th>
                            <th class="px-4 py-4">Ref 2 Mobile</th>
                            <th class="px-4 py-4">Interested In</th>
                            <th class="px-4 py-4">Courses</th>
                            <th class="px-4 py-4">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($registrations)): ?>
                            <tr>
                                <td colspan="18" class="px-6 py-16 text-center text-gray-500 font-bold">No Records Found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registrations as $row): ?>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    
                                    <td class="px-4 py-3">
                                        <?php if ($row['status'] === 'Completed'): ?>
                                            <span class="px-2 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">Completed</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 rounded text-[10px] font-bold bg-orange-100 text-orange-800 uppercase">Partial</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <?php if ($row['status'] === 'Completed'): ?>
                                            <?php
                                                $wa_st = $row['wa_status'] ?? 'Pending';
                                                $wa_cls = ['Sent' => 'bg-emerald-100 text-emerald-700', 'Failed' => 'bg-red-100 text-red-700', 'Invalid' => 'bg-gray-100 text-gray-500'][$wa_st] ?? 'bg-amber-100 text-amber-700';
                                            ?>
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="pass.php?id=<?= (int)$row['id'] ?>" title="View / Download Pass" class="p-1.5 bg-blue-50 text-[#1a2a4a] hover:bg-[#1a2a4a] hover:text-[#c9a227] rounded transition-colors border border-blue-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                </a>
                                                <a href="pass.php?id=<?= (int)$row['id'] ?>&amp;send=1" title="Send Admit Card on WhatsApp (API)" class="p-1.5 bg-green-50 text-green-600 hover:bg-green-600 hover:text-white rounded transition-colors border border-green-200">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                                                </a>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase <?= $wa_cls ?>" title="WhatsApp status">WA: <?= secure_clean_output($wa_st) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="px-4 py-3 font-medium text-[#1a2a4a]"><?= secure_clean_output($row['event_name'] ?? 'Unknown') ?></td>
                                    <td class="px-4 py-3 text-[#c9a227] font-bold"><?= secure_clean_output($row['source'] ?? 'Direct') ?></td>

                                    <td class="px-4 py-3 font-bold text-gray-900"><?= secure_clean_output($row['student_name'] ?? 'N/A') ?></td>
                                    <td class="px-4 py-3 text-gray-600"><?= secure_clean_output($row['gender'] ?? '-') ?></td>
                                    <td class="px-4 py-3 text-gray-600"><?= secure_clean_output($row['city'] ?? '-') ?></td>

                                    <td class="px-4 py-3 font-mono text-gray-800">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            <?= secure_clean_output($row['mobile_number'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-gray-800">
                                        <?php if(!empty($row['whatsapp_number'])): ?>
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                            <?= secure_clean_output($row['whatsapp_number']) ?>
                                        </div>
                                        <?php else: ?>
                                        -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-gray-800">
                                        <?php if(!empty($row['parent_mobile'])): ?>
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                            <?= secure_clean_output($row['parent_mobile']) ?>
                                        </div>
                                        <?php else: ?>
                                        -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            <?= secure_clean_output($row['email_id'] ?? '-') ?>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-gray-800"><?= secure_clean_output($row['ref1_name'] ?? '-') ?></td>
                                    <td class="px-4 py-3 font-mono text-gray-800"><?= secure_clean_output($row['ref1_mobile'] ?? '-') ?></td>
                                    <td class="px-4 py-3 text-gray-800"><?= secure_clean_output($row['ref2_name'] ?? '-') ?></td>
                                    <td class="px-4 py-3 font-mono text-gray-800"><?= secure_clean_output($row['ref2_mobile'] ?? '-') ?></td>
                                    
                                    <td class="px-4 py-3 text-gray-800"><?= secure_clean_output($row['interested_in'] ?? '-') ?></td>
                                    <td class="px-4 py-3 text-gray-600"><?= secure_clean_output($row['courses'] ?? '-') ?></td>
                                    
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        <?= date('Y-m-d H:i', strtotime($row['last_updated'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>