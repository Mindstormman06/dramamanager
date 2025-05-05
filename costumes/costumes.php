<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php'; // Include header for authentication and session management

// Allowed sorting options
$allowedSorts = ['name', 'era', 'style', 'category', 'show', 'condition'];
$sort = $_GET['sort'] ?? 'name'; // Default sort by name
$sort = in_array($sort, $allowedSorts) ? $sort : 'name';

// Map sort keys to database columns
$sortColumn = match ($sort) {
    'era' => 'costumes.decade',
    'style' => 'costumes.style',
    'category' => 'costumecategories.name',
    'show' => 'shows.title',
    'condition' => 'costumes.itemcondition',
    default => 'costumes.name',
};

// Fetch all costumes with category and show associations
$stmt = $pdo->prepare("
    SELECT 
        costumes.*, 
        costumecategories.name AS category_name,
        GROUP_CONCAT(shows.title ORDER BY shows.title ASC SEPARATOR ', ') AS show_titles
    FROM costumes
    LEFT JOIN costumecategories ON costumes.category_id = costumecategories.id
    LEFT JOIN showcostumes ON costumes.id = showcostumes.costume_id
    LEFT JOIN shows ON showcostumes.show_id = shows.id
    GROUP BY costumes.id
    ORDER BY $sortColumn ASC
");
$stmt->execute();
$costumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Costumes | QSS Drama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-emerald-800">Costumes</h1>
      <a href="add_costume.php" class="bg-emerald-700 text-white px-4 py-2 rounded hover:bg-emerald-600 transition">
        + Add Costume
      </a>
    </div>

    <div class="mb-4">
      <span class="text-gray-600">Sort by:</span>
      <a href="?sort=name" class="text-blue-600 hover:underline">Name</a> |
      <a href="?sort=era" class="text-blue-600 hover:underline">Era</a> |
      <a href="?sort=style" class="text-blue-600 hover:underline">Style</a> |
      <a href="?sort=category" class="text-blue-600 hover:underline">Category</a> |
      <a href="?sort=condition" class="text-blue-600 hover:underline">Condition</a> |
      <a href="?sort=show" class="text-blue-600 hover:underline">Show</a>
    </div>

    <?php if (count($costumes) === 0): ?>
      <p class="text-gray-600">No costumes found. Click "Add Costume" to start.</p>
    <?php else: ?>
      <div class="grid gap-4">
        <?php foreach ($costumes as $c): ?>
          <div class="bg-white rounded-lg p-4 shadow border-l-4 border-emerald-600 flex gap-4">
            <?php if (!empty($c['photo_url'])): ?>
              <img src="../<?= htmlspecialchars($c['photo_url']) ?>" alt="Photo of <?= htmlspecialchars($c['name']) ?>" class="w-24 h-24 object-cover rounded">
            <?php else: ?>
              <div class="w-24 h-24 flex items-center justify-center bg-gray-200 text-gray-400 rounded">No Photo</div>
            <?php endif; ?>

            <div class="flex-1">
              <h3 class="text-xl font-bold"><?= htmlspecialchars($c['name']) ?></h3>
              <?php if (isset($c['category_name'])): ?>
                <p class="text-gray-600 text-sm">Category: <?= htmlspecialchars($c['category_name']) ?></p>
              <?php endif; ?>
              <?php if (isset($c['decade'])): ?>
                <p class="text-gray-600 text-sm">Era: <?= htmlspecialchars($c['decade']) ?></p>
              <?php endif; ?>
              <?php if (isset($c['style'])): ?>
                <p class="text-gray-600 text-sm">Style: <?= htmlspecialchars($c['style']) ?></p>
              <?php endif; ?>
              <?php if (isset($c['location'])): ?>
                <p class="text-gray-600 text-sm">Location: <?= htmlspecialchars($c['location']) ?></p>
              <?php endif; ?>
              <?php if (isset($c['itemcondition'])): ?>
                <p class="text-gray-600 text-sm">Condition: <?= htmlspecialchars($c['itemcondition']) ?></p>
              <?php endif; ?>
              <?php if (isset($c['show_titles'])): ?>
                <p class="text-gray-600 text-sm">Used In: <?= htmlspecialchars($c['show_titles']) ?></p>
              <?php endif; ?>

              <div class="flex gap-4 mt-2 text-sm">
                <a href="edit_costume.php?id=<?= $c['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                <a href="../backend/costumes/delete_costume.php?id=<?= $c['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this costume?');">Delete</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
