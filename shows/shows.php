<?php

require_once __DIR__ . '/../backend/db.php';
include '../header.php';

$error = $_GET['error'] ?? null;

// Fetch all shows
$stmt = $pdo->query("SELECT * FROM shows ORDER BY year DESC, title ASC");
$shows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Shows | QSS Drama Program</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <?php if ($error === 'linked'): ?>
      <div class="bg-red-100 text-red-700 border border-red-300 p-4 rounded mb-6">
        <strong>Cannot delete show:</strong> It is linked to characters, lines, costumes, or props.
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <div class="bg-green-100 text-green-700 border border-green-300 p-4 rounded mb-6">
        <?php if ($_GET['success'] === 'scan_complete'): ?>
          Script scanned successfully. No new characters found. Mentions and lines updated.
        <?php elseif ($_GET['success'] === 'new_characters_added'): ?>
          New characters added successfully.
        <?php elseif ($_GET['success'] === 'deleted'): ?>
          Show deleted successfully.
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-semibold text-purple-800">Production List</h2>
      <a href="add_show.php" class="bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded transition">+ Add Show</a>
    </div>

    <?php if (count($shows) === 0): ?>
      <p class="text-gray-600">No shows found. Click "Add Show" to begin.</p>
    <?php else: ?>
      <div class="grid gap-4">
        <?php foreach ($shows as $show): ?>
          <div class="bg-white rounded-lg p-4 shadow border-l-4 border-purple-600">
            <h3 class="text-xl font-bold"><?= htmlspecialchars($show['title']) ?></h3>
            <?php if (!empty($show['year'])): ?>
              <p class="text-sm text-gray-500">Year: <?= htmlspecialchars($show['year']) ?><?= $show['semester'] ? ' – Semester ' . htmlspecialchars($show['semester']) : '' ?></p>
            <?php endif; ?>
            <?php if (!empty($show['notes'])): ?>
              <p class="mt-2 text-gray-700"><?= nl2br(htmlspecialchars($show['notes'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($show['script_path'])): ?>
              <p class="mt-2 text-gray-700">
                <a href="<?= htmlspecialchars($show['script_path']) ?>" target="_blank" class="text-blue-600 hover:underline">
                  View Script (PDF)
                </a>
              </p>
            <?php endif; ?>
            <div class="flex gap-2 mt-4">
              <a href="edit_show.php?id=<?= $show['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
              <a href="../backend/shows/delete_show.php?id=<?= $show['id'] ?>" class="text-red-600 hover:underline"
                onclick="return confirm('Are you sure you want to delete this show?');">Delete</a>
              <!-- <a href="../backend/scripts/scan_script.php?show_id=<?= $show['id'] ?>" 
                 class="text-green-600 hover:underline"
                 onclick="return confirm('Are you sure you want to scan the script? This only functions on SPECIFICALLY FORMATTED SCRIPTS');">
                 Scan
              </a> -->
              <!-- Disabled scan button until auto-detect fixed! -->
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
