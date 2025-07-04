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

function sortLink($label, $key, $currentSort) {
    $base = 'text-blue-600 hover:underline';
    $active = $key === $currentSort ? 'font-bold underline text-[#7B1E3B]' : $base;
    return "<a href=\"?sort=$key\" class=\"$active\">$label</a>";
}



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
      <h1 class="text-3xl font-bold text-[#7B1E3B]">Costumes</h1>
      <a href="add_costume.php" class="bg-[#7B1E3B] text-white px-4 py-2 rounded shadow hover:bg-[#9B3454] transition">
        + Add Costume
      </a>
    </div>

    <div class="mb-4 text-sm">
      <span class="text-gray-700 font-medium">Sort by:</span>
      <?= sortLink("Name", "name", $sort) ?> |
      <?= sortLink("Era", "era", $sort) ?> |
      <?= sortLink("Style", "style", $sort) ?> |
      <?= sortLink("Category", "category", $sort) ?> |
      <?= sortLink("Condition", "condition", $sort) ?> |
      <?= sortLink("Show", "show", $sort) ?>
    </div>


    <?php if (count($costumes) === 0): ?>
      <div class="text-center py-10 text-gray-500 italic">
        No costumes found. Click <strong class="text-[#7B1E3B]">“Add Costume”</strong> to get started!
      </div>
    <?php else: ?>
      <div class="grid gap-4 md:grid-cols-2">
        <?php foreach ($costumes as $c): ?>
          <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-[#7B1E3B] flex gap-4 hover:shadow-lg transition">

            <?php if (!empty($c['photo_url'])): ?>
              <img src="../<?= htmlspecialchars($c['photo_url']) ?>" alt="Photo of <?= htmlspecialchars($c['name']) ?>" class="w-24 h-24 object-cover rounded-lg">
            <?php else: ?>
              <div class="w-24 h-24 flex items-center justify-center bg-gray-200 text-gray-400 rounded">No Photo</div>
            <?php endif; ?>

            <div class="flex-1">
              <h3 class="text-xl font-bold"><?= htmlspecialchars($c['name']) ?></h3>
              <?php if (isset($c['category_name'])): ?>
                <p class="text-gray-700 text-sm leading-relaxed">Category: <?= htmlspecialchars($c['category_name']) ?></p>
              <?php endif; ?>
              <?php if (isset($c['decade'])): ?>
                <p class="text-gray-700 text-sm leading-relaxed">Era: <?= htmlspecialchars($c['decade']) ?></p>
              <?php endif; ?>
              <?php if (isset($c['style'])): ?>
                <p class="text-gray-700 text-sm leading-relaxed">Style: <?= htmlspecialchars($c['style']) ?></p>
              <?php endif; ?>
              <?php if (isset($c['location'])): ?>
                <p class="text-gray-700 text-sm leading-relaxed">Location: <?= htmlspecialchars($c['location']) ?></p>
              <?php endif; ?>
              <?php if (isset($c['itemcondition'])): ?>
                <p class="text-gray-700 text-sm leading-relaxed">Condition: <?= htmlspecialchars($c['itemcondition']) ?></p>
              <?php endif; ?>
              <?php if (isset($c['show_titles'])): ?>
                <p class="text-gray-700 text-sm leading-relaxed">Used In: <?= htmlspecialchars($c['show_titles']) ?></p>
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
  <?php include '../footer.php'; ?>

</body>
</html>
