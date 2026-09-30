<?php
// High Security Headers for Core PHP
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

require_once __DIR__ . '/auth.php';

$msg = '';
$msgType = '';

// Handle Secure Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        // Strictly validate ID as an integer
        $delete_id = filter_var($_POST['event_id'], FILTER_VALIDATE_INT);
        if (!$delete_id) {
            throw new Exception("Security Alert: Invalid Event ID.");
        }

        // 1. Remember the image paths (files are removed after the database delete succeeds)
        $fileStmt = $pdo->prepare("SELECT logo_path, photo_path FROM events WHERE id = :id LIMIT 1");
        $fileStmt->execute([':id' => $delete_id]);
        $event = $fileStmt->fetch(PDO::FETCH_ASSOC);
        if (!$event) {
            throw new Exception("Event not found.");
        }

        // 2. Delete in a safe order (works even if the old database has foreign keys):
        //    registrations are kept and simply unlinked from the event (shown as "Unknown"),
        //    then the event's agents are removed, then the event itself.
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE student_registrations SET event_id = NULL WHERE event_id = :id")->execute([':id' => $delete_id]);
        $pdo->prepare("DELETE FROM agents WHERE event_id = :id")->execute([':id' => $delete_id]);
        $pdo->prepare("DELETE FROM events WHERE id = :id")->execute([':id' => $delete_id]);
        $pdo->commit();

        // 3. Remove the image files from the server
        delete_public_file($event['logo_path'] ?? null);
        delete_public_file($event['photo_path'] ?? null);

        $msg = "Event and associated files securely deleted.";
        $msgType = "success";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $msg = "Deletion Error: " . $e->getMessage();
        $msgType = "error";
    }
}

// Fetch all events securely
try {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY created_at DESC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $msg = "Database Error: Unable to fetch events.";
    $msgType = "error";
    $events = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - <?= htmlspecialchars(BRAND_NAME, ENT_QUOTES, 'UTF-8') ?> Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { font-family: 'Inter', system-ui, sans-serif; }
        
        /* Premium Card */
        .premium-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px -4px rgba(26, 42, 74, 0.05);
            border: 1px solid rgba(26, 42, 74, 0.04);
        }

        /* Animations */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-up { animation: fadeSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        .delay-100 { animation-delay: 100ms; }
    </style>
</head>
<body class="bg-[#f8f9fa] p-4 md:p-8">
    
    <div class="max-w-[85rem] mx-auto animate-up">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <a href="admin_dashboard.php" class="text-sm font-bold text-[#2d4a8a] hover:underline flex items-center gap-1">
                    &larr; Back to Dashboard
                </a>
                <h2 class="text-3xl font-extrabold text-[#1a2a4a] mt-2 tracking-tight">Manage Events</h2>
            </div>
            <a href="add_event.php" class="bg-[#1a2a4a] hover:bg-[#0f1f3d] text-white px-6 py-3 rounded-xl font-extrabold text-sm shadow-md transition-transform transform hover:scale-105 uppercase tracking-wide">
                + Create New Event
            </a>
        </div>

        <?php if($msg): ?>
            <div class="p-4 mb-6 rounded-xl text-sm font-bold <?= $msgType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="premium-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-[#1a2a4a] uppercase text-[11px] font-extrabold tracking-wider border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Logo</th>
                            <th class="px-6 py-4">Event Name</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Date Created</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 font-medium">No events found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-400">#<?= htmlspecialchars($event['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                    
                                    <td class="px-6 py-4">
                                        <?php if (!empty($event['photo_path']) || !empty($event['logo_path'])): ?>
                                            <img src="<?= htmlspecialchars($event['photo_path'] ?: $event['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="h-10 w-10 rounded-lg object-cover border border-gray-100 bg-gray-50">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center text-[10px] text-gray-400 font-bold uppercase">N/A</div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="px-6 py-4 font-extrabold text-[#1a2a4a]"><?= htmlspecialchars(clean_text($event['event_name']), ENT_QUOTES, 'UTF-8') ?></td>
                                    
                                    <td class="px-6 py-4">
                                        <?php if ($event['is_active']): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-green-100 text-green-800 uppercase tracking-widest border border-green-200">Active</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-gray-100 text-gray-800 uppercase tracking-widest border border-gray-200">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-gray-500 text-xs font-medium">
                                        <?= date('d M Y', strtotime($event['created_at'])) ?>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-right space-x-3">
                                        <a href="edit_event.php?id=<?= htmlspecialchars($event['id'], ENT_QUOTES, 'UTF-8') ?>" class="inline-block px-4 py-2 bg-white text-[#1a2a4a] border border-gray-200 hover:border-[#2d4a8a] hover:text-[#2d4a8a] rounded-lg text-[11px] font-bold transition-all uppercase tracking-wide">
                                            Edit
                                        </a>
                                        
                                        <form method="POST" class="inline-block" onsubmit="return confirm('Delete this event? Its agents will be removed. Student registrations are kept and will show the event as Unknown.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-[11px] font-bold transition-all border border-red-100 uppercase tracking-wide">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>