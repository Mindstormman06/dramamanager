<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /characters/");
    exit;
}

// Fetch character
$stmt = $pdo->prepare("SELECT * FROM characters WHERE id = ?");
$stmt->execute([$id]);
$char = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$char) {
    die("Character not found.");
}

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

// Fetch the linked student (if any)
$linkedStudentStmt = $pdo->prepare("
    SELECT sc.student_id, s.first_name, s.last_name 
    FROM studentcharacters sc
    JOIN students s ON sc.student_id = s.id
    WHERE sc.character_id = ?
");
$linkedStudentStmt->execute([$id]);
$linkedStudent = $linkedStudentStmt->fetch(PDO::FETCH_ASSOC);

$stage_name = $char['stage_name'];
$real_name = $char['real_name'];
$show_id = $char['show_id'];
$linked_student_id = $linkedStudent['student_id'] ?? null;

// Fetch the show title based on the show_id
$showStmt = $pdo->prepare("SELECT title FROM shows WHERE id = ?");
$showStmt->execute([$show_id]);
$show = $showStmt->fetch(PDO::FETCH_ASSOC);

if (!$show) {
    die("Associated show not found.");
}

$show_title = $show['title'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stage_name = trim($_POST['stage_name']);
    $real_name = trim($_POST['real_name']);
    $student_id = $_POST['student_id'] ?? null;

    if (empty($stage_name)) {
        $errors[] = "Stage name is required.";
    }

    if (empty($errors)) {
        // Update the character in the characters table
        $stmt = $pdo->prepare("UPDATE characters SET stage_name = ?, real_name = ? WHERE id = ?");
        $stmt->execute([$stage_name, $real_name ?: null, $id]);

        // Update the student link
        if ($student_id && $student_id !== 'manual') {
            // Fetch the student's full name
            $studentStmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE id = ?");
            $studentStmt->execute([$student_id]);
            $student = $studentStmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $real_name = $student['first_name'] . ' ' . $student['last_name'];

                // Update the real_name in the characters table
                $stmt = $pdo->prepare("UPDATE characters SET real_name = ? WHERE id = ?");
                $stmt->execute([$real_name, $id]);

                // Check if the link already exists in the studentcharacters table
                $linkCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM studentcharacters WHERE character_id = ? AND student_id = ?");
                $linkCheckStmt->execute([$id, $student_id]);
                $linkExists = $linkCheckStmt->fetchColumn() > 0;

                if (!$linkExists) {
                    // Insert the new link if it doesn't exist
                    $stmt = $pdo->prepare("INSERT INTO studentcharacters (character_id, student_id) VALUES (?, ?)");
                    $stmt->execute([$id, $student_id]);
                }
            }
        } else {
            // If manual input, remove any existing link in the studentcharacters table
            $stmt = $pdo->prepare("DELETE FROM studentcharacters WHERE character_id = ?");
            $stmt->execute([$id]);
        }

        // Redirect back to the characters page for the selected show
        header("Location: /characters/?show_id=$show_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Character | QSS Drama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <h1 class="text-3xl font-bold text-[#7B1E3B] mb-6">Edit Character</h1>
    <a href="/characters/?show_id=<?= $show_id ?>" class="text-blue-600 hover:underline mb-4">← Back to Character List</a>

    <?php if ($errors): ?>
      <div class="bg-red-100 text-red-700 border border-red-300 p-4 rounded mb-6">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-6 rounded-lg shadow border space-y-4">
      <div>
        <label for="stage_name" class="block font-medium mb-1">Stage Name *</label>
        <input type="text" name="stage_name" id="stage_name" required value="<?= htmlspecialchars($stage_name) ?>"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
      </div>

      <div>
        <label for="student_id" class="block font-medium mb-1">Linked Student</label>
        <select name="student_id" id="student_id" class="w-full border rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]" onchange="toggleManualInput()">
          <option value="">-- Select a Student --</option>
          <?php foreach ($students as $student): ?>
            <option value="<?= $student['id'] ?>" <?= $linked_student_id == $student['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
            </option>
          <?php endforeach; ?>
          <option value="manual">Other (Enter Manually)</option>
        </select>
      </div>

      <div id="manual-input" class="<?= $linked_student_id ? 'hidden' : '' ?>">
        <label for="real_name" class="block font-medium mb-1">Real Name</label>
        <input type="text" name="real_name" id="real_name" value="<?= htmlspecialchars($real_name) ?>"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
      </div>

      <div>
        <p class="text-sm text-gray-600">Associated Show: <strong><?= htmlspecialchars($show_title) ?></strong></p>
      </div>

      <div class="flex justify-end">
        <button type="submit" class="bg-[#7B1E3B] text-white px-6 py-2 rounded hover:bg-[#9B3454] transition">
          Save Changes
        </button>
      </div>
    </form>
  </main>
  <?php include '../footer.php'; ?>

  <script>
    function toggleManualInput() {
      const studentSelect = document.getElementById('student_id');
      const manualInput = document.getElementById('manual-input');
      if (studentSelect.value === 'manual') {
        manualInput.classList.remove('hidden');
      } else {
        manualInput.classList.add('hidden');
        document.getElementById('real_name').value = ''; // Clear manual input
      }
    }
  </script>
</body>
</html>
