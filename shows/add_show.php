<?php
require_once __DIR__ . '/../backend/db.php';

$title = '';
$year = '';
$semester = '';
$notes = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $year = trim($_POST['year']);
    $notes = trim($_POST['notes']);
    $semester = trim($_POST['semester']);

    // Basic validation
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (!empty($year) && !is_numeric($year)) {
        $errors[] = 'Year must be a number.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO shows (title, year, semester, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $year ?: null, $semester ?: null, $notes]);  
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
<body class="bg-gray-50 text-gray-800">

  <header class="bg-purple-800 text-white py-6 mb-8 shadow-md">
    <div class="max-w-4xl mx-auto px-4">
      <h1 class="text-3xl font-bold">➕ Add Show</h1>
      <a href="shows.php" class="text-sm underline hover:text-purple-300">← Back to Shows</a>
    </div>
  </header>

  <main class="max-w-4xl mx-auto px-4">
    <?php if ($errors): ?>
      <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded mb-6">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-6 rounded-lg shadow border border-purple-200 space-y-4">
      <div>
        <label class="block font-medium mb-1" for="title">Show Title *</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600" />
      </div>

      <div>
        <label class="block font-medium mb-1" for="year">Year (optional)</label>
        <input type="number" name="year" id="year" value="<?= htmlspecialchars($year) ?>"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600" />
      </div>

      <div>
        <label class="block font-medium mb-1" for="semester">Semester</label>
        <select name="semester" id="semester" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600">
          <option value="" <?= $semester === '' ? 'selected' : '' ?>>Select Semester</option>
          <option value="1" <?= $semester === '1' ? 'selected' : '' ?>>1</option>
          <option value="2" <?= $semester === '2' ? 'selected' : '' ?>>2</option>
        </select>
      </div>

      <div>
        <label class="block font-medium mb-1" for="notes">Notes (optional)</label>
        <textarea name="notes" id="notes" rows="4"
                  class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600"><?= htmlspecialchars($notes) ?></textarea>
      </div>

      <div class="flex justify-end">
        <button type="submit" class="bg-purple-700 text-white px-6 py-2 rounded hover:bg-purple-600 transition">
          Save Show
        </button>
      </div>
    </form>
  </main>

</body>
</html>
