<?php
// =====================================================================
//  INSTALLER — open once in the browser: https://your-site.com/install.php
//  1) Creates all tables  2) Upgrades old tables (missing columns / wrong types)
//  3) Creates the first admin account.
//  DELETE THIS FILE FROM THE SERVER AFTER SETUP.
// =====================================================================
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/schema.php';
require_once __DIR__ . '/includes/system_checks.php';
session_start();
if (empty($_SESSION['install_csrf'])) $_SESSION['install_csrf'] = bin2hex(random_bytes(16));

// ---------- 1. Database ----------
$log = run_migrations($pdo);
if (!array_filter($log, function ($l) { return $l[0] === 'err'; })) {
    array_unshift($log, ['ok', 'Database is up to date (schema version ' . SCHEMA_VERSION . ').']);
}
$ok = !array_filter($log, function ($l) { return $l[0] === 'err'; });

// ---------- 2. Server ----------
$log = array_merge($log, environment_checks());

// ---------- 3. First admin ----------
$adminCount = 0;
try { $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn(); } catch (PDOException $e) {}
$adminMsg = '';
if ($adminCount === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['install_csrf'], (string)($_POST['csrf'] ?? ''))) {
        $adminMsg = 'Your session has expired. Please refresh the page.';
    } else {
        $u = trim($_POST['username'] ?? '');
        $p = (string)($_POST['password'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_.@-]{3,100}$/', $u)) {
            $adminMsg = 'Username must be at least 3 characters (letters, numbers, _ . @ -).';
        } elseif (strlen($p) < 8) {
            $adminMsg = 'Password must be at least 8 characters.';
        } else {
            $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)")
                ->execute([$u, password_hash($p, PASSWORD_DEFAULT)]);
            $adminCount = 1;
            $log[] = ['ok', "Admin account '$u' created."];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Installer</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-50 p-6 font-sans">
<div class="max-w-3xl mx-auto bg-white rounded-2xl shadow p-8">
  <h1 class="text-2xl font-extrabold text-[#0a1c44] mb-6">Event Registration — Setup</h1>
  <ul class="space-y-2 mb-8 text-sm">
    <?php foreach ($log as [$t, $m]): ?>
      <li class="px-3 py-2 rounded <?= $t === 'ok' ? 'bg-emerald-50 text-emerald-800' : ($t === 'warn' ? 'bg-amber-50 text-amber-800' : 'bg-red-50 text-red-700') ?>">
        <?= $t === 'ok' ? '✅' : ($t === 'warn' ? '⚠️' : '❌') ?> <?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php if ($adminCount === 0): ?>
    <h2 class="font-extrabold text-[#0a1c44] mb-3">Create Admin Account</h2>
    <?php if ($adminMsg): ?><div class="bg-red-50 text-red-700 p-3 rounded mb-3 text-sm font-bold"><?= htmlspecialchars($adminMsg, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="POST" class="space-y-3">
      <input type="hidden" name="csrf" value="<?= $_SESSION['install_csrf'] ?>">
      <input name="username" placeholder="Username" required class="w-full border rounded-lg px-4 py-2.5">
      <input name="password" type="password" placeholder="Password (minimum 8 characters)" required class="w-full border rounded-lg px-4 py-2.5">
      <button class="w-full bg-[#0a1c44] text-white font-bold py-3 rounded-xl">Create Admin</button>
    </form>
  <?php else: ?>
    <div class="bg-[#0a1c44] text-white rounded-xl p-5">
      <p class="font-bold mb-2">Setup complete<?= $ok ? '' : ' — please fix the errors shown above' ?>.</p>
      <p class="text-sm opacity-90 mb-4">For security, please DELETE <b>install.php</b> from the server now.</p>
      <a href="admin/login.php" class="inline-block bg-[#f5a623] text-[#0a1c44] font-extrabold px-5 py-2 rounded-lg">Go to Admin Login →</a>
    </div>
  <?php endif; ?>
</div>
</body></html>
