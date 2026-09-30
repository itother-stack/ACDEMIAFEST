<?php
// =====================================================================
//  register/index.php — Student Event Registration (public form)
//  Form, admit card, PDF and WhatsApp — same as the original
// =====================================================================
require_once __DIR__ . '/../includes/functions.php';   // also loads db.php + config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");

// ---------------------------------------------------------
// 1. AJAX: agents of an event (Marketing Source dropdown)
// ---------------------------------------------------------
if (isset($_GET['fetch_agents']) && isset($_GET['event_id'])) {
    header('Content-Type: application/json');
    $event_id = filter_var($_GET['event_id'], FILTER_VALIDATE_INT);
    if ($event_id) {
        $stmt = $pdo->prepare("SELECT agent_name FROM agents WHERE event_id = :event_id ORDER BY agent_name ASC");
        $stmt->execute([':event_id' => $event_id]);
        $agents = array_map(function ($a) { return ['agent_name' => clean_text($a['agent_name'])]; }, $stmt->fetchAll(PDO::FETCH_ASSOC));
        echo json_encode($agents);
    } else {
        echo json_encode([]);
    }
    exit();
}

// ---------------------------------------------------------
// 1B. AJAX: receive the admit card PDF generated in the browser and send it on WhatsApp
// ---------------------------------------------------------
if (isset($_GET['send_admit_wa'])) {
    ini_set('display_errors', '0');
    header('Content-Type: application/json; charset=utf-8');

    $fail = function ($msg, $code = 400) {
        http_response_code($code);
        echo json_encode(['ok' => false, 'message' => $msg]);
        exit();
    };

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') $fail('Invalid request method.', 405);
    if (empty($_POST['csrf_token']) || !hash_equals((string)$_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
        $fail('Invalid session token. Please refresh the page.', 403);
    }

    // Only for the registration just made in this session
    $wa = isset($_SESSION['wa_pending']) ? $_SESSION['wa_pending'] : null;
    if (!$wa || empty($_POST['nonce']) || !hash_equals((string)$wa['nonce'], (string)$_POST['nonce'])) {
        $fail('No pending admit card for this session.', 403);
    }
    if (!empty($wa['sent'])) {
        echo json_encode(['ok' => true, 'already' => true, 'pdf' => (bool)WA_USE_PDF_HEADER, 'message' => 'Already sent to WhatsApp.']);
        exit();
    }

    $res = wa_store_pdf_and_send($pdo, $wa, isset($_FILES['pdf']) ? $_FILES['pdf'] : null);
    if ($res['ok']) {
        $_SESSION['wa_pending']['sent'] = true;
        echo json_encode(['ok' => true, 'pdf' => (bool)WA_USE_PDF_HEADER, 'message' => $res['message']]);
        exit();
    }
    $fail($res['message'], $res['code']);
}

// ---------------------------------------------------------
// 2. REGISTRATION SUBMIT
// ---------------------------------------------------------
$success_message = '';
$error_message = '';
$passData = null;
$waReady = false;
$waMasked = '';
$waNonce = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf_token']) || !hash_equals((string)$_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
            throw new Exception("Security Alert: Invalid request session. Please refresh the page and try again.");
        }

        $clean = function ($key) {
            return htmlspecialchars(trim((string)($_POST[$key] ?? '')), ENT_QUOTES, 'UTF-8');
        };
        $student_name    = $clean('student_name');
        $gender          = $clean('gender');
        $mobile_number   = $clean('mobile_number');
        $whatsapp_number = $clean('whatsapp_number');
        $parent_mobile   = $clean('parent_mobile');
        $city            = $clean('city');
        $source          = $clean('source');
        $ref1_name       = $clean('ref1_name');
        $ref1_mobile     = $clean('ref1_mobile');
        $ref2_name       = $clean('ref2_name');
        $ref2_mobile     = $clean('ref2_mobile');

        $email_id = filter_var(trim((string)($_POST['email_id'] ?? '')), FILTER_SANITIZE_EMAIL);
        if (!filter_var($email_id, FILTER_VALIDATE_EMAIL)) throw new Exception("Please provide a valid email address.");

        $event_id = filter_var($_POST['event_id'] ?? null, FILTER_VALIDATE_INT);

        $joinArr = function ($key) {
            return isset($_POST[$key]) && is_array($_POST[$key])
                ? implode(', ', array_map(function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }, $_POST[$key]))
                : null;
        };
        $interested_in = $joinArr('interested_in');
        $courses       = $joinArr('courses');

        if (empty($student_name) || empty($mobile_number) || empty($email_id) || empty($event_id)) {
            throw new Exception("All required fields must be filled out prior to submission.");
        }

        // Make sure the event is active
        $evtStmt = $pdo->prepare("SELECT * FROM events WHERE id = :id AND is_active = 1 LIMIT 1");
        $evtStmt->execute([':id' => $event_id]);
        $evtData = $evtStmt->fetch(PDO::FETCH_ASSOC);
        if (!$evtData) throw new Exception("The selected event is not available for registration.");

        // Duplicate check
        $checkStmt = $pdo->prepare("
            SELECT id FROM student_registrations
            WHERE event_id = :event_id
            AND (mobile_number = :mobile_number OR whatsapp_number = :whatsapp_number)
            LIMIT 1
        ");
        $checkStmt->execute([
            ':event_id' => $event_id,
            ':mobile_number' => $mobile_number,
            ':whatsapp_number' => $whatsapp_number
        ]);
        if ($checkStmt->fetch()) {
            throw new Exception("Registration Declined: This contact number is already registered for the selected event.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO student_registrations (
                interested_in, student_name, gender, mobile_number, whatsapp_number,
                parent_mobile, email_id, city, courses, event_id,
                ref1_name, ref1_mobile, ref2_name, ref2_mobile, source, status
            ) VALUES (
                :interested_in, :student_name, :gender, :mobile_number, :whatsapp_number,
                :parent_mobile, :email_id, :city, :courses, :event_id,
                :ref1_name, :ref1_mobile, :ref2_name, :ref2_mobile, :source, 'Completed'
            )
        ");
        $reg = [
            'interested_in' => $interested_in, 'student_name' => $student_name, 'gender' => $gender,
            'mobile_number' => $mobile_number, 'whatsapp_number' => $whatsapp_number,
            'parent_mobile' => $parent_mobile, 'email_id' => $email_id, 'city' => $city,
            'courses' => $courses, 'event_id' => $event_id, 'ref1_name' => $ref1_name,
            'ref1_mobile' => $ref1_mobile, 'ref2_name' => $ref2_name, 'ref2_mobile' => $ref2_mobile,
            'source' => $source,
        ];
        $params = [];
        foreach ($reg as $k => $v) $params[':' . $k] = $v;
        $stmt->execute($params);

        // ---------------------------------------------------------
        // 3. ADMIT CARD DATA + QR
        // ---------------------------------------------------------
        $new_registration_id = (int)$pdo->lastInsertId();
        $unique_pass_id = make_pass_id($new_registration_id);
        $passData = build_pass_data($reg, $evtData, $unique_pass_id);

        // ---------------------------------------------------------
        // 4. WHATSAPP (the browser generates the PDF and uploads it)
        // ---------------------------------------------------------
        $waPhone = wa_normalize_phone($whatsapp_number);
        $waNonce = bin2hex(random_bytes(16));
        $_SESSION['wa_pending'] = [
            'nonce'   => $waNonce,
            'sent'    => false,
            'reg_id'  => $new_registration_id,
            'phone'   => $waPhone,
            'pass_id' => $unique_pass_id,
            'name'    => $passData['student_name'],
            'event'   => $passData['event_name'],
            'date'    => $passData['event_date'],
            'venue'   => $passData['event_venue'],
        ];
        if ($waPhone) {
            $waReady  = true;
            $waMasked = wa_mask_phone($waPhone);
        } else {
            $pdo->prepare("UPDATE student_registrations SET wa_status = 'Invalid' WHERE id = ?")->execute([$new_registration_id]);
        }

        $success_message = "Your registration has been successfully processed.";
    } catch (PDOException $e) {
        error_log('[REG] DB error: ' . $e->getMessage());
        if ($e->getCode() === '23000' && stripos($e->getMessage(), 'Duplicate') !== false) {
            $error_message = "Registration Declined: This contact number is already registered.";
        } else {
            $error_message = SHOW_ERRORS ? 'Database error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') : 'Something went wrong. Please try again.';
        }
    } catch (Exception $e) {
        $error_message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

// Pre-select the event coming from the home page (?event=ID)
$preselect_event = filter_var($_POST['event_id'] ?? ($_GET['event'] ?? null), FILTER_VALIDATE_INT) ?: 0;

// Active events
$events = [];
try {
    $events = $pdo->query("SELECT id, event_name FROM events WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Events could not be loaded right now. Please try again in a moment.";
    error_log('[REG] events: ' . $e->getMessage());
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admit_card_styles.php';
?>

<section class="reg-hero">
    <div class="reg-hero-badge"><?= $passData ? 'Registration Complete' : 'Event Registration' ?></div>
    <h1><?= $passData ? 'You\'re <span>Registered!</span>' : 'Register <span>Free</span> for an Event' ?></h1>
    <p><?= $passData ? 'Your digital admit card is ready.' : 'Fill in your details and get your digital admit card instantly on WhatsApp.' ?></p>
</section>

<div class="reg-wrap">
    <div>
        
        <?php if ($error_message): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 mb-6 font-bold text-center text-sm md:text-base" style="position:relative"><?= $error_message ?></div>
        <?php endif; ?>

        <?php if ($passData): ?>
            <div class="text-center mb-8">
                <div class="confirm-card confirm-card-pass">
                    <div class="confirm-icon">&#10003;</div>
                    <div class="confirm-title">Registration Confirmed!</div>
                    <div class="preview-box" id="previewBox"></div>
                </div>

                <!-- WhatsApp delivery status -->
                <?php if ($waReady): ?>
                    <div id="waStatus" class="wa-status pending">Preparing your admit card for WhatsApp <?= htmlspecialchars($waMasked, ENT_QUOTES, 'UTF-8') ?>…</div>
                <?php else: ?>
                    <div class="wa-status error">WhatsApp number looks invalid, so the card could not be sent. Please download it below.</div>
                <?php endif; ?>

                <button onclick="downloadPDF()" class="download-btn">
                    Download PDF Admit Card
                </button>
            </div>


            <?php require __DIR__ . '/../includes/admit_card_markup.php'; ?>

            <?php require __DIR__ . '/../includes/admit_card_script.php'; ?>

            <script>
            // Show the actual entry pass (scaled to fit) instead of a plain text summary
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

        <?php else: ?>
            <div class="premium-card">
                <div class="form-head">
                    <?= brand_logo_svg(52) ?>
                    <div class="form-brand-tag">AcademiaFest Education Event</div>
                    <h2>Student Event Registration</h2>
                    <p>Fields marked * are required</p>
                </div>

                <form method="POST" action="">
                    <!-- Security Token Verification -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-6">
                        <label>Academic Interest</label>
                        <div class="flex flex-wrap gap-4 md:gap-6 items-center">
                            <label class="choice"><input type="checkbox" name="interested_in[]" value="Study in India"> Study in India</label>
                            <label class="choice"><input type="checkbox" name="interested_in[]" value="Study Abroad"> Study Abroad</label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6 mb-6">
                        <div>
                            <label for="student_name">Applicant Full Name *</label>
                            <input type="text" id="student_name" name="student_name" placeholder="Full Name" required>
                        </div>
                        <div>
                            <label>Biological Gender *</label>
                            <div class="flex flex-wrap gap-4 md:gap-6 items-center min-h-[44px] md:h-[48px]">
                                <label class="choice"><input type="radio" name="gender" value="Male" required> Male</label>
                                <label class="choice"><input type="radio" name="gender" value="Female" required> Female</label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6 mb-6">
                        <div>
                            <label for="mobile_number">Primary Mobile Number *</label>
                            <input type="tel" id="mobile_number" name="mobile_number" placeholder="9876543210" required>
                        </div>
                        <div>
                            <label for="whatsapp_number">WhatsApp Number *</label>
                            <input type="tel" id="whatsapp_number" name="whatsapp_number" placeholder="9876543210" pattern="[0-9]{10}" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6 mb-6">
                        <div>
                            <label for="parent_mobile">Guardian Contact Number</label>
                            <input type="tel" id="parent_mobile" name="parent_mobile" placeholder="9876543210">
                        </div>
                        <div>
                            <label for="email_id">Electronic Mail Address *</label>
                            <input type="email" id="email_id" name="email_id" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="city">Current Residence City *</label>
                        <input type="text" id="city" name="city" placeholder="Lucknow" required>
                    </div>

                    <div class="mb-6">
                        <label>Target Degree Programs</label>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 md:gap-3 mt-2">
                            <?php foreach(COURSE_OPTIONS as $course): ?>
                                <label class="course-chip">
                                    <input type="checkbox" name="courses[]" value="<?= $course ?>"> <?= $course ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="event_id">Select Registration Event *</label>
                        <select id="event_id" name="event_id" required class="bg-white" style="box-sizing: border-box; width: 100%;">
                            <option value="">Please select an event...</option>
                            <?php foreach($events as $event): ?>
                                <option value="<?= htmlspecialchars($event['id'], ENT_QUOTES, 'UTF-8') ?>"<?= (int)$event['id'] === $preselect_event ? ' selected' : '' ?>>
                                    <?= htmlspecialchars(clean_text($event['event_name']), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-8">
                        <label for="source">How did you come to know about this event?</label>
                        <select id="source" name="source" class="bg-white" style="box-sizing: border-box; width: 100%;">
                            <option value="">Select an event to load sources...</option>
                        </select>
                    </div>

                    <button type="submit" class="submit-btn">
                        Finalize Registration & Generate Pass
                    </button>
                    <p class="text-xs text-gray-500 text-center mt-3">By registering, you agree to receive your admit card and event updates on your WhatsApp number.</p>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventSelect = document.getElementById('event_id');
    const sourceSelect = document.getElementById('source');

    if(eventSelect) {
        eventSelect.addEventListener('change', function() {
            const eventId = this.value;
            sourceSelect.innerHTML = '<option value="">Retrieving operational agents...</option>';

            if(eventId) {
                fetch('?fetch_agents=1&event_id=' + eventId)
                .then(response => response.json())
                .then(data => {
                    sourceSelect.innerHTML = '<option value="">Select acquisition source...</option>';
                    
                    if(data.length > 0) {
                        data.forEach(function(agent) {
                            const opt = document.createElement('option');
                            opt.value = agent.agent_name;
                            opt.textContent = agent.agent_name;
                            sourceSelect.appendChild(opt);
                        });
                    }
                    
                    sourceSelect.innerHTML += `
                        <option value="Social Media">Social Media Campaign</option>
                        <option value="Friends/Family">Personal Network</option>
                        <option value="Other">Unspecified Source</option>
                    `;
                })
                .catch(error => {
                    sourceSelect.innerHTML = '<option value="">Connection error. Please retry.</option>';
                });
            } else {
                sourceSelect.innerHTML = '<option value="">Select an event to load sources...</option>';
            }
        });
    }

    // If an event is pre-selected (home page link), load its agents right away
    if (eventSelect && eventSelect.value) {
        eventSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
