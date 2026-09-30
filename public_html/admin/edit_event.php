<?php
// High Security Headers for Core PHP
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

require_once __DIR__ . '/auth.php';

$msg = '';
$msgType = '';
$event = null;
$current_agents = [];

// 1. Fetch Existing Event Data
if (isset($_GET['id'])) {
    $edit_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    if (!$edit_id) {
        die("Invalid Event ID.");
    }

    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $edit_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        die("Event not found.");
    }

    // Fetch existing agents for this event
    $agentStmt = $pdo->prepare("SELECT id, agent_name FROM agents WHERE event_id = :id ORDER BY id ASC");
    $agentStmt->execute([':id' => $edit_id]);
    $current_agents = $agentStmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    header("Location: view_events.php");
    exit();
}

// 2. Handle Update Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $event_name = htmlspecialchars(trim($_POST['event_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $event_date = htmlspecialchars(trim($_POST['event_date'] ?? ''), ENT_QUOTES, 'UTF-8');
        $event_venue = htmlspecialchars(trim($_POST['event_venue'] ?? ''), ENT_QUOTES, 'UTF-8');
        $contact_phone = htmlspecialchars(trim($_POST['contact_phone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $offer_zone = htmlspecialchars(trim($_POST['offer_zone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $expert_details = htmlspecialchars(trim($_POST['expert_details'] ?? ''), ENT_QUOTES, 'UTF-8');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Keep the old logo by default unless a new one is uploaded
        $logo_path = $event['logo_path'] ?? null;
        $photo_path = $event['photo_path'] ?? null;

        $event_type  = htmlspecialchars(trim($_POST['event_type'] ?? ''), ENT_QUOTES, 'UTF-8');
        $city        = htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8');
        $event_time  = htmlspecialchars(trim($_POST['event_time'] ?? ''), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
        $seat_limit  = htmlspecialchars(trim($_POST['seat_limit'] ?? ''), ENT_QUOTES, 'UTF-8');
        $sort_order  = (int)($_POST['sort_order'] ?? 0);


        if (empty($event_name)) {
            throw new Exception("Event Name is required.");
        }

        // Handle New Logo Upload (if provided)
        $newLogo = save_uploaded_image($_FILES['event_logo'] ?? null, '/storage/events');
        if ($newLogo) { delete_public_file($logo_path); $logo_path = $newLogo; }

        // Website photo: new upload / remove
        $newPhoto = save_uploaded_image($_FILES['event_photo'] ?? null, '/storage/events');
        if ($newPhoto) { delete_public_file($photo_path); $photo_path = $newPhoto; }
        elseif (!empty($_POST['remove_photo'])) { delete_public_file($photo_path); $photo_path = null; }

        // Entry Pass template: keep the old one by default unless a new one is uploaded or removed
        $entry_pass_bg = $event['entry_pass_bg'] ?? null;
        $clampPct = function ($k) {
            return isset($_POST[$k]) && $_POST[$k] !== '' ? max(0, min(100, (int)$_POST[$k])) : null;
        };
        $ep_name_x = $clampPct('entry_pass_name_x'); $ep_name_y = $clampPct('entry_pass_name_y');
        $ep_reg_x  = $clampPct('entry_pass_reg_x');  $ep_reg_y  = $clampPct('entry_pass_reg_y');
        $ep_qr_x   = $clampPct('entry_pass_qr_x');   $ep_qr_y   = $clampPct('entry_pass_qr_y');
        $ep_qr_size = $clampPct('entry_pass_qr_size');

        $newEntryBg = save_uploaded_image($_FILES['entry_pass_bg'] ?? null, '/storage/events');
        if ($newEntryBg) { delete_public_file($entry_pass_bg); $entry_pass_bg = $newEntryBg; }
        elseif (!empty($_POST['remove_entry_pass_bg'])) {
            delete_public_file($entry_pass_bg);
            $entry_pass_bg = null;
            $ep_name_x = $ep_name_y = $ep_reg_x = $ep_reg_y = $ep_qr_x = $ep_qr_y = $ep_qr_size = null;
        }

        // Update Event Record
        $updateStmt = $pdo->prepare("
            UPDATE events SET
                event_name = :name, event_date = :date, event_venue = :venue,
                contact_phone = :phone, offer_zone = :offer, expert_details = :expert,
                logo_path = :path, is_active = :active,
                photo_path = :photo, event_type = :etype, city = :city, event_time = :etime,
                description = :descr, seat_limit = :seats, sort_order = :sort,
                entry_pass_bg = :ep_bg, entry_pass_name_x = :ep_nx, entry_pass_name_y = :ep_ny,
                entry_pass_reg_x = :ep_rx, entry_pass_reg_y = :ep_ry,
                entry_pass_qr_x = :ep_qx, entry_pass_qr_y = :ep_qy, entry_pass_qr_size = :ep_qs
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':name' => $event_name, ':date' => $event_date, ':venue' => $event_venue,
            ':phone' => $contact_phone, ':offer' => $offer_zone, ':expert' => $expert_details,
            ':path' => $logo_path, ':active' => $is_active, ':id' => $edit_id,
            ':photo' => $photo_path, ':etype' => $event_type, ':city' => $city, ':etime' => $event_time,
            ':descr' => $description, ':seats' => $seat_limit, ':sort' => $sort_order,
            ':ep_bg' => $entry_pass_bg, ':ep_nx' => $ep_name_x, ':ep_ny' => $ep_name_y,
            ':ep_rx' => $ep_reg_x, ':ep_ry' => $ep_reg_y, ':ep_qx' => $ep_qr_x, ':ep_qy' => $ep_qr_y, ':ep_qs' => $ep_qr_size,
        ]);

        // Handle Agents Update
        // Strategy: Delete all existing agents for this event, then re-insert the submitted ones
        $delAgents = $pdo->prepare("DELETE FROM agents WHERE event_id = :id");
        $delAgents->execute([':id' => $edit_id]);

        if (!empty($_POST['agents']) && is_array($_POST['agents'])) {
            $insertAgent = $pdo->prepare("INSERT INTO agents (event_id, agent_name) VALUES (:event_id, :agent_name)");
            foreach ($_POST['agents'] as $agent) {
                $clean_agent = htmlspecialchars(trim($agent), ENT_QUOTES, 'UTF-8');
                if (!empty($clean_agent)) {
                    $insertAgent->execute([':event_id' => $edit_id, ':agent_name' => $clean_agent]);
                }
            }
        }

        $pdo->commit();
        
        $msg = "Event successfully updated.";
        $msgType = "success";
        
        // Refresh event data to show updated values in the form
        $stmt->execute([':id' => $edit_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        $agentStmt->execute([':id' => $edit_id]);
        $current_agents = $agentStmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $msg = $e->getMessage();
        $msgType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - <?= htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') ?> Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function addAgentField() {
            const container = document.getElementById('agent-container');
            const row = document.createElement('div');
            row.className = 'flex gap-2 mb-3';
            row.innerHTML = `
                <input type="text" name="agents[]" placeholder="Enter Agent Name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                <button type="button" onclick="this.parentElement.remove()" class="bg-red-50 text-red-600 px-4 py-2 rounded-lg border border-red-200 font-bold hover:bg-red-100 transition-colors">X</button>
            `;
            container.appendChild(row);
        }

        // ── Entry Pass template: preview + drag-to-position ──────────────────
        function epMakeDraggable(chipId, hxId, hyId) {
            var stage = document.getElementById('epStage');
            var chip = document.getElementById(chipId);
            var hx = document.getElementById(hxId);
            var hy = document.getElementById(hyId);
            var dragging = false;
            function move(clientX, clientY) {
                var rect = stage.getBoundingClientRect();
                var x = Math.max(0, Math.min(100, ((clientX - rect.left) / rect.width) * 100));
                var y = Math.max(0, Math.min(100, ((clientY - rect.top) / rect.height) * 100));
                chip.style.left = x + '%';
                chip.style.top = y + '%';
                hx.value = Math.round(x);
                hy.value = Math.round(y);
            }
            chip.addEventListener('mousedown', function (e) { dragging = true; chip.style.cursor = 'grabbing'; e.preventDefault(); });
            chip.addEventListener('touchstart', function () { dragging = true; }, { passive: true });
            window.addEventListener('mousemove', function (e) { if (dragging) move(e.clientX, e.clientY); });
            window.addEventListener('touchmove', function (e) { if (dragging && e.touches[0]) { move(e.touches[0].clientX, e.touches[0].clientY); e.preventDefault(); } }, { passive: false });
            window.addEventListener('mouseup', function () { dragging = false; chip.style.cursor = 'grab'; });
            window.addEventListener('touchend', function () { dragging = false; });
        }
        document.addEventListener('DOMContentLoaded', function () {
            epMakeDraggable('epName', 'epNameX', 'epNameY');
            epMakeDraggable('epReg', 'epRegX', 'epRegY');
            epMakeDraggable('epQr', 'epQrX', 'epQrY');

            document.getElementById('epQrSizeRange').addEventListener('input', function () {
                document.getElementById('epQr').style.width = this.value + '%';
                document.getElementById('epQrSize').value = this.value;
            });

            document.getElementById('epBgInput').addEventListener('change', function () {
                var file = this.files && this.files[0];
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('epImg').src = e.target.result;
                    document.getElementById('epPreviewWrap').style.display = '';
                    document.getElementById('epRemoveFlag').value = '0';
                };
                reader.readAsDataURL(file);
            });

            var epRemoveBtn = document.getElementById('epRemoveBtn');
            if (epRemoveBtn) epRemoveBtn.addEventListener('click', function () {
                document.getElementById('epBgInput').value = '';
                document.getElementById('epPreviewWrap').style.display = 'none';
                document.getElementById('epRemoveFlag').value = '1';
            });
        });
    </script>
</head>
<body class="bg-[#f8f9fa] font-sans text-gray-800 p-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6"><a href="view_events.php" class="text-sm font-bold text-[#2d4a8a] hover:underline">&larr; Back to Events List</a></div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold text-[#1a2a4a] mb-6">Edit Event: <?= htmlspecialchars(clean_text($event['event_name']), ENT_QUOTES, 'UTF-8') ?></h2>
            
            <?php if($msg): ?>
                <div class="p-4 mb-6 rounded-lg text-sm font-medium <?= $msgType === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Event Name *</label>
                    <input type="text" name="event_name" value="<?= htmlspecialchars(clean_text($event['event_name']), ENT_QUOTES, 'UTF-8') ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Event Date & Time</label>
                        <input type="text" name="event_date" value="<?= htmlspecialchars(clean_text($event['event_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Contact Phone</label>
                        <input type="text" name="contact_phone" value="<?= htmlspecialchars(clean_text($event['contact_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Event Venue</label>
                    <input type="text" name="event_venue" value="<?= htmlspecialchars(clean_text($event['event_venue'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                </div>

                <hr class="border-gray-200">
                <h3 class="text-lg font-bold text-[#1a2a4a]">Additional Settings</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Offer Zone Text</label>
                        <input type="text" name="offer_zone" value="<?= htmlspecialchars(clean_text($event['offer_zone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Expert Details</label>
                        <input type="text" name="expert_details" value="<?= htmlspecialchars(clean_text($event['expert_details'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                </div>

                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700">Assign Agents / Referral Sources</label>
                        </div>
                        <button type="button" onclick="addAgentField()" class="bg-[#2d4a8a] text-white px-3 py-1.5 rounded-md font-bold text-xs hover:bg-blue-700 transition-colors">+ Add Agent</button>
                    </div>

                    <div id="agent-container">
                        <?php if(empty($current_agents)): ?>
                            <div class="flex gap-2 mb-3">
                                <input type="text" name="agents[]" placeholder="Enter Agent Name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                            </div>
                        <?php else: ?>
                            <?php foreach($current_agents as $agent): ?>
                                <div class="flex gap-2 mb-3">
                                    <input type="text" name="agents[]" value="<?= htmlspecialchars(clean_text($agent['agent_name']), ENT_QUOTES, 'UTF-8') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                                    <button type="button" onclick="this.parentElement.remove()" class="bg-red-50 text-red-600 px-4 py-2 rounded-lg border border-red-200 font-bold hover:bg-red-100 transition-colors">X</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>


                <hr class="border-gray-200">
                <div>
                    <h3 class="text-lg font-bold text-[#1a2a4a]">Website Details <span class="text-xs font-semibold text-gray-400">(Home page "Upcoming Events" card)</span></h3>
                    <p class="text-xs text-gray-500 mt-1">Only active events are shown on the website. The photo appears at the top of the event card.</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Event Photo / Banner (JPG, PNG or WEBP — up to 20MB, resized automatically)</label>
                    <?php if(!empty($event['photo_path'])): ?>
                        <div class="mb-3 flex items-end gap-3">
                            <img src="<?= htmlspecialchars($event['photo_path'], ENT_QUOTES, 'UTF-8') ?>" class="h-24 w-40 object-cover rounded-lg border border-gray-200">
                            <label class="text-xs font-bold text-red-600 flex items-center gap-1 cursor-pointer"><input type="checkbox" name="remove_photo" value="1"> Remove photo</label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="event_photo" accept="image/png, image/jpeg, image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-[#2d4a8a] hover:file:bg-blue-100 cursor-pointer">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Event Type</label>
                        <select name="event_type" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none bg-white">
<option value="General Education Fair" <?= clean_text($event['event_type'] ?? '') === 'General Education Fair' ? 'selected' : '' ?>>General Education Fair</option>
<option value="MBA Fair" <?= clean_text($event['event_type'] ?? '') === 'MBA Fair' ? 'selected' : '' ?>>MBA Fair</option>
<option value="UG Fair" <?= clean_text($event['event_type'] ?? '') === 'UG Fair' ? 'selected' : '' ?>>UG Fair</option>
<option value="PG Fair" <?= clean_text($event['event_type'] ?? '') === 'PG Fair' ? 'selected' : '' ?>>PG Fair</option>
<option value="Study Abroad Fair" <?= clean_text($event['event_type'] ?? '') === 'Study Abroad Fair' ? 'selected' : '' ?>>Study Abroad Fair</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">City</label>
                        <input type="text" name="city" value="<?= htmlspecialchars(clean_text($event['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g., Lucknow" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Timing</label>
                        <input type="text" name="event_time" value="<?= htmlspecialchars(clean_text($event['event_time'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g., 10:00 AM - 5:00 PM" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Short Description</label>
                        <textarea name="description" rows="3" placeholder="e.g., Meet 30+ top colleges, free counselling, scholarship info..." class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none"><?= htmlspecialchars(clean_text($event['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Seats</label>
                        <input type="text" name="seat_limit" value="<?= htmlspecialchars(clean_text($event['seat_limit'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g., 500" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                        <label class="block text-sm font-bold text-gray-700 mb-1 mt-3">Display Order</label>
                        <input type="number" name="sort_order" value="<?= htmlspecialchars(clean_text($event['sort_order'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Update Event Logo (Optional)</label>
                    <?php if(!empty($event['logo_path'] ?? '')): ?>
                        <div class="mb-3">
                            <span class="text-xs text-gray-500 block mb-1">Current Logo:</span>
                            <img src="<?= htmlspecialchars($event['logo_path'], ENT_QUOTES, 'UTF-8') ?>" class="h-16 w-auto rounded border border-gray-200 bg-white p-1">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="event_logo" accept="image/png, image/jpeg, image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-[#2d4a8a] hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-xs text-gray-400 mt-2">Leave blank to keep the current logo.</p>
                </div>

                <hr class="border-gray-200">
                <?php
                    $epHas = !empty($event['entry_pass_bg']);
                    $epV = function ($k, $d) use ($event) { return ($event[$k] ?? null) !== null ? (int)$event[$k] : $d; };
                    $epNameX = $epV('entry_pass_name_x', 50); $epNameY = $epV('entry_pass_name_y', 50);
                    $epRegX  = $epV('entry_pass_reg_x', 50);  $epRegY  = $epV('entry_pass_reg_y', 58);
                    $epQrX   = $epV('entry_pass_qr_x', 85);   $epQrY   = $epV('entry_pass_qr_y', 40);
                    $epQrS   = $epV('entry_pass_qr_size', 16);
                ?>
                <div>
                    <h3 class="text-lg font-bold text-[#1a2a4a]">Entry Pass Template <span class="text-xs font-semibold text-gray-400">(Optional — this event's own admit card design)</span></h3>
                    <p class="text-xs text-gray-500 mt-1">Upload a custom entry pass design (e.g. from Canva). Drag the <b>Name</b>, <b>Reg. No.</b> and <b>QR</b> boxes onto the spots you left blank on your design. Leave this blank to use the default AcademiaFest pass for this event.</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Entry Pass Background (JPG, PNG or WEBP)</label>
                    <input type="file" id="epBgInput" name="entry_pass_bg" accept="image/png, image/jpeg, image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-[#2d4a8a] hover:file:bg-blue-100 cursor-pointer">
                    <?php if (!$epHas): ?><p class="text-xs text-gray-400 mt-2">No custom template yet — this event currently uses the default AcademiaFest pass.</p><?php endif; ?>

                    <div id="epPreviewWrap" class="mt-4" style="<?= $epHas ? '' : 'display:none;' ?>">
                        <div id="epStage" style="position:relative; max-width:460px; margin:0 auto; user-select:none; -webkit-user-select:none; border-radius:10px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.15);">
                            <img id="epImg" style="width:100%; display:block;" alt="Preview" src="<?= $epHas ? htmlspecialchars($event['entry_pass_bg'], ENT_QUOTES, 'UTF-8') : '' ?>">
                            <div id="epName" class="ep-chip" style="position:absolute; left:<?= $epNameX ?>%; top:<?= $epNameY ?>%; transform:translate(-50%,-50%); background:#1a2a4a; color:#fff; font-weight:700; font-size:12px; padding:6px 12px; border-radius:6px; cursor:grab; white-space:nowrap; box-shadow:0 2px 8px rgba(0,0,0,.3);">Student Name</div>
                            <div id="epReg" class="ep-chip" style="position:absolute; left:<?= $epRegX ?>%; top:<?= $epRegY ?>%; transform:translate(-50%,-50%); background:#b8860b; color:#fff; font-weight:700; font-size:11px; padding:5px 10px; border-radius:6px; cursor:grab; white-space:nowrap; box-shadow:0 2px 8px rgba(0,0,0,.3);">AF-2026-00001</div>
                            <div id="epQr" class="ep-chip" style="position:absolute; left:<?= $epQrX ?>%; top:<?= $epQrY ?>%; width:<?= $epQrS ?>%; aspect-ratio:1/1; transform:translate(-50%,-50%); background:#fff; border:2px dashed #2d4a8a; border-radius:6px; cursor:grab; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; color:#2d4a8a;">QR</div>
                        </div>
                        <p class="text-xs text-gray-500 text-center mt-2">Drag each box onto its place on the design.</p>
                        <div class="max-w-xs mx-auto mt-3">
                            <label class="block text-xs font-bold text-gray-700 mb-1">QR Size</label>
                            <input type="range" id="epQrSizeRange" min="8" max="35" value="<?= $epQrS ?>" class="w-full">
                        </div>
                        <div class="text-center mt-2">
                            <button type="button" id="epRemoveBtn" class="text-xs font-bold text-red-600 hover:underline">Remove template &amp; use default pass</button>
                        </div>
                    </div>

                    <input type="hidden" name="remove_entry_pass_bg" id="epRemoveFlag" value="0">
                    <input type="hidden" name="entry_pass_name_x" id="epNameX" value="<?= $epNameX ?>">
                    <input type="hidden" name="entry_pass_name_y" id="epNameY" value="<?= $epNameY ?>">
                    <input type="hidden" name="entry_pass_reg_x" id="epRegX" value="<?= $epRegX ?>">
                    <input type="hidden" name="entry_pass_reg_y" id="epRegY" value="<?= $epRegY ?>">
                    <input type="hidden" name="entry_pass_qr_x" id="epQrX" value="<?= $epQrX ?>">
                    <input type="hidden" name="entry_pass_qr_y" id="epQrY" value="<?= $epQrY ?>">
                    <input type="hidden" name="entry_pass_qr_size" id="epQrSize" value="<?= $epQrS ?>">
                </div>

                <div class="flex items-center bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <input type="checkbox" name="is_active" id="is_active" <?= $event['is_active'] ? 'checked' : '' ?> class="h-5 w-5 text-[#2d4a8a] rounded border-gray-300">
                    <label for="is_active" class="ml-3 block text-sm font-medium text-gray-900 cursor-pointer">Set as Active</label>
                </div>

                <button type="submit" class="w-full bg-[#1a2a4a] text-white font-bold py-3.5 rounded-xl hover:bg-[#0f1f3d] transition-colors">Update Event Record</button>
            </form>
        </div>
    </div>
</body>
</html>