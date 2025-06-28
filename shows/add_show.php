<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

$title = '';
$year = '';
$semester = '';
$notes = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $year = trim($_POST['year']);
    $semester = trim($_POST['semester']);
    $notes = trim($_POST['notes']);
    $errors = [];

    // Validate required fields
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (!empty($year) && !is_numeric($year)) {
        $errors[] = 'Year must be a number.';
    }

    // Handle script upload
    $script_path = null;
    if (!empty($_FILES['script']['tmp_name']) && is_uploaded_file($_FILES['script']['tmp_name'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['script']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $errors[] = 'Only PDF files are allowed for the script.';
        } else {
            $filename = uniqid('script_', true) . '.' . $ext;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['script']['tmp_name'], $targetPath)) {
                $script_path = '../uploads/' . $filename;
            } else {
                $errors[] = 'Failed to upload the script.';
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO shows (title, year, semester, notes, script_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $year ?: null, $semester ?: null, $notes, $script_path]);
        header("Location: shows.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Show | QSS Drama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <?php if ($errors): ?>
      <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded mb-6">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow border space-y-4">
      <h2 class="text-2xl font-semibold mb-6">Add New Show</h2>
      <a href="shows.php" class="text-blue-600 hover:underline mb-4">← Back to Show List</a>
      <div>
        <label class="block font-medium mb-1" for="title">Show Title *</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]" />
      </div>

      <div>
        <label class="block font-medium mb-1" for="year">Year (optional)</label>
        <input type="number" name="year" id="year" value="<?= htmlspecialchars($year) ?>"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]" />
      </div>

      <div>
        <label class="block font-medium mb-1" for="semester">Semester</label>
        <select name="semester" id="semester" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
          <option value="" <?= $semester === '' ? 'selected' : '' ?>>Select Semester</option>
          <option value="1" <?= $semester === '1' ? 'selected' : '' ?>>1</option>
          <option value="2" <?= $semester === '2' ? 'selected' : '' ?>>2</option>
        </select>
      </div>

      <div>
        <label class="block font-medium mb-1" for="notes">Notes (optional)</label>
        <textarea name="notes" id="notes" rows="4"
                  class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]"><?= htmlspecialchars($notes) ?></textarea>
      </div>

      <div>
        <label class="block font-medium mb-1" for="script">Upload Script (PDF)</label>
        <input type="file" name="script" id="script" accept="application/pdf"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]" />
      </div>

      <div class="flex justify-end">
        <button type="submit" class="bg-[#7B1E3B] text-white px-6 py-2 rounded hover:bg-[#9B3454] transition">
          Save Show
        </button>
      </div>
    </form>
  </main>
  <?php include '../footer.php'; ?>
</body>
</html>
