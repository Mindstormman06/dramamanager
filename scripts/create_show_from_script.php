<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../backend/db.php';
include '../header.php';

$loggedInUsername = $_SESSION['username'];

// Fetch the logged-in teacher's ID
$stmt = $pdo->prepare("SELECT id FROM teachers WHERE username = ?");
$stmt->execute([$loggedInUsername]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    die('You are not registered as a teacher.');
}

$teacherId = $teacher['id']; // The actual teacher ID

// Fetch the lead teacher for the logged-in teacher (if they are a linked teacher)
$leadTeacherStmt = $pdo->prepare("
    SELECT lead_teacher_id 
    FROM teacher_links 
    WHERE linked_teacher_id = ?
");
$leadTeacherStmt->execute([$teacherId]);
$leadTeacherId = $leadTeacherStmt->fetchColumn();

// Include the lead teacher's ID (if any) in the list of teacher IDs
$allTeacherIds = [$teacherId];
if ($leadTeacherId) {
    $allTeacherIds[] = $leadTeacherId;
}

// Fetch teachers linked to the logged-in teacher (if they are a lead teacher)
$linkedTeachersStmt = $pdo->prepare("
    SELECT linked_teacher_id 
    FROM teacher_links 
    WHERE lead_teacher_id = ?
");
$linkedTeachersStmt->execute([$teacherId]);
$linkedTeacherIds = $linkedTeachersStmt->fetchAll(PDO::FETCH_COLUMN);

// Merge all relevant teacher IDs (logged-in teacher, lead teacher, and linked teachers)
$allTeacherIds = array_merge($allTeacherIds, $linkedTeacherIds);

// Fetch students linked to all relevant teachers
$placeholders = implode(',', array_fill(0, count($allTeacherIds), '?'));
$studentStmt = $pdo->prepare("
    SELECT s.id, s.first_name, s.last_name 
    FROM students s
    WHERE s.teacher_id IN ($placeholders)
");
$studentStmt->execute($allTeacherIds);
$students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

// Check if script analysis data exists
if (!isset($_SESSION['script_analysis']) || !isset($_SESSION['character_list'])) {
    die("No script data found. Please analyze a script first.");
}

$characters = $_SESSION['script_analysis'];
$characterList = explode(',', $_SESSION['character_list']);

$title = '';
$year = '';
$semester = '';
$notes = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $year = trim($_POST['year']);
    $semester = trim($_POST['semester']);
    $notes = trim($_POST['notes']);
    $real_names = $_POST['real_name'] ?? [];
    $student_ids = $_POST['student_id'] ?? [];
    $errors = [];

    if (empty($title)) {
        $errors[] = "Show title is required.";
    }

    if (empty($errors)) {
        // Handle script upload
        $script_path = $_SESSION['uploaded_script_path'] ?? null;

        // Insert show
        $stmt = $pdo->prepare("INSERT INTO shows (title, year, semester, notes, script_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $year ?: null, $semester ?: null, $notes, $script_path]);
        $show_id = $pdo->lastInsertId();

        // Insert characters
        foreach ($characters as $stageName => $counts) {
            // Convert stage name to "Title Case" (first letter of each word capitalized)
            $formattedStageName = ucwords(strtolower($stageName));

            $studentId = $student_ids[$stageName] ?? null;
            $realName = $real_names[$stageName] ?? null;

            // If a student is selected, fetch their full name
            if ($studentId && $studentId !== 'manual') {
                $studentStmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE id = ?");
                $studentStmt->execute([$studentId]);
                $student = $studentStmt->fetch(PDO::FETCH_ASSOC);

                if ($student) {
                    $realName = $student['first_name'] . ' ' . $student['last_name'];
                }
            }

            $stmt = $pdo->prepare("
                INSERT INTO characters (stage_name, real_name, show_id, mention_count, line_count)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $formattedStageName,
                $realName ?: null,
                $show_id,
                $counts['mentions'],
                $counts['lines']
            ]);

            if (isset($realName)) {
              $logRealName = "($realName)";
            }

            log_event("Character '$formattedStageName' $logRealName added to show $title (ID: $show_id) by user '{$_SESSION['username']}'", 'INFO');
        }

        // Clear session data
        unset($_SESSION['script_analysis']);
        unset($_SESSION['character_list']);
        unset($_SESSION['uploaded_script_path']);

        log_event("Show '$title' (ID: $show_id) added by user '{$_SESSION['username']}'", 'INFO');


        header("Location: /shows/");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Show from Script | QSS Drama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <h1 class="text-3xl font-bold text-[#7B1E3B] mb-6">🎭 Create Show from Script</h1>

    <?php if ($errors): ?>
      <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded mb-6">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-6 rounded-lg shadow border space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="title" class="block font-medium mb-1">Show Title *</label>
          <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required
                 class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#7B1E3B]" />
        </div>

        <div>
          <label for="year" class="block font-medium mb-1">Year</label>
          <input type="number" name="year" id="year" value="<?= htmlspecialchars($year) ?>"
                 class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#7B1E3B]" />
        </div>

        <div>
          <label for="semester" class="block font-medium mb-1">Semester</label>
          <select name="semester" id="semester" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#7B1E3B]">
            <option value="" <?= $semester === '' ? 'selected' : '' ?>>Select Semester</option>
            <option value="1" <?= $semester === '1' ? 'selected' : '' ?>>1</option>
            <option value="2" <?= $semester === '2' ? 'selected' : '' ?>>2</option>
          </select>
        </div>

        <div class="sm:col-span-2">
          <label for="notes" class="block font-medium mb-1">Notes</label>
          <textarea name="notes" id="notes" rows="3"
                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#7B1E3B]"><?= htmlspecialchars($notes) ?></textarea>
        </div>
      </div>

      <h2 class="text-xl font-semibold mt-8 mb-2">🧍 Characters</h2>
      <div class="overflow-x-auto">
        <table class="w-full bg-white border rounded shadow text-sm">
          <thead class="bg-[#7B1E3B] text-white">
            <tr>
              <th class="p-2 text-left">Stage Name</th>
              <th class="p-2 text-left">Real Name</th>
              <th class="p-2 text-center">Mentions</th>
              <th class="p-2 text-center">Lines</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($characters as $stage => $counts): ?>
              <tr class="border-t border-gray-200">
                <td class="p-2 font-medium"><?= htmlspecialchars(ucwords(strtolower($stage))) ?></td>
                <td class="p-2">
                  <select name="student_id[<?= htmlspecialchars($stage) ?>]" class="w-full border rounded px-2 py-1" onchange="toggleManualInput(this)">
                    <option value="">-- Select a Student --</option>
                    <?php foreach ($students as $student): ?>
                      <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></option>
                    <?php endforeach; ?>
                    <option value="manual">Other (Enter Manually)</option>
                  </select>
                  <input type="text" name="real_name[<?= htmlspecialchars($stage) ?>]" class="w-full border rounded px-2 py-1 mt-2 hidden" placeholder="Enter Real Name" />
                </td>
                <td class="p-2 text-center"><?= $counts['mentions'] ?></td>
                <td class="p-2 text-center"><?= $counts['lines'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="flex justify-end pt-4">
        <button type="submit" class="bg-[#7B1E3B] text-white px-6 py-2 rounded hover:bg-[#9B3454] transition">
          Save Show & Characters
        </button>
      </div>
    </form>
  </main>
  <?php include '../footer.php'; ?>

  <script>
    function toggleManualInput(selectElement) {
      const manualInput = selectElement.nextElementSibling;
      if (selectElement.value === 'manual') {
        manualInput.classList.remove('hidden');
      } else {
        manualInput.classList.add('hidden');
        manualInput.value = ''; // Clear manual input
      }
    }
  </script>
</body>
</html>
