<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: shows.php");
    exit;
}

// Fetch the current show
$stmt = $pdo->prepare("SELECT * FROM shows WHERE id = ?");
$stmt->execute([$id]);
$show = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$show) {
    die("Show not found.");
}

$title = $show['title'];
$year = $show['year'];
$semester = $show['semester'];
$notes = $show['notes'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $year = trim($_POST['year']);
    $semester = trim($_POST['semester']);
    $notes = trim($_POST['notes']);

    if (empty($title)) {
        $errors[] = 'Title is required.';
    }

    // Handle script upload
    $script_path = $show['script_path']; // Keep the current script path by default
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

                // Optionally delete the old script file if it exists (BROKEN)
                // if (!empty($show['script_path']) && file_exists(__DIR__ . '/../' . $show['script_path'])) {
                //     unlink(__DIR__ . '/../' . $show['script_path']);
                // }
            } else {
                $errors[] = 'Failed to upload the script.';
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE shows SET title = ?, year = ?, semester = ?, notes = ?, script_path = ? WHERE id = ?");
        $stmt->execute([$title, $year ?: null, $semester ?: null, $notes, $script_path, $id]);
        header("Location: shows.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Show | QSS Drama</title>
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
      <h2 class="text-2xl font-semibold mb-6">Edit Show</h2>
      <a href="shows.php" class="text-blue-600 hover:underline mb-4">← Back to Show List</a>
      <div>
        <label class="block font-medium mb-1" for="title">Show Title *</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]" />
      </div>

      <div>
        <label class="block font-medium mb-1" for="year">Year</label>
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
        <label class="block font-medium mb-1" for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="4"
                  class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]"><?= htmlspecialchars($notes) ?></textarea>
      </div>

      <div>
        <label class="block font-medium mb-1" for="script">Upload Script (PDF only)</label>
        <input type="file" name="script" id="script"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]" />
      </div>

      <div class="flex justify-end">
        <button type="submit" class="bg-[#7B1E3B] text-white px-6 py-2 rounded hover:bg-[#9B3454] transition">
          Update Show
        </button>
      </div>
    </form>
  </main>
  <?php include '../footer.php'; ?>
</body>
</html>
