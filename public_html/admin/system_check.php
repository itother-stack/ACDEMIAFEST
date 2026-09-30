<?php
// =====================================================================
//  admin/system_check.php — Database health + server settings
//  "Repair Database" safely creates missing tables/columns (no data is deleted)
// =====================================================================
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/system_checks.php';

$repairLog = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'repair') {
    $repairLog = run_migrations($pdo);
    if (!$repairLog) $repairLog = [['ok', 'Nothing to repair — the database already matches this version.']];
}

// Current database status
$dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
$tableStatus = [];
foreach (schema_tables() as $table => $def) {
    $st = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
    $st->execute([$dbName, $table]);
    $cols = array_map('strtolower', $st->fetchAll(PDO::FETCH_COLUMN));
    $missing = array_diff(array_keys($def['columns']), $cols);
    $tableStatus[] = [$table, !empty($cols), array_values($missing)];
}
$schemaVersion = 0;
try { $schemaVersion = (int)$pdo->query("SELECT meta_value FROM app_meta WHERE meta_key = 'schema_version'")->fetchColumn(); } catch (PDOException $e) {}
$env = environment_checks();
$icon = function ($t) { return $t === 'ok' ? '✅' : ($t === 'warn' ? '⚠️' : '❌'); };
$cls  = function ($t) { return $t === 'ok' ? 'bg-emerald-50 text-emerald-800' : ($t === 'warn' ? 'bg-amber-50 text-amber-800' : 'bg-red-50 text-red-700'); };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Check - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap'); body{font-family:'Inter',sans-serif;background:#f8f9fa}
    .premium-card{background:#fff;border-radius:16px;box-shadow:0 4px 20px -4px rgba(26,42,74,0.05);border:1px solid rgba(26,42,74,0.04)}</style>
</head>
<body class="p-4 md:p-8">
<div class="max-w-5xl mx-auto">
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <div>
            <h2 class="text-3xl font-extrabold text-[#1a2a4a]">System Check</h2>
            <p class="text-sm text-gray-500">Database structure and server settings.</p>
        </div>
        <a href="admin_dashboard.php" class="text-sm font-bold text-[#1a2a4a] bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 uppercase tracking-wide">Dashboard</a>
    </div>

    <?php if ($repairLog !== null): ?>
        <div class="premium-card p-6 mb-6">
            <h3 class="font-extrabold text-[#1a2a4a] mb-3">Repair Result</h3>
            <ul class="space-y-1.5 text-sm">
                <?php foreach ($repairLog as [$t, $m]): ?>
                    <li class="px-3 py-2 rounded <?= $cls($t) ?>"><?= $icon($t) ?> <?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="premium-card p-6 mb-6">
        <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
            <h3 class="font-extrabold text-[#1a2a4a]">Database <span class="text-xs font-semibold text-gray-400">(schema version <?= $schemaVersion ?> / required <?= SCHEMA_VERSION ?>)</span></h3>
            <form method="POST"><input type="hidden" name="action" value="repair">
                <button class="bg-[#1a2a4a] text-white font-bold px-5 py-2 rounded-lg text-sm hover:bg-[#0f1f3d]">Repair Database</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <?php foreach ($tableStatus as [$table, $exists, $missing]): ?>
                <tr class="border-b border-gray-100">
                    <td class="py-2 font-mono font-bold text-[#1a2a4a]"><?= $table ?></td>
                    <td class="py-2">
                        <?php if (!$exists): ?><span class="text-red-600 font-bold">❌ Table missing</span>
                        <?php elseif ($missing): ?><span class="text-red-600 font-bold">❌ Missing columns: <?= htmlspecialchars(implode(', ', $missing)) ?></span>
                        <?php else: ?><span class="text-emerald-700 font-bold">✅ OK</span><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p class="text-xs text-gray-500 mt-3">"Repair Database" only adds missing tables/columns and fixes incompatible column types. Existing data is never deleted.</p>
    </div>

    <div class="premium-card p-6">
        <h3 class="font-extrabold text-[#1a2a4a] mb-3">Server</h3>
        <ul class="space-y-1.5 text-sm">
            <?php foreach ($env as [$t, $m]): ?>
                <li class="px-3 py-2 rounded <?= $cls($t) ?>"><?= $icon($t) ?> <?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
</body>
</html>
