<?php

require_once __DIR__ . '/../backend/db.php';

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
<body class="bg-gray-50 text-gray-800">

  <header class="bg-purple-800 text-white py-6 mb-8 shadow-md">
    <div class="max-w-6xl mx-auto px-4">
      <h1 class="text-3xl font-bold">🎭 Shows</h1>
      <a href="../index.php" class="text-sm underline hover:text-purple-300">← Back to Home</a>
    </div>
  </header>

  <main class="max-w-6xl mx-auto px-4">
    
  <?php if ($error === 'linked'): ?>
    <div class="bg-red-100 text-red-700 border border-red-300 p-4 rounded mb-6 max-w-6xl mx-auto">
      <strong>Cannot delete show:</strong> It is linked to characters, lines, costumes, or props.
    </div>
  <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-semibold">Production List</h2>
      <a href="add_show.php" class="bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded transition">+ Add Show</a>
    </div>

    <?php if (count($shows) === 0): ?>
      <p class="text-gray-600">No shows found. Click "Add Show" to begin.</p>
    <?php else: ?>
      <div class="grid gap-4">
        <?php foreach ($shows as $show): ?>
          <div class="bg-white rounded-lg p-4 shadow border-l-4 border-purple-600">
            <h3 class="text-xl font-bold"><?= htmlspecialchars($show['title']) ?></h3>
            <p class="text-sm text-gray-500">Year: <?= htmlspecialchars($show['year']) ?><?= $show['semester'] ? ' – Semester ' . htmlspecialchars($show['semester']) : '' ?></p>
            <?php if (!empty($show['notes'])): ?>
              <p class="mt-2 text-gray-700"><?= nl2br(htmlspecialchars($show['notes'])) ?></p>
            <?php endif; ?>
            <div class="flex gap-2 mt-4">
              <a href="edit_show.php?id=<?= $show['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
              <a href="../backend/shows/delete_show.php?id=<?= $show['id'] ?>" class="text-red-600 hover:underline"
                onclick="return confirm('Are you sure you want to delete this show?');">Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

</body>
</html>
