<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../backend/db.php';

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

    if (empty($title)) {
        $errors[] = "Show title is required.";
    }

    if (empty($errors)) {
        // Insert show
        $stmt = $pdo->prepare("INSERT INTO shows (title, year, semester, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $year ?: null, $semester ?: null, $notes]);
        $show_id = $pdo->lastInsertId();

        // Insert characters
        foreach ($characters as $stageName => $counts) {
            $realName = $real_names[$stageName] ?? null;

            $stmt = $pdo->prepare("
                INSERT INTO characters (stage_name, real_name, show_id, mention_count, line_count)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $stageName,
                $realName ?: null,
                $show_id,
                $counts['mentions'],
                $counts['lines']
            ]);

        }

        // Clear session data
        unset($_SESSION['script_analysis']);
        unset($_SESSION['character_list']);

        header("Location: ../shows/shows.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Show from Script | QSS Drama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-5xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-6 text-purple-800">🎭 Create Show from Script</h1>

    <?php if ($errors): ?>
      <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded mb-6">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="title" class="block font-medium mb-1">Show Title *</label>
          <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required
                 class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-purple-600" />
        </div>

        <div>
          <label for="year" class="block font-medium mb-1">Year</label>
          <input type="number" name="year" id="year" value="<?= htmlspecialchars($year) ?>"
                 class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-purple-600" />
        </div>

        <div>
            <label class="block font-medium mb-1" for="semester">Semester</label>
            <select name="semester" id="semester" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600">
            <option value="" <?= $semester === '' ? 'selected' : '' ?>>Select Semester</option>
            <option value="1" <?= $semester === '1' ? 'selected' : '' ?>>1</option>
            <option value="2" <?= $semester === '2' ? 'selected' : '' ?>>2</option>
            </select>
        </div>

        <div class="sm:col-span-2">
          <label for="notes" class="block font-medium mb-1">Notes</label>
          <textarea name="notes" id="notes" rows="3"
                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-purple-600"><?= htmlspecialchars($notes) ?></textarea>
        </div>
      </div>

      <h2 class="text-xl font-semibold mt-8 mb-2">🧍 Characters</h2>
      <div class="overflow-x-auto">
        <table class="w-full bg-white border border-purple-300 rounded shadow text-sm">
          <thead class="bg-purple-700 text-white">
            <tr>
              <th class="p-2 text-left">Stage Name</th>
              <th class="p-2 text-left">Real Name</th>
              <th class="p-2 text-left">Mentions</th>
              <th class="p-2 text-left">Lines</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($characters as $stage => $counts): ?>
              <tr class="border-t border-gray-200">
                <td class="p-2 font-medium"><?= htmlspecialchars($stage) ?></td>
                <td class="p-2">
                  <input type="text" name="real_name[<?= htmlspecialchars($stage) ?>]"
                         class="w-full border rounded px-2 py-1" />
                </td>
                <td class="p-2 text-center"><?= $counts['mentions'] ?></td>
                <td class="p-2 text-center"><?= $counts['lines'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="flex justify-end pt-4">
        <button type="submit" class="bg-purple-700 text-white px-6 py-2 rounded hover:bg-purple-600 transition">
          Save Show & Characters
        </button>
      </div>
    </form>
  </main>
</body>
</html>
