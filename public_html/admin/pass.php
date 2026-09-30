<?php
// =====================================================================
//  admin/pass.php — One student's admit card: preview, download PDF,
//  (re)send on WhatsApp, edit details or delete the registration
// =====================================================================
require_once __DIR__ . '/auth.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) { header("Location: view_registrations.php"); exit(); }

$loadReg = function () use ($pdo, $id) {
    $st = $pdo->prepare("SELECT * FROM student_registrations WHERE id = ? LIMIT 1");
    $st->execute([$id]);
    return $st->fetch();
};
$reg = $loadReg();
if (!$reg) { header("Location: view_registrations.php"); exit(); }

$loadEvt = function ($eventId) use ($pdo) {
    if (!$eventId) return null;
    $st = $pdo->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
    $st->execute([$eventId]);
    return $st->fetch() ?: null;
};
$evt = $loadEvt($reg['event_id']);
$passId = make_pass_id($reg['id'], $reg['created_at']);

// ---------- AJAX: receive the PDF and send it on WhatsApp ----------
if (isset($_GET['send_wa'])) {
    ini_set('display_errors', '0');
    header('Content-Type: application/json; charset=utf-8');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok' => false, 'message' => 'Invalid request method.']); exit(); }
    $pd = build_pass_data($reg, $evt, $passId);
    $wa = [
        'reg_id'  => $reg['id'],
        'phone'   => wa_normalize_phone(clean_text($reg['whatsapp_number'])),
        'pass_id' => $passId,
        'name'    => $pd['student_name'],
        'event'   => $pd['event_name'],
        'date'    => $pd['event_date'],
        'venue'   => $pd['event_venue'],
    ];
    $res = wa_store_pdf_and_send($pdo, $wa, $_FILES['pdf'] ?? null);
    http_response_code($res['code']);
    echo json_encode(['ok' => $res['ok'], 'pdf' => (bool)WA_USE_PDF_HEADER, 'message' => $res['message']]);
    exit();
}

$msg = ''; $msgType = '';

