<?php
// =====================================================================
//  admin/banners.php — Home page hero banner (the sliding photos behind
//  "India's Trusted Education Event Platform"). Upload (many at once),
//  reorder, show/hide, delete. When none are active, stock photos are shown.
// =====================================================================
require_once __DIR__ . '/auth.php';

$msg = ''; $msgType = 'success';
$c = function ($k) { return htmlspecialchars(trim((string)($_POST[$k] ?? '')), ENT_QUOTES, 'UTF-8'); };

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'upload') {
            $files = normalize_files_array($_FILES['banners'] ?? null);
            $files = array_values(array_filter($files, function ($f) { return $f['error'] !== UPLOAD_ERR_NO_FILE; }));
            if (!$files) throw new Exception('Please choose at least one photo.');

            $maxSort = (int)$pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM hero_banners")->fetchColumn();
            $ins = $pdo->prepare("INSERT INTO hero_banners (image_path, sort_order) VALUES (?, ?)");
            $ok = 0; $errors = [];
            foreach ($files as $f) {
                try {
                    $path = save_uploaded_image($f, '/storage/main');
                    $ins->execute([$path, ++$maxSort]);
                    $ok++;
                } catch (Exception $e) {
                    $errors[] = htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') . ': ' . $e->getMessage();
                }
            }
            $msg = "$ok banner(s) uploaded." . ($errors ? ' Skipped — ' . implode(' | ', $errors) : '');
            $msgType = $errors && !$ok ? 'error' : 'success';
        }

        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $pdo->prepare("UPDATE hero_banners SET sort_order = ?, is_active = ? WHERE id = ?")
                ->execute([(int)($_POST['sort_order'] ?? 0), isset($_POST['is_active']) ? 1 : 0, $id]);
            $msg = 'Banner updated.';
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $st = $pdo->prepare("SELECT image_path FROM hero_banners WHERE id = ?");
            $st->execute([$id]);
            if ($p = $st->fetchColumn()) delete_public_file($p);
            $pdo->prepare("DELETE FROM hero_banners WHERE id = ?")->execute([$id]);
            $msg = 'Banner deleted.';
        }
    } catch (Exception $e) {
        $msg = $e->getMessage(); $msgType = 'error';
    }
}

$banners = [];
try {
    $banners = $pdo->query("SELECT * FROM hero_banners ORDER BY sort_order ASC, id DESC")->fetchAll();
} catch (PDOException $e) {
    $msg = 'The banners table could not be read. Open <a href="system_check.php" class="underline">System Check</a> and click "Repair Database".'; $msgType = 'error';
}
$maxUpload = ini_get('upload_max_filesize');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage Banners - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8f9fa; }
        .premium-card { background:#fff; border-radius:16px; box-shadow:0 4px 20px -4px rgba(26,42,74,0.05); border:1px solid rgba(26,42,74,0.04); }
        .inp { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:7px 10px; font-size:13px; }
    </style>
</head>
<body class="p-4 md:p-8">
<div class="max-w-7xl mx-auto">
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <div>
            <h2 class="text-3xl font-extrabold text-[#1a2a4a]">Homepage Banners</h2>
            <p class="text-sm text-gray-500">The sliding photos behind the headline on the home page. Landscape photos work best (about 1920&times;1080).</p>
        </div>
        <a href="admin_dashboard.php" class="text-sm font-bold text-[#1a2a4a] bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 uppercase tracking-wide">Dashboard</a>
    </div>

    <?php if ($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold text-sm <?= $msgType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Upload -->
    <div class="premium-card p-6 mb-8">
        <h3 class="font-extrabold text-[#1a2a4a] text-lg mb-4">Upload Banner Photos</h3>
        <form method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-4 items-start md:items-end">
            <input type="hidden" name="action" value="upload">
            <div class="flex-1 w-full">
                <input type="file" name="banners[]" multiple required accept="image/png, image/jpeg, image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-[#2d4a8a] hover:file:bg-blue-100 cursor-pointer bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                <p class="text-xs text-gray-400 mt-1">You can select several photos at once. JPG, PNG or WEBP, up to 20MB each (server limit per file: <?= htmlspecialchars($maxUpload) ?>). Large photos are resized automatically.</p>
            </div>
            <button class="bg-[#1a2a4a] text-white font-bold px-6 py-2.5 rounded-xl hover:bg-[#0f1f3d] whitespace-nowrap">Upload</button>
        </form>
    </div>

    <!-- List -->
    <?php if (!$banners): ?>
        <div class="premium-card p-10 text-center text-gray-500 font-bold">No custom banners yet. Until you add some, the home page shows default stock photos.</div>
    <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            <?php foreach ($banners as $p): ?>
                <div class="premium-card overflow-hidden <?= $p['is_active'] ? '' : 'opacity-60' ?>">
                    <a href="<?= htmlspecialchars($p['image_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                        <img src="<?= htmlspecialchars($p['image_path'], ENT_QUOTES, 'UTF-8') ?>" class="w-full h-32 object-cover bg-gray-100" loading="lazy" alt="">
                    </a>
                    <form method="POST" class="p-4 space-y-2">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-gray-600 whitespace-nowrap">Order</label>
                            <input name="sort_order" type="number" value="<?= (int)$p['sort_order'] ?>" title="Display order (lower number shows first)" class="inp" style="width:80px">
                        </div>
                        <label class="flex items-center gap-2 text-xs font-bold text-gray-600"><input type="checkbox" name="is_active" <?= $p['is_active'] ? 'checked' : '' ?>> Show on website</label>
                        <button class="w-full bg-blue-50 text-[#2d4a8a] border border-blue-200 font-bold py-2 rounded-lg text-xs hover:bg-blue-100">Save</button>
                    </form>
                    <form method="POST" class="px-4 pb-4" onsubmit="return confirm('Delete this banner?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button class="w-full bg-red-50 text-red-700 border border-red-200 font-bold py-2 rounded-lg text-xs hover:bg-red-100">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
