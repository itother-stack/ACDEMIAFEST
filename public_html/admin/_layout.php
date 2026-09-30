<?php
// =====================================================================
//  admin/_layout.php — Shared admin chrome, injected into every admin page
//  by auth.php: AcademiaFest top bar + mobile menu, fonts/colours, and
//  mobile-friendly tables (rows become cards on small screens).
// =====================================================================

function admin_nav_items() {
    return [
        ['admin_dashboard.php',   'Dashboard'],
        ['view_registrations.php', 'Registrations'],
        ['view_events.php',        'Events'],
        ['add_event.php',          'Create Event'],
        ['gallery.php',            'Gallery'],
        ['banners.php',            'Banners'],
        ['system_check.php',       'System Check'],
        ['change_password.php',    'Password'],
    ];
}

function admin_head_html() {
    return <<<'HTML'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;600;700;800&display=swap" rel="stylesheet">
<style id="af-admin-theme">
    body { font-family: 'Source Sans 3', system-ui, sans-serif !important; background: #f8f9fc !important; padding: 0 !important; margin: 0; }
    h1, h2, .af-display { font-family: 'Playfair Display', Georgia, serif; }
    .af-page { padding: 32px; }
    body.af-login { padding: 16px !important; background: linear-gradient(135deg, #0f1f3d 0%, #1a2a4a 45%, #1e3a6e 100%) !important; }
    .af-page > *, .af-page .grid > *, .af-page .lg\:grid-cols-2 > * { min-width: 0; }

    /* Top bar */
    .af-bar { background: linear-gradient(135deg, #0f1f3d 0%, #1a2a4a 60%, #1e3a6e 100%); color: #fff; position: sticky; top: 0; z-index: 90; box-shadow: 0 2px 14px rgba(15,31,61,.25); }
    .af-bar-inner { max-width: 1400px; margin: 0 auto; padding: 0 24px; height: 62px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .af-bar-brand { display: flex; align-items: center; gap: 10px; color: #fff; text-decoration: none; }
    .af-bar-logo { width: 38px; height: 38px; background: #fff; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex: 0 0 38px; }
    .af-bar-name { font-family: 'Playfair Display', Georgia, serif; font-size: 19px; font-weight: 700; line-height: 1; }
    .af-bar-name span { color: #e6bf46; }
    .af-bar-links { display: flex; align-items: center; gap: 4px; }
    .af-bar-links a { color: #cbd5e6; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 10px; border-radius: 7px; white-space: nowrap; }
    .af-bar-links a:hover, .af-bar-links a.active { color: #fff; background: rgba(255,255,255,.10); }
    .af-bar-links a.active { box-shadow: inset 0 -2px 0 #c9a227; }
    .af-bar-actions { display: flex; align-items: center; gap: 8px; }
    .af-btn-site { color: #cbd5e6; font-size: 13px; font-weight: 600; text-decoration: none; padding: 7px 10px; }
    .af-btn-logout { background: linear-gradient(135deg, #c9a227, #e6bf46); color: #1a2a4a !important; font-weight: 800; font-size: 12px; letter-spacing: .5px; text-transform: uppercase; padding: 8px 14px; border-radius: 7px; text-decoration: none; white-space: nowrap; }
    .af-burger { display: none; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; width: 42px; height: 38px; border-radius: 8px; font-size: 20px; cursor: pointer; }
    .af-drawer { display: none; background: #0f1f3d; border-top: 1px solid rgba(255,255,255,.08); padding: 8px 12px 14px; }
    .af-drawer.open { display: block; }
    .af-drawer a { display: block; color: #cbd5e6; text-decoration: none; font-weight: 600; font-size: 15px; padding: 11px 12px; border-radius: 8px; }
    .af-drawer a.active { background: rgba(201,162,39,.15); color: #e6bf46; }
    .af-drawer .af-drawer-row { display: flex; gap: 8px; margin-top: 8px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,.08); }
    .af-drawer .af-drawer-row a { flex: 1; text-align: center; background: rgba(255,255,255,.06); }
    .af-drawer .af-drawer-row a.af-btn-logout { background: linear-gradient(135deg, #c9a227, #e6bf46); }

    @media (max-width: 1180px) {
        .af-bar-links, .af-btn-site, .af-bar-actions .af-btn-logout { display: none; }
        .af-burger { display: block; }
    }

    /* ── Mobile ─────────────────────────────────────────────────────── */
    @media (max-width: 768px) {
        .af-page { padding: 14px 12px 28px; }
        .af-bar-inner { padding: 0 12px; height: 56px; }
        .af-bar-name { font-size: 17px; }
        .af-page h1, .af-page h2.text-3xl, .af-page h2.text-2xl, .af-page .text-3xl { font-size: 22px !important; line-height: 1.25 !important; }
        .af-page .text-2xl { font-size: 19px !important; }
        .af-page .p-8, .af-page .md\:p-8, .af-page .p-10 { padding: 16px !important; }
        .af-page .p-6 { padding: 14px !important; }
        .af-page .gap-8 { gap: 16px !important; }
        .af-page .mb-8, .af-page .mb-10 { margin-bottom: 18px !important; }
        .af-page input:not([type=checkbox]):not([type=radio]), .af-page select, .af-page textarea { font-size: 16px !important; min-height: 42px; } /* no iPhone zoom */
        .af-page [class*="min-w-["] { min-width: 0 !important; flex-basis: 100% !important; }
        .af-page .ml-auto { margin-left: 0 !important; }

        /* Tables become cards */
        table.af-cards { min-width: 0 !important; white-space: normal !important; border-collapse: separate !important; }
        table.af-cards thead { display: none !important; }
        table.af-cards, table.af-cards tbody { display: block; width: 100%; }
        table.af-cards tr { display: flex; flex-direction: column; background: #fff; border: 1px solid #e8edf8; border-radius: 12px; padding: 10px 14px; margin: 0 0 12px; box-shadow: 0 2px 10px rgba(26,42,74,.05); }
        table.af-cards td { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 7px 0 !important; border: none !important; border-bottom: 1px dashed #eef1f8 !important; text-align: right !important; white-space: normal !important; word-break: break-word; font-size: 14px; }
        table.af-cards td:last-child { border-bottom: none !important; }
        table.af-cards td::before { content: attr(data-label); flex: 0 0 38%; text-align: left; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #8a99b8; }
        table.af-cards td[data-label=""]::before { display: none; }
        table.af-cards td.af-primary { order: -1; font-family: 'Playfair Display', Georgia, serif; font-size: 17px; font-weight: 700; color: #1a2a4a; text-align: left !important; justify-content: flex-start; border-bottom: 1px solid #eef1f8 !important; padding-bottom: 9px !important; }
        table.af-cards td.af-primary::before { display: none; }
        table.af-cards td > div, table.af-cards td > .flex { justify-content: flex-end; flex-wrap: wrap; }
        table.af-cards td.af-blank { display: none; }
        table.af-cards td.af-empty { display: block; text-align: center !important; }
        table.af-cards td.af-empty::before { display: none; }
        .overflow-x-auto:has(> table.af-cards), div:has(> table.af-cards) { overflow: visible !important; border: none !important; box-shadow: none !important; background: transparent !important; }
    }
</style>
HTML;
}

function admin_topbar_html() {
    $current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'admin_dashboard.php');
    if ($current === 'pass.php') $current = 'view_registrations.php';
    if ($current === 'edit_event.php') $current = 'view_events.php';
    $h = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
    $links = '';
    $drawer = '';
    foreach (admin_nav_items() as [$href, $label]) {
        $active = $href === $current ? ' class="active"' : '';
        $links  .= "<a href=\"$href\"$active>" . $h($label) . "</a>";
        $drawer .= "<a href=\"$href\"$active>" . $h($label) . "</a>";
    }
    $logo = brand_logo_svg(26);
    $name = $h(BRAND_NAME);
    return <<<HTML
<header class="af-bar">
    <div class="af-bar-inner">
        <a class="af-bar-brand" href="admin_dashboard.php">
            <span class="af-bar-logo">$logo</span>
            <span class="af-bar-name">$name <span>Admin</span></span>
        </a>
        <nav class="af-bar-links">$links</nav>
        <div class="af-bar-actions">
            <a class="af-btn-site" href="/" target="_blank">View Website ↗</a>
            <a class="af-btn-logout" href="logout.php">Logout</a>
            <button class="af-burger" type="button" aria-label="Menu" onclick="document.getElementById('afDrawer').classList.toggle('open')">☰</button>
        </div>
    </div>
    <div class="af-drawer" id="afDrawer">
        $drawer
        <div class="af-drawer-row">
            <a href="/" target="_blank">View Website ↗</a>
            <a class="af-btn-logout" href="logout.php">Logout</a>
        </div>
    </div>
</header>
<div class="af-page">
HTML;
}

function admin_foot_html() {
    return <<<'HTML'
</div>
<script>
/* Label every table cell with its column name so rows can be shown as cards on mobile */
(function () {
    var primaryCols = ['student name', 'event name', 'name'];
    document.querySelectorAll('.af-page table').forEach(function (table) {
        var heads = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) { return th.textContent.trim(); });
        if (!heads.length) return;
        table.classList.add('af-cards');
        var primaryIndex = -1;
        heads.forEach(function (t, i) { if (primaryIndex < 0 && primaryCols.indexOf(t.toLowerCase()) !== -1) primaryIndex = i; });
        table.querySelectorAll('tbody tr').forEach(function (tr) {
            var cells = tr.children;
            if (cells.length === 1) { cells[0].classList.add('af-empty'); return; }
            Array.prototype.forEach.call(cells, function (td, i) {
                td.setAttribute('data-label', heads[i] || '');
                if (i === primaryIndex) td.classList.add('af-primary');
                var txt = td.textContent.replace(/\s+/g, ' ').trim();
                if ((txt === '' || txt === '-' || txt === '—' || txt === 'N/A') && !td.querySelector('img,svg,a,button,input,select')) td.classList.add('af-blank');
            });
        });
    });
})();
</script>
HTML;
}
