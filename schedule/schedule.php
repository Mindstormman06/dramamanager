<?php
session_start();
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

$isTeacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
$isStudent = isset($_SESSION['role']) && $_SESSION['role'] === 'student';
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? '';

// Fetch rehearsals
if ($isStudent) {
    // Get the student's ID from the students table using their user_id
    $stmt = $pdo->prepare("SELECT id FROM students WHERE username = ?");
    $stmt->execute([$username]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    $studentId = $student ? $student['id'] : null;

    // Students: only see rehearsals they're assigned to
    $stmt = $pdo->prepare("
        SELECT r.* FROM rehearsals r
        JOIN rehearsal_participants rp ON r.id = rp.rehearsal_id
        WHERE rp.student_id = ?
        ORDER BY r.date ASC
    ");
    $stmt->execute([$studentId]);
    $rehearsals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Teachers: see all rehearsals
    $stmt = $pdo->query("SELECT * FROM rehearsals ORDER BY date ASC");
    $rehearsals = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch students for teacher dropdown
$students = [];
if ($isTeacher) {
    $students = $pdo->query("SELECT id, first_name, last_name FROM students ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);
}

// Handle new rehearsal form submission
if ($isTeacher && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $date = $_POST['date'];
    $notes = trim($_POST['notes']);
    $participants = $_POST['participants'] ?? [];

    $stmt = $pdo->prepare("INSERT INTO rehearsals (title, date, notes, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $date, $notes, $userId]);
    $rehearsalId = $pdo->lastInsertId();

    // Insert participants
    $stmt = $pdo->prepare("INSERT INTO rehearsal_participants (rehearsal_id, student_id) VALUES (?, ?)");
    foreach ($participants as $studentId) {
        $stmt->execute([$rehearsalId, $studentId]);
    }

    // Fetch full info for selected students (first_name, last_name, discord_username)
    if (!empty($participants)) {
        $inQuery = implode(',', array_fill(0, count($participants), '?'));
        $stmt = $pdo->prepare("SELECT first_name, last_name, discord_username FROM students WHERE id IN ($inQuery)");
        $stmt->execute($participants);
        $studentInfos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $studentInfos = [];
    }

    $botPayload = [
        'title' => $title,
        'date' => date('M d, Y H:i', strtotime($date)),
        'notes' => $notes,
        'students' => $studentInfos
    ];

    $ch = curl_init('http://10.0.0.47:3079/rehearsal');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($botPayload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    header("Location: schedule.php");
    exit;
}

// Handle Discord username update for students
if ($isStudent && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['discord_username'])) {
    $discordUsername = trim($_POST['discord_username']);
    // Get student ID
    $stmt = $pdo->prepare("SELECT id FROM students WHERE username = ?");
    $stmt->execute([$username]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($student) {
        $stmt = $pdo->prepare("UPDATE students SET discord_username = ? WHERE id = ?");
        $stmt->execute([$discordUsername, $student['id']]);
    }
}

// Handle rehearsal deletion (teachers only)
if ($isTeacher && isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];

    // Fetch rehearsal info and participants before deleting
    $stmt = $pdo->prepare("SELECT * FROM rehearsals WHERE id = ?");
    $stmt->execute([$deleteId]);
    $rehearsal = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT s.first_name, s.last_name, s.discord_username
        FROM rehearsal_participants rp
        JOIN students s ON rp.student_id = s.id
        WHERE rp.rehearsal_id = ?
    ");
    $stmt->execute([$deleteId]);
    $studentInfos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Send deletion info to the bot
    if ($rehearsal) {
        $botPayload = [
            'title' => $rehearsal['title'],
            'date' => date('M d, Y H:i', strtotime($rehearsal['date'])),
            'notes' => $rehearsal['notes'],
            'students' => $studentInfos,
        ];

        $ch = curl_init('http://10.0.0.47:3079/rehearsalcancel');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($botPayload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    // Now delete the rehearsal
    $stmt = $pdo->prepare("DELETE FROM rehearsals WHERE id = ?");
    $stmt->execute([$deleteId]);
    header("Location: schedule.php");
    exit;
}

// Fetch current Discord username for student
$currentDiscordUsername = '';
if ($isStudent) {
    $stmt = $pdo->prepare("SELECT discord_username FROM students WHERE username = ?");
    $stmt->execute([$username]);
    $currentDiscordUsername = $stmt->fetchColumn() ?: '';
}
?>

<main class="flex-1 w-full max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold text-[#7B1E3B] mb-6">📅 Rehearsal Schedule</h1>

    <?php if ($isStudent): ?>
    <!-- Settings Button -->
    <div class="flex justify-end mb-6">
        <button onclick="openSettings()" class="bg-[#7B1E3B] text-white px-4 py-2 rounded hover:bg-[#9B3454] transition">
            ⚙️ Settings
        </button>
    </div>
    <?php endif; ?>

    <?php if ($isTeacher): ?>
    <!-- Add New Rehearsal Form -->
    <div class="bg-white rounded-xl shadow p-6 border border-gray-200 mb-8">
        <h2 class="text-xl font-semibold mb-4">Add New Rehearsal</h2>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block font-medium mb-1">Title</label>
                <input type="text" name="title" required class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block font-medium mb-1">Date & Time</label>
                <input type="datetime-local" name="date" required class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block font-medium mb-1">Notes</label>
                <textarea name="notes" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div>
                <label class="block font-medium mb-1">Participants</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <?php foreach ($students as $student): ?>
                        <label class="flex items-center gap-2 bg-gray-50 rounded px-2 py-1 cursor-pointer">
                            <input type="checkbox" name="participants[]" value="<?= $student['id'] ?>" class="accent-[#7B1E3B]">
                            <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-gray-500 mt-1">Tap to select one or more students.</p>
            </div>
            <button type="submit" class="bg-[#7B1E3B] text-white px-4 py-2 rounded hover:bg-[#9B3454]">Add Rehearsal</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Rehearsal List -->
    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h2 class="text-xl font-semibold mb-4">Upcoming Rehearsals</h2>
        <?php if (empty($rehearsals)): ?>
            <p class="text-gray-600">No rehearsals scheduled.</p>
        <?php else: ?>
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th class="py-2">Title</th>
                        <th class="py-2">Date & Time</th>
                        <th class="py-2">Notes</th>
                        <?php if ($isTeacher): ?>
                        <th class="py-2">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rehearsals as $r): ?>
                        <tr class="border-t">
                            <td class="py-2"><?= htmlspecialchars($r['title']) ?></td>
                            <td class="py-2"><?= date('M d, Y H:i', strtotime($r['date'])) ?></td>
                            <td class="py-2"><?= nl2br(htmlspecialchars($r['notes'])) ?></td>
                            <?php if ($isTeacher): ?>
                            <td class="py-2">
                                <a href="?delete=<?= $r['id'] ?>" class="text-red-600 hover:underline"
                                   onclick="return confirm('Are you sure you want to delete this rehearsal?');">
                                    Delete
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<!-- Settings Modal for Students -->
<?php if ($isStudent): ?>
<div id="settings-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md relative">
        <button onclick="closeSettings()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
        <h2 class="text-xl font-semibold mb-4">Settings</h2>
        <form method="POST" class="space-y-4">
            <div>
                <label for="discord_username" class="block font-medium mb-1">Discord Username (NOT Display Name)</label>
                <input type="text" name="discord_username" id="discord_username"
                    value="<?= htmlspecialchars($currentDiscordUsername) ?>"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]" maxlength="37" />
            </div>
            <button type="submit" class="bg-[#7B1E3B] text-white px-4 py-2 rounded hover:bg-[#9B3454]">Save</button>
        </form>
    </div>
</div>
<script>
function openSettings() {
    document.getElementById('settings-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}
function closeSettings() {
    document.getElementById('settings-modal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}
// Optional: close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === "Escape") closeSettings();
});
</script>
<?php endif; ?>

<?php include '../footer.php'; ?>