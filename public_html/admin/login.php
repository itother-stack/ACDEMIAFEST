<?php
define('ADMIN_PUBLIC_PAGE', true);
require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['admin_id'])) { header("Location: admin_dashboard.php"); exit(); }

$error = '';
if (!empty($_GET['expired'])) $error = 'Session expired. Please login again.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['login_fails'] = $_SESSION['login_fails'] ?? 0;
    $_SESSION['login_lock']  = $_SESSION['login_lock'] ?? 0;
    if ($_SESSION['login_lock'] > time()) {
        $error = 'Too many attempts. Please wait ' . ceil(($_SESSION['login_lock'] - time()) / 60) . ' minute(s).';
    } else {
        $u = trim($_POST['username'] ?? '');
        $p = (string)($_POST['password'] ?? '');
        $admin = null;
        try {
            $st = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1");
            $st->execute([$u]);
            $admin = $st->fetch();
        } catch (PDOException $e) {
            $error = 'Admin table not found. Please run install.php first.';
        }
        if ($admin && password_verify($p, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = (int)$admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            $_SESSION['admin_last'] = time();
            $_SESSION['login_fails'] = 0;
            header("Location: admin_dashboard.php");
            exit();
        } elseif (!$error) {
            $_SESSION['login_fails']++;
            if ($_SESSION['login_fails'] >= 5) { $_SESSION['login_lock'] = time() + 600; $_SESSION['login_fails'] = 0; }
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap'); body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="af-login bg-[#f8f9fa] min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-[0_4px_20px_-4px_rgba(26,42,74,0.08)] border border-gray-100 p-8">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-3"><?= brand_logo_svg(56) ?></div>
            <div class="af-display text-2xl font-bold text-[#1a2a4a]"><?= htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') ?></div>
            <h1 class="text-sm font-bold uppercase tracking-widest text-[#8a99b8] mt-1">Admin Login</h1>
        </div>
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 border border-red-200 p-3 rounded-lg text-sm font-bold mb-4"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-[11px] font-extrabold uppercase tracking-wider text-[#1a2a4a] mb-1.5">Username</label>
                <input name="username" required autofocus class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-extrabold uppercase tracking-wider text-[#1a2a4a] mb-1.5">Password</label>
                <input name="password" type="password" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
            </div>
            <button class="w-full bg-[#1a2a4a] hover:bg-[#0f1f3d] text-white font-bold py-3 rounded-xl">Login</button>
        </form>
    </div>
</body>
</html>
