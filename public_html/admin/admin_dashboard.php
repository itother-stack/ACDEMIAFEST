<?php
require_once __DIR__ . '/auth.php';

$stats = ['total' => 0, 'today' => 0, 'events' => 0, 'active' => 0, 'wa_sent' => 0, 'wa_failed' => 0];
$perEvent = [];
$dbError = '';
try {
    $stats['total']     = (int)$pdo->query("SELECT COUNT(*) FROM student_registrations")->fetchColumn();
    $stats['today']     = (int)$pdo->query("SELECT COUNT(*) FROM student_registrations WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $stats['events']    = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $stats['active']    = (int)$pdo->query("SELECT COUNT(*) FROM events WHERE is_active = 1")->fetchColumn();
    $stats['wa_sent']   = (int)$pdo->query("SELECT COUNT(*) FROM student_registrations WHERE wa_status = 'Sent'")->fetchColumn();
    $stats['wa_failed'] = (int)$pdo->query("SELECT COUNT(*) FROM student_registrations WHERE wa_status IN ('Failed','Invalid')")->fetchColumn();
} catch (PDOException $e) {
    $dbError = 'Some database tables or columns are missing. Open <a href="system_check.php" class="underline">System Check</a> and click "Repair Database".' . (SHOW_ERRORS ? ' (' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . ')' : '');
    error_log('[DASHBOARD] stats: ' . $e->getMessage());
}

// Registrations per event (subquery works in MySQL strict / ONLY_FULL_GROUP_BY mode)
try {
    $perEvent = $pdo->query("SELECT e.id, e.event_name, e.is_active,
                                    (SELECT COUNT(*) FROM student_registrations sr WHERE sr.event_id = e.id) AS total
                             FROM events e
                             ORDER BY e.created_at DESC
                             LIMIT 10")->fetchAll();
} catch (PDOException $e) {
    error_log('[DASHBOARD] per-event: ' . $e->getMessage());
    if (!$dbError) $dbError = 'Could not load event summary.' . (SHOW_ERRORS ? ' ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') : '');
}

$syncUrl = site_base_url() . '/admin/sheets_sync.php?token=' . urlencode(SHEETS_SYNC_TOKEN);
$formUrl = site_base_url() . '/register/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');
        body { font-family: 'Inter', system-ui, sans-serif; background-color: #f4f7f6; }
        .premium-card { background:#fff; border-radius:16px; box-shadow:0 4px 20px -4px rgba(26,42,74,0.05); border:1px solid rgba(26,42,74,0.06); transition:all .3s cubic-bezier(.4,0,.2,1); }
        a.premium-card:hover { box-shadow:0 12px 28px -6px rgba(26,42,74,0.12); transform:translateY(-4px); }
        @keyframes fadeSlideUp { from { opacity:0; transform:translateY(20px);} to { opacity:1; transform:translateY(0);} }
        .animate-up { animation: fadeSlideUp .6s cubic-bezier(.16,1,.3,1) forwards; opacity:0; }
        .delay-100 { animation-delay:100ms; } .delay-200 { animation-delay:200ms; }
    </style>
</head>
<body class="text-gray-800 antialiased">


    <main class="max-w-[85rem] mx-auto">
        <div class="mb-8 animate-up">
            <h1 class="text-3xl font-extrabold text-[#1a2a4a] tracking-tight mb-1">Dashboard Overview</h1>
            <p class="text-sm text-gray-500 mb-3">Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>.</p>
            <div class="w-16 h-1.5 bg-[#c9a227] rounded-full"></div>
        </div>

        <?php if (!empty($dbError)): ?>
            <div class="bg-red-50 text-red-700 border border-red-200 p-4 rounded-xl font-bold mb-8"><?= $dbError ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-10 animate-up delay-100">
            <?php foreach ([
                ['Total Registrations', $stats['total'], 'border-[#1a2a4a] text-[#1a2a4a]'],
                ['Today', $stats['today'], 'border-blue-500 text-blue-600'],
                ['Active Events', $stats['active'], 'border-emerald-500 text-emerald-600'],
                ['All Events', $stats['events'], 'border-gray-400 text-gray-700'],
                ['WhatsApp Sent', $stats['wa_sent'], 'border-green-500 text-green-600'],
                ['WhatsApp Failed', $stats['wa_failed'], 'border-red-500 text-red-600'],
            ] as [$label, $num, $cls]): ?>
                <div class="premium-card p-5 border-l-4 <?= explode(' ', $cls)[0] ?>">
                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1"><?= $label ?></div>
                    <div class="text-3xl font-extrabold <?= explode(' ', $cls)[1] ?>"><?= number_format($num) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Main links -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 animate-up delay-100 mb-10">
            <?php foreach ([
                ['view_registrations.php', 'Event Registrations', 'View, filter & export students', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['view_events.php', 'Manage Events', 'Edit / delete / activate', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['add_event.php', 'Create Event', 'New event + photo + agents', 'M12 4v16m8-8H4'],
                ['gallery.php', 'Event Gallery', 'Home page photos', 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['banners.php', 'Homepage Banners', 'Sliding hero photos', 'M3 7l9-4 9 4v10l-9 4-9-4V7zm9-4v18M3 7l9 4 9-4'],
                ['view_registrations.php?export=excel', 'Export CSV', 'Download all registrations', 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'],
            ] as [$href, $title, $sub, $icon]): ?>
                <a href="<?= $href ?>" class="premium-card p-6 flex items-center gap-5 group">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-blue-50 text-blue-600 group-hover:bg-[#1a2a4a] group-hover:text-white transition-all shadow-inner flex-shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= $icon ?>"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-lg text-[#1a2a4a] group-hover:text-[#c9a227] transition-colors"><?= $title ?></h4>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mt-1"><?= $sub ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="grid lg:grid-cols-2 gap-6 animate-up delay-200">
            <!-- Per event -->
            <div class="premium-card p-6">
                <h3 class="font-extrabold text-[#1a2a4a] text-lg mb-4 pb-3 border-b border-gray-100">Registrations by Event</h3>
                <?php if (empty($perEvent)): ?>
                    <p class="text-sm text-gray-500">No events yet. <a href="add_event.php" class="text-[#2d4a8a] font-bold">Create your first event →</a></p>
                <?php else: ?>
                    <table class="w-full text-sm">
                        <?php foreach ($perEvent as $pe): ?>
                            <tr class="border-b border-gray-50">
                                <td class="py-2.5 font-bold text-[#1a2a4a]">
                                    <?= htmlspecialchars(clean_text($pe['event_name']), ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!$pe['is_active']): ?><span class="ml-1 text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded uppercase">Inactive</span><?php endif; ?>
                                </td>
                                <td class="py-2.5 text-right">
                                    <a href="view_registrations.php?event_id=<?= (int)$pe['id'] ?>" class="font-extrabold text-[#c9a227] hover:underline"><?= number_format($pe['total']) ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Links -->
            <div class="premium-card p-6">
                <h3 class="font-extrabold text-[#1a2a4a] text-lg mb-4 pb-3 border-b border-gray-100">Share & Sync Links</h3>

                <label class="block text-[11px] font-bold text-[#1a2a4a] uppercase tracking-wider mb-1.5">Registration Form (for students) — for one specific event use: <span class="normal-case">/register/?event=ID</span></label>
                <div class="flex gap-2 mb-5">
                    <input readonly value="<?= htmlspecialchars($formUrl, ENT_QUOTES, 'UTF-8') ?>" class="flex-1 border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-xs font-mono">
                    <button type="button" onclick="copyText(this)" class="bg-[#1a2a4a] text-white text-xs font-bold px-3 rounded-lg">Copy</button>
                </div>

                <label class="block text-[11px] font-bold text-[#1a2a4a] uppercase tracking-wider mb-1.5">Google Sheets Live Sync URL (private)</label>
                <div class="flex gap-2 mb-2">
                    <input readonly value="<?= htmlspecialchars($syncUrl, ENT_QUOTES, 'UTF-8') ?>" class="flex-1 border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-xs font-mono">
                    <button type="button" onclick="copyText(this)" class="bg-[#1a2a4a] text-white text-xs font-bold px-3 rounded-lg">Copy</button>
                </div>
                <p class="text-xs text-gray-500 leading-relaxed">
                    In Google Sheets, type in cell A1: <code class="bg-gray-100 px-1 rounded">=IMPORTDATA("this URL")</code> — the data keeps updating automatically.
                    For a single event, add <code class="bg-gray-100 px-1 rounded">&amp;event_id=ID</code> to the end of the URL. Do not share this URL with anyone.
                </p>
            </div>
        </div>
    </main>

<script>
function copyText(btn) {
    const input = btn.previousElementSibling;
    input.select();
    (navigator.clipboard ? navigator.clipboard.writeText(input.value) : Promise.reject()).catch(function () { document.execCommand('copy'); });
    btn.textContent = 'Copied!';
    setTimeout(function () { btn.textContent = 'Copy'; }, 1500);
}
</script>
</body>
</html>