// ---------- Update / Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM student_registrations WHERE id = ?")->execute([$id]);
        header("Location: view_registrations.php?deleted=1");
        exit();
    }
    if ($action === 'update') {
        try {
            $c = function ($k) { return htmlspecialchars(trim((string)($_POST[$k] ?? '')), ENT_QUOTES, 'UTF-8'); };
            $email = filter_var(trim((string)($_POST['email_id'] ?? '')), FILTER_SANITIZE_EMAIL);
            if ($c('student_name') === '' || $c('mobile_number') === '') throw new Exception('Name and mobile are required.');
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Invalid email address.');
            $newEvent = filter_var($_POST['event_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $status = in_array($_POST['status'] ?? '', ['Completed', 'Partial'], true) ? $_POST['status'] : 'Completed';

            $pdo->prepare("UPDATE student_registrations SET
                    student_name = :n, gender = :g, mobile_number = :m, whatsapp_number = :w, parent_mobile = :p,
                    email_id = :e, city = :c, event_id = :ev, source = :s, status = :st,
                    ref1_name = :r1n, ref1_mobile = :r1m, ref2_name = :r2n, ref2_mobile = :r2m
                WHERE id = :id")
                ->execute([
                    ':n' => $c('student_name'), ':g' => $c('gender'), ':m' => $c('mobile_number'),
                    ':w' => $c('whatsapp_number'), ':p' => $c('parent_mobile'), ':e' => $email,
                    ':c' => $c('city'), ':ev' => $newEvent, ':s' => $c('source'), ':st' => $status,
                    ':r1n' => $c('ref1_name'), ':r1m' => $c('ref1_mobile'), ':r2n' => $c('ref2_name'), ':r2m' => $c('ref2_mobile'),
                    ':id' => $id,
                ]);
            $reg = $loadReg();
            $evt = $loadEvt($reg['event_id']);
            $msg = 'Registration updated. You can resend the admit card on WhatsApp if needed.'; $msgType = 'success';
        } catch (Exception $e) {
            $msg = $e->getMessage(); $msgType = 'error';
        }
    }
}

$passData = build_pass_data($reg, $evt, $passId);
$waPhone  = wa_normalize_phone(clean_text($reg['whatsapp_number']));
$waReady  = (bool)$waPhone;
$waMasked = wa_mask_phone($waPhone);
$waEndpoint = 'pass.php?id=' . (int)$id . '&send_wa=1';
$waCsrf   = admin_csrf();
$waNonce  = '';
$waAutoSend = $waReady && isset($_GET['send']);

$allEvents = $pdo->query("SELECT id, event_name FROM events ORDER BY created_at DESC")->fetchAll();
$v = function ($k) use ($reg) { return htmlspecialchars(clean_text($reg[$k] ?? ''), ENT_QUOTES, 'UTF-8'); };
$waSt = $reg['wa_status'] ?? 'Pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card <?= htmlspecialchars($passId, ENT_QUOTES, 'UTF-8') ?> - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php require __DIR__ . '/../includes/admit_card_styles.php'; ?>
    <style>
        .edit-grid label { margin-bottom: 4px; }
        .preview-box { width: 100%; overflow: hidden; border-radius: 12px; }
        .preview-box .image-admit-card-wrapper { transform-origin: top left; }
    </style>
</head>
<body class="p-4 md:p-8">
<div class="max-w-6xl mx-auto">

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-extrabold text-[#1a2a4a]">Admit Card — <span class="text-[#d9381e]"><?= htmlspecialchars($passId, ENT_QUOTES, 'UTF-8') ?></span></h2>
            <p class="text-sm text-gray-500">Registered: <?= date('d M Y, h:i A', strtotime($reg['created_at'])) ?> · WhatsApp: <b><?= htmlspecialchars($waSt, ENT_QUOTES, 'UTF-8') ?></b><?= $reg['wa_sent_at'] ? ' (' . date('d M, h:i A', strtotime($reg['wa_sent_at'])) . ')' : '' ?></p>
        </div>
        <a href="view_registrations.php" class="text-sm font-bold text-[#1a2a4a] bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 uppercase tracking-wide">&larr; Registrations</a>
    </div>

    <?php if ($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold text-sm <?= $msgType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' ?>"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- LEFT: preview + actions -->
        <div class="premium-card">
            <h3 class="font-extrabold text-[#1a2a4a] mb-4">Admit Card Preview</h3>
            <div class="preview-box" id="previewBox"></div>

            <div class="mt-6">
                <?php if ($waReady): ?>
                    <div id="waStatus" class="wa-status pending">WhatsApp: <?= htmlspecialchars($waMasked, ENT_QUOTES, 'UTF-8') ?></div>
                <?php else: ?>
                    <div id="waStatus" class="wa-status error">WhatsApp number is invalid. Edit the number on the right, then send.</div>
                <?php endif; ?>
                <div class="flex flex-col sm:flex-row gap-3 mt-3">
                    <button type="button" onclick="downloadPDF()" class="download-btn" style="margin:0;">Download PDF</button>
                    <?php if ($waReady): ?>
                        <button type="button" onclick="sendAdmitToWhatsApp()" class="download-btn" style="margin:0;background:#16a34a;">Send on WhatsApp</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: edit details -->
        <div class="premium-card">
            <h3 class="font-extrabold text-[#1a2a4a] mb-4">Student Details</h3>
            <form method="POST" class="edit-grid space-y-4">
                <input type="hidden" name="action" value="update">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div><label>Full Name *</label><input name="student_name" value="<?= $v('student_name') ?>" required></div>
                    <div><label>Gender</label>
                        <select name="gender">
                            <?php foreach (['Male', 'Female'] as $g): ?><option <?= clean_text($reg['gender']) === $g ? 'selected' : '' ?>><?= $g ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div><label>Mobile *</label><input name="mobile_number" value="<?= $v('mobile_number') ?>" required></div>
                    <div><label>WhatsApp</label><input name="whatsapp_number" value="<?= $v('whatsapp_number') ?>"></div>
                    <div><label>Parent Mobile</label><input name="parent_mobile" value="<?= $v('parent_mobile') ?>"></div>
                    <div><label>Email</label><input name="email_id" type="email" value="<?= $v('email_id') ?>"></div>
                    <div><label>City</label><input name="city" value="<?= $v('city') ?>"></div>
                    <div><label>Source</label><input name="source" value="<?= $v('source') ?>"></div>
                    <div><label>Ref 1 Name</label><input name="ref1_name" value="<?= $v('ref1_name') ?>"></div>
                    <div><label>Ref 1 Mobile</label><input name="ref1_mobile" value="<?= $v('ref1_mobile') ?>"></div>
                    <div><label>Ref 2 Name</label><input name="ref2_name" value="<?= $v('ref2_name') ?>"></div>
                    <div><label>Ref 2 Mobile</label><input name="ref2_mobile" value="<?= $v('ref2_mobile') ?>"></div>
                    <div><label>Event</label>
                        <select name="event_id">
                            <option value="">— None —</option>
                            <?php foreach ($allEvents as $e): ?>
                                <option value="<?= (int)$e['id'] ?>" <?= (int)$reg['event_id'] === (int)$e['id'] ? 'selected' : '' ?>><?= htmlspecialchars(clean_text($e['event_name']), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label>Status</label>
                        <select name="status">
                            <?php foreach (['Completed', 'Partial'] as $s): ?><option <?= $reg['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Interest: <?= $v('interested_in') ?: '-' ?> · Courses: <?= $v('courses') ?: '-' ?></p>
                <button class="w-full bg-[#1a2a4a] text-white font-bold py-3 rounded-xl hover:bg-[#0f1f3d]">Save Changes</button>
            </form>

            <form method="POST" class="mt-4" onsubmit="return confirm('Delete this registration permanently?');">
                <input type="hidden" name="action" value="delete">
                <button class="w-full bg-red-50 text-red-700 border border-red-200 font-bold py-2.5 rounded-xl hover:bg-red-100 text-sm">Delete Registration</button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/admit_card_markup.php'; ?>
<?php require __DIR__ . '/../includes/admit_card_script.php'; ?>

<script>
// Small on-screen preview (the PDF is always generated from the full 800px card)
(function () {
    var box = document.getElementById('previewBox');
    var src = document.getElementById('eventPass');
    if (!box || !src) return;
    var clone = src.cloneNode(true);
    clone.removeAttribute('id');
    clone.classList.remove('admit-source-hidden');
    clone.style.width = '800px'; clone.style.maxWidth = 'none';
    box.appendChild(clone);
    function fit() {
        var s = box.clientWidth / 800;
        clone.style.transform = 'scale(' + s + ')';
        box.style.height = (clone.offsetHeight * s) + 'px';
    }
    window.addEventListener('resize', fit);
    clone.querySelectorAll('img').forEach(function (img) { img.addEventListener('load', fit); });
    window.addEventListener('load', fit);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(fit);
    fit();
})();
</script>
</body>
</html>
