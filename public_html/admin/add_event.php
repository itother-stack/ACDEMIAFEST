<?php
// High Security Headers for Core PHP
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

require_once __DIR__ . '/auth.php';

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin Transaction for Multi-Table Insert Safety
        $pdo->beginTransaction();

        $event_name = htmlspecialchars(trim($_POST['event_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $event_date = htmlspecialchars(trim($_POST['event_date'] ?? ''), ENT_QUOTES, 'UTF-8');
        $event_venue = htmlspecialchars(trim($_POST['event_venue'] ?? ''), ENT_QUOTES, 'UTF-8');
        $contact_phone = htmlspecialchars(trim($_POST['contact_phone'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        // New Fields
        $offer_zone = htmlspecialchars(trim($_POST['offer_zone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $expert_details = htmlspecialchars(trim($_POST['expert_details'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $logo_path = null;

        $event_type  = htmlspecialchars(trim($_POST['event_type'] ?? ''), ENT_QUOTES, 'UTF-8');
        $city        = htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8');
        $event_time  = htmlspecialchars(trim($_POST['event_time'] ?? ''), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
        $seat_limit  = htmlspecialchars(trim($_POST['seat_limit'] ?? ''), ENT_QUOTES, 'UTF-8');
        $sort_order  = (int)($_POST['sort_order'] ?? 0);


        if (empty($event_name)) {
            throw new Exception("Event Name is required.");
        }

        // Secure File Upload Handling (logo + website photo + entry pass template)
        $logo_path  = save_uploaded_image($_FILES['event_logo'] ?? null, '/storage/events');
        $photo_path = save_uploaded_image($_FILES['event_photo'] ?? null, '/storage/events');
        $entry_pass_bg = save_uploaded_image($_FILES['entry_pass_bg'] ?? null, '/storage/events');

        $clampPct = function ($k) {
            return isset($_POST[$k]) && $_POST[$k] !== '' ? max(0, min(100, (int)$_POST[$k])) : null;
        };
        $ep_name_x = $clampPct('entry_pass_name_x'); $ep_name_y = $clampPct('entry_pass_name_y');
        $ep_reg_x  = $clampPct('entry_pass_reg_x');  $ep_reg_y  = $clampPct('entry_pass_reg_y');
        $ep_qr_x   = $clampPct('entry_pass_qr_x');   $ep_qr_y   = $clampPct('entry_pass_qr_y');
        $ep_qr_size = $clampPct('entry_pass_qr_size');

        // 1. Insert Event Data
        $stmt = $pdo->prepare("
            INSERT INTO events (event_name, event_date, event_venue, contact_phone, offer_zone, expert_details, logo_path, is_active,
                                photo_path, event_type, city, event_time, description, seat_limit, sort_order,
                                entry_pass_bg, entry_pass_name_x, entry_pass_name_y, entry_pass_reg_x, entry_pass_reg_y,
                                entry_pass_qr_x, entry_pass_qr_y, entry_pass_qr_size)
            VALUES (:name, :date, :venue, :phone, :offer, :expert, :path, :active,
                    :photo, :etype, :city, :etime, :descr, :seats, :sort,
                    :ep_bg, :ep_nx, :ep_ny, :ep_rx, :ep_ry, :ep_qx, :ep_qy, :ep_qs)
        ");
        $stmt->execute([
            ':name' => $event_name,
            ':date' => $event_date,
            ':venue' => $event_venue,
            ':phone' => $contact_phone,
            ':offer' => $offer_zone,
            ':expert' => $expert_details,
            ':path' => $logo_path,
            ':active' => $is_active,
            ':photo' => $photo_path, ':etype' => $event_type, ':city' => $city, ':etime' => $event_time,
            ':descr' => $description, ':seats' => $seat_limit, ':sort' => $sort_order,
            ':ep_bg' => $entry_pass_bg, ':ep_nx' => $ep_name_x, ':ep_ny' => $ep_name_y,
            ':ep_rx' => $ep_reg_x, ':ep_ry' => $ep_reg_y, ':ep_qx' => $ep_qr_x, ':ep_qy' => $ep_qr_y, ':ep_qs' => $ep_qr_size,
        ]);

        // 2. Fetch the newly created Event ID
        $new_event_id = $pdo->lastInsertId();

        // 3. Insert Dynamic Agents
        if (!empty($_POST['agents']) && is_array($_POST['agents'])) {
            $agentStmt = $pdo->prepare("INSERT INTO agents (event_id, agent_name) VALUES (:event_id, :agent_name)");
            foreach ($_POST['agents'] as $agent) {
                $clean_agent = htmlspecialchars(trim($agent), ENT_QUOTES, 'UTF-8');
                if (!empty($clean_agent)) {
                    $agentStmt->execute([
                        ':event_id' => $new_event_id,
                        ':agent_name' => $clean_agent
                    ]);
                }
            }
        }

        // Commit Transaction
        $pdo->commit();

        $msg = "Event and Agents successfully added.";
        $msgType = "success";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack(); // Revert everything if anything fails
        if (!empty($photo_path)) delete_public_file($photo_path);
        if (!empty($logo_path)) delete_public_file($logo_path);
        if (!empty($entry_pass_bg)) delete_public_file($entry_pass_bg);
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
    <title>Add Event - <?= htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') ?> Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // JavaScript function to add dynamic Agent fields
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
                };
                reader.readAsDataURL(file);
            });

            var epRemoveBtn = document.getElementById('epRemoveBtn');
            if (epRemoveBtn) epRemoveBtn.addEventListener('click', function () {
                document.getElementById('epBgInput').value = '';
                document.getElementById('epPreviewWrap').style.display = 'none';
                var rm = document.getElementById('epRemoveFlag');
                if (rm) rm.value = '1';
            });
        });
    </script>
</head>
<body class="bg-[#f8f9fa] font-sans text-gray-800 p-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6"><a href="admin_dashboard.php" class="text-sm font-bold text-[#2d4a8a] hover:underline">&larr; Back to Dashboard</a></div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold text-[#1a2a4a] mb-6">Create New Event</h2>
            
            <?php if($msg): ?>
                <div class="p-4 mb-6 rounded-lg text-sm font-medium <?= $msgType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Event Name *</label>
                    <input type="text" name="event_name" required placeholder="e.g., Mega Education Fair 2026" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Event Date & Time</label>
                        <input type="text" name="event_date" placeholder="e.g., SUNDAY, 24TH MAY 2026" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Contact Phone</label>
                        <input type="text" name="contact_phone" placeholder="e.g., 92360-31588" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Event Venue</label>
                    <input type="text" name="event_venue" placeholder="e.g., HOTEL THE LANDMARK KANPUR" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                </div>

                <hr class="border-gray-200">
                <h3 class="text-lg font-bold text-[#1a2a4a]">Additional Settings</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Offer Zone Text</label>
                        <input type="text" name="offer_zone" placeholder="e.g., Get Chance to win Laptop+Phone+Alexa" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                    
                </div>

                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700">Assign Agents / Referral Sources</label>
                            <p class="text-xs text-gray-500">Students will select these when asked "How you came to know about the event?".</p>
                        </div>
                        <button type="button" onclick="addAgentField()" class="bg-[#2d4a8a] text-white px-3 py-1.5 rounded-md font-bold text-xs hover:bg-blue-700 transition-colors">+ Add Agent</button>
                    </div>

                    <div id="agent-container">
                        <div class="flex gap-2 mb-3">
                            <input type="text" name="agents[]" placeholder="Enter Agent Name (e.g., Social Media, John Doe)" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                        </div>
                    </div>
                </div>


                <hr class="border-gray-200">
                <div>
                    <h3 class="text-lg font-bold text-[#1a2a4a]">Website Details <span class="text-xs font-semibold text-gray-400">(Home page "Upcoming Events" card)</span></h3>
                    <p class="text-xs text-gray-500 mt-1">Only active events are shown on the website. The photo appears at the top of the event card.</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Event Photo / Banner (JPG, PNG or WEBP — up to 20MB, resized automatically)</label>
                    <input type="file" name="event_photo" accept="image/png, image/jpeg, image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-[#2d4a8a] hover:file:bg-blue-100 cursor-pointer">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Event Type</label>
                        <select name="event_type" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none bg-white">
<option value="General Education Fair">General Education Fair</option>
<option value="MBA Fair">MBA Fair</option>
<option value="UG Fair">UG Fair</option>
<option value="PG Fair">PG Fair</option>
<option value="Study Abroad Fair">Study Abroad Fair</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">City</label>
                        <input type="text" name="city" value="" placeholder="e.g., Lucknow" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Timing</label>
                        <input type="text" name="event_time" value="" placeholder="e.g., 10:00 AM - 5:00 PM" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Short Description</label>
                        <textarea name="description" rows="3" placeholder="e.g., Meet 30+ top colleges, free counselling, scholarship info..." class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Seats</label>
                        <input type="text" name="seat_limit" value="" placeholder="e.g., 500" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                        <label class="block text-sm font-bold text-gray-700 mb-1 mt-3">Display Order</label>
                        <input type="number" name="sort_order" value="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2d4a8a] outline-none">
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Event Logo (Optional)</label>
                    <input type="file" name="event_logo" accept="image/png, image/jpeg, image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-[#2d4a8a] hover:file:bg-blue-100 cursor-pointer">
                </div>

                <hr class="border-gray-200">
                <div>
                    <h3 class="text-lg font-bold text-[#1a2a4a]">Entry Pass Template <span class="text-xs font-semibold text-gray-400">(Optional — this event's own admit card design)</span></h3>
                    <p class="text-xs text-gray-500 mt-1">Upload a custom entry pass design (e.g. from Canva). Drag the <b>Name</b>, <b>Reg. No.</b> and <b>QR</b> boxes onto the spots you left blank on your design. Leave this blank to use the default AcademiaFest pass for this event.</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Entry Pass Background (JPG, PNG or WEBP)</label>
                    <input type="file" id="epBgInput" name="entry_pass_bg" accept="image/png, image/jpeg, image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-[#2d4a8a] hover:file:bg-blue-100 cursor-pointer">

                    <div id="epPreviewWrap" class="mt-4" style="display:none;">
                        <div id="epStage" style="position:relative; max-width:460px; margin:0 auto; user-select:none; -webkit-user-select:none; border-radius:10px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.15);">
                            <img id="epImg" style="width:100%; display:block;" alt="Preview">
                            <div id="epName" class="ep-chip" style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); background:#1a2a4a; color:#fff; font-weight:700; font-size:12px; padding:6px 12px; border-radius:6px; cursor:grab; white-space:nowrap; box-shadow:0 2px 8px rgba(0,0,0,.3);">Student Name</div>
                            <div id="epReg" class="ep-chip" style="position:absolute; left:50%; top:58%; transform:translate(-50%,-50%); background:#b8860b; color:#fff; font-weight:700; font-size:11px; padding:5px 10px; border-radius:6px; cursor:grab; white-space:nowrap; box-shadow:0 2px 8px rgba(0,0,0,.3);">AF-2026-00001</div>
                            <div id="epQr" class="ep-chip" style="position:absolute; left:85%; top:40%; width:16%; aspect-ratio:1/1; transform:translate(-50%,-50%); background:#fff; border:2px dashed #2d4a8a; border-radius:6px; cursor:grab; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; color:#2d4a8a;">QR</div>
                        </div>
                        <p class="text-xs text-gray-500 text-center mt-2">Drag each box onto its place on the design.</p>
                        <div class="max-w-xs mx-auto mt-3">
                            <label class="block text-xs font-bold text-gray-700 mb-1">QR Size</label>
                            <input type="range" id="epQrSizeRange" min="8" max="35" value="16" class="w-full">
                        </div>
                        <div class="text-center mt-2">
                            <button type="button" id="epRemoveBtn" class="text-xs font-bold text-red-600 hover:underline">Remove template &amp; use default pass</button>
                        </div>
                    </div>

                    <input type="hidden" name="entry_pass_name_x" id="epNameX" value="50">
                    <input type="hidden" name="entry_pass_name_y" id="epNameY" value="50">
                    <input type="hidden" name="entry_pass_reg_x" id="epRegX" value="50">
                    <input type="hidden" name="entry_pass_reg_y" id="epRegY" value="58">
                    <input type="hidden" name="entry_pass_qr_x" id="epQrX" value="85">
                    <input type="hidden" name="entry_pass_qr_y" id="epQrY" value="40">
                    <input type="hidden" name="entry_pass_qr_size" id="epQrSize" value="16">
                </div>

                <div class="flex items-center bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <input type="checkbox" name="is_active" id="is_active" checked class="h-5 w-5 text-[#2d4a8a] rounded border-gray-300">
                    <label for="is_active" class="ml-3 block text-sm font-medium text-gray-900 cursor-pointer">Set as Active</label>
                </div>

                <button type="submit" class="w-full bg-[#1a2a4a] text-white font-bold py-3.5 rounded-xl hover:bg-[#0f1f3d] transition-colors">Save Event & Agents</button>
            </form>
        </div>
    </div>
</body>
</html>