<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

// Allowed sorting options
$allowedSorts = ['name', 'condition', 'category', 'show'];
$sort = $_GET['sort'] ?? 'name'; // Default sort by name
$sort = in_array($sort, $allowedSorts) ? $sort : 'name';

// Map sort keys to database columns
$sortColumn = match ($sort) {
    'condition' => 'props.itemcondition',
    'category' => 'propcategories.name',
    'show' => 'shows.title',
    default => 'props.name',
};

// Fetch all props with category and show associations
$stmt = $pdo->prepare("
    SELECT 
        props.*, 
        propcategories.name AS category_name,
        GROUP_CONCAT(shows.title ORDER BY shows.title ASC SEPARATOR ', ') AS show_titles
    FROM props
    LEFT JOIN propcategories ON props.category_id = propcategories.id
    LEFT JOIN showprops ON props.id = showprops.prop_id
    LEFT JOIN shows ON showprops.show_id = shows.id
    GROUP BY props.id
    ORDER BY $sortColumn ASC
");
$stmt->execute();
$props = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Props | QSS Drama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-blue-800">Props</h1>
      <a href="add_prop.php" class="bg-blue-700 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
        + Add Prop
      </a>
    </div>

    <div class="mb-4">
      <span class="text-gray-600">Sort by:</span>
      <a href="?sort=name" class="text-blue-600 hover:underline">Name</a> |
      <a href="?sort=condition" class="text-blue-600 hover:underline">Condition</a> |
      <a href="?sort=category" class="text-blue-600 hover:underline">Category</a> |
      <a href="?sort=show" class="text-blue-600 hover:underline">Show</a>
    </div>

    <?php if (count($props) === 0): ?>
      <p class="text-gray-600">No props found. Click "Add Prop" to start.</p>
    <?php else: ?>
      <div class="grid gap-4">
        <?php foreach ($props as $p): ?>
          <div class="bg-white rounded-lg p-4 shadow border-l-4 border-blue-600 flex gap-4">
            <?php if (!empty($p['photo_url'])): ?>
              <img src="../<?= htmlspecialchars($p['photo_url']) ?>" alt="Photo of <?= htmlspecialchars($p['name']) ?>" class="w-24 h-24 object-cover rounded">
            <?php else: ?>
              <div class="w-24 h-24 flex items-center justify-center bg-gray-200 text-gray-400 rounded">No Photo</div>
            <?php endif; ?>

            <div class="flex-1">
              <h3 class="text-xl font-bold"><?= htmlspecialchars($p['name']) ?></h3>
              <?php if (isset($p['category_name'])): ?>
                <p class="text-gray-600 text-sm">Category: <?= htmlspecialchars($p['category_name']) ?></p>
              <?php endif; ?>
              <?php if (isset($p['description'])): ?>
                <p class="text-gray-600 text-sm">Description: <?= htmlspecialchars($p['description']) ?></p>
              <?php endif; ?>
              <?php if (isset($p['itemcondition'])): ?>
                <p class="text-gray-600 text-sm">Condition: <?= htmlspecialchars($p['itemcondition']) ?></p>
              <?php endif; ?>
              <?php if (isset($p['show_titles'])): ?>
                <p class="text-gray-600 text-sm">Used In: <?= htmlspecialchars($p['show_titles']) ?></p>
              <?php endif; ?>

              <div class="flex gap-4 mt-2 text-sm">
                <a href="edit_prop.php?id=<?= $p['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                <a href="../backend/props/delete_prop.php?id=<?= $p['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this prop?');">Delete</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
