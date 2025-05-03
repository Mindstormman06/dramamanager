<?php
require_once __DIR__ . '/../backend/db.php';

// Fetch shows for dropdown
$showStmt = $pdo->query("SELECT id, title, year, semester FROM shows ORDER BY year DESC, semester DESC, title ASC");
$shows = $showStmt->fetchAll(PDO::FETCH_ASSOC);

$stage_name = '';
$real_name = '';
$show_id = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stage_name = trim($_POST['stage_name']);
    $real_name = trim($_POST['real_name']);
    $show_id = $_POST['show_id'];

    if (empty($stage_name)) {
        $errors[] = "Stage name is required.";
    }

    if (empty($show_id)) {
        $errors[] = "Please select a show.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO characters (stage_name, real_name, show_id) VALUES (?, ?, ?)");
        $stmt->execute([$stage_name, $real_name ?: null, $show_id]);
        header("Location: characters.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Character | QSS Drama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<header class="bg-purple-800 text-white py-6 mb-8 shadow-md">
  <div class="max-w-4xl mx-auto px-4">
    <h1 class="text-3xl font-bold">➕ Add Character</h1>
    <a href="characters.php" class="text-sm underline hover:text-purple-300">← Back to Characters</a>
  </div>
</header>

<main class="max-w-4xl mx-auto px-4">
  <?php if ($errors): ?>
    <div class="bg-red-100 text-red-700 border border-red-300 p-4 rounded mb-6">
      <ul class="list-disc list-inside">
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" class="bg-white p-6 rounded-lg shadow border border-purple-200 space-y-4">
    <div>
      <label for="stage_name" class="block font-medium mb-1">Stage Name *</label>
      <input type="text" name="stage_name" id="stage_name" required value="<?= htmlspecialchars($stage_name) ?>"
             class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600" />
    </div>

    <div>
      <label for="real_name" class="block font-medium mb-1">Real Name (optional)</label>
      <input type="text" name="real_name" id="real_name" value="<?= htmlspecialchars($real_name) ?>"
             class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600" />
    </div>

    <div>
      <label for="show_id" class="block font-medium mb-1">Associated Show *</label>
      <select name="show_id" id="show_id" required
              class="w-full border rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-purple-600">
        <option value="">-- Select a Show --</option>
        <?php foreach ($shows as $show): ?>
          <option value="<?= $show['id'] ?>" <?= $show_id == $show['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($show['title']) ?> (<?= $show['year'] ?><?= $show['semester'] ? " – Semester " . $show['semester'] : '' ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="flex justify-end">
      <button type="submit" class="bg-purple-700 text-white px-6 py-2 rounded hover:bg-purple-600 transition">
        Add Character
      </button>
    </div>
  </form>
</main>

</body>
</html>
