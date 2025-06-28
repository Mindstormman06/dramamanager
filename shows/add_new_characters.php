<?php
session_start();

if (!isset($_SESSION['new_characters']) || !isset($_SESSION['new_character_counts']) || !isset($_SESSION['show_id'])) {
    header("Location: shows.php");
    exit;
}

$newCharacters = $_SESSION['new_characters'];
$newCharacterCounts = $_SESSION['new_character_counts'];
$show_id = $_SESSION['show_id'];

require_once __DIR__ . '/../backend/db.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only process characters that were selected
    if (!empty($_POST['characters'])) {
        foreach ($_POST['characters'] as $character) {
            $realName = $_POST['real_name'][$character] ?? null;
            $studentId = $_POST['student_id'][$character] ?? null;
            $counts = $newCharacterCounts[$character] ?? ['mentions' => 0, 'lines' => 0]; // Default to 0 if counts are missing

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
            $stmt->execute([$character, $realName ?: null, $show_id, $counts['mentions'], $counts['lines']]);
        }
    }

    // Clear session data
    unset($_SESSION['new_characters']);
    unset($_SESSION['new_character_counts']);
    unset($_SESSION['show_id']);

    header("Location: shows.php?success=new_characters_added");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Characters</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
<main class="max-w-6xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-6 text-[#7B1E3B]">🎭 Add New Characters</h1>
  <form method="POST" id="character-form" class="space-y-6">
    <div class="overflow-x-auto">
      <table class="w-full bg-white border rounded shadow text-sm">
        <thead class="bg-[#7B1E3B] text-white">
          <tr>
            <th class="p-2 text-left">Add</th>
            <th class="p-2 text-left">Stage Name</th>
            <th class="p-2 text-left">Real Name</th>
            <th class="p-2 text-left">Student</th>
            <th class="p-2 text-center">Mentions</th>
            <th class="p-2 text-center">Lines</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($newCharacters as $character): ?>
            <tr class="border-t border-gray-200">
              <td class="p-2 text-center">
                <input type="checkbox" name="characters[]" value="<?= htmlspecialchars($character) ?>" id="character-<?= htmlspecialchars($character) ?>" checked>
              </td>
              <td class="p-2 font-medium"><?= htmlspecialchars($character) ?></td>
              <td class="p-2">
                <select name="student_id[<?= htmlspecialchars($character) ?>]" class="w-full border rounded px-2 py-1" onchange="toggleManualInput(this)">
                  <option value="">-- Select a Student --</option>
                  <?php foreach ($students as $student): ?>
                    <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></option>
                  <?php endforeach; ?>
                  <option value="manual">Other (Enter Manually)</option>
                </select>
                <input type="text" name="real_name[<?= htmlspecialchars($character) ?>]" class="w-full border rounded px-2 py-1 mt-2 hidden" placeholder="Enter Real Name" />
              </td>
              <td class="p-2 text-center"><?= $newCharacterCounts[$character]['mentions'] ?? 0 ?></td>
              <td class="p-2 text-center"><?= $newCharacterCounts[$character]['lines'] ?? 0 ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="flex gap-4 mt-6">
      <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" onclick="ignoreAll()">Ignore All</button>
      <button type="submit" class="bg-[#7B1E3B] text-white px-4 py-2 rounded hover:bg-[#9B3454]">Add Selected</button>
      <button type="button" class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-600" onclick="addAll()">Add All</button>
    </div>
  </form>
</main>
<?php include '../footer.php'; ?>
<script>
  function ignoreAll() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
  }

  function addAll() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
  }

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