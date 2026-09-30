<?php
require_once __DIR__ . '/auth.php';
$msg = ''; $ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
    $st->execute([$_SESSION['admin_id']]);
    $hash = $st->fetchColumn();
    $new = (string)($_POST['new_password'] ?? '');
    if (!$hash || !password_verify((string)($_POST['current_password'] ?? ''), $hash)) {
        $msg = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $msg = 'New password must be at least 8 characters.';
    } elseif ($new !== ($_POST['confirm_password'] ?? '')) {
        $msg = 'New passwords do not match.';
    } else {
        $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
        $msg = 'Password changed successfully.'; $ok = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title><script src="https://cdn.tailwindcss.com"></script>
<style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap'); body{font-family:'Inter',sans-serif;background:#f8f9fa}</style></head>
<body class="p-4 md:p-8">
<div class="max-w-md mx-auto">
    <div class="mb-6"><a href="admin_dashboard.php" class="text-sm font-bold text-[#2d4a8a] hover:underline">&larr; Back to Dashboard</a></div>
    <div class="bg-white rounded-2xl shadow border border-gray-100 p-8">
        <h2 class="text-xl font-extrabold text-[#1a2a4a] mb-5">Change Password</h2>
        <?php if ($msg): ?><div class="p-3 rounded-lg mb-4 text-sm font-bold <?= $ok ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' ?>"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="password" name="current_password" placeholder="Current password" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5">
            <input type="password" name="new_password" placeholder="New password (min 8)" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5">
            <input type="password" name="confirm_password" placeholder="Confirm new password" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5">
            <button class="w-full bg-[#1a2a4a] text-white font-bold py-3 rounded-xl">Update Password</button>
        </form>
    </div>
</div>
</body></html>
