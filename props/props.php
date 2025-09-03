<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

// Allowed sorting options
$allowedSorts = ['name', 'condition', 'category', 'show'];
$sort = $_GET['sort'] ?? 'name'; // Default sort by name
$sort = in_array($sort, $allowedSorts) ? $sort : 'name';

//Default required role
$required_role='props';

// Map sort keys to database columns
$sortColumn = match ($sort) {
    'condition' => 'props.itemcondition',
    'category' => 'propcategories.name',
    'show' => 'shows.title',
    default => 'props.name',
};

// Helper for sort links (to match costumes.php)
function sortLink($label, $key, $currentSort) {
    $base = 'text-blue-600 hover:underline';
    $active = $key === $currentSort ? 'font-bold underline text-[#7B1E3B]' : $base;
    return "<a href=\"?sort=$key\" class=\"$active\">$label</a>";
}

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
      <h1 class="text-3xl font-bold text-[#7B1E3B]">Props</h1>
      <?php if ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin' || in_array($required_role, $_SESSION['student_roles'])) : ?>
        <a href="add_prop.php" class="bg-[#7B1E3B] text-white px-4 py-2 rounded shadow hover:bg-[#9B3454] transition">
          + Add Prop
        </a>
      <?php endif; ?>
    </div>

    <div class="mb-4 text-sm">
      <span class="text-gray-700 font-medium">Sort by:</span>
      <?= sortLink("Name", "name", $sort) ?> |
      <?= sortLink("Condition", "condition", $sort) ?> |
      <?= sortLink("Category", "category", $sort) ?> |
      <?= sortLink("Show", "show", $sort) ?>
    </div>

    <?php if (count($props) === 0): ?>
      <?php if ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin' || in_array($required_role, $_SESSION['student_roles'])) : ?>
      <div class="text-center py-10 text-gray-500 italic">
        No costumes found. Click <strong class="text-[#7B1E3B]">“Add Prop”</strong> to get started!
      </div>
      <?php else: ?>
        <div class="text-center py-2 text-gray-500 italic">
          No costumes found.
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="grid gap-4 md:grid-cols-2">
        <?php foreach ($props as $p): ?>
          <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-[#7B1E3B] flex gap-4 hover:shadow-lg transition">
            <?php if (!empty($p['photo_url'])): ?>
              <img src="../<?= htmlspecialchars($p['photo_url']) ?>" alt="Photo of <?= htmlspecialchars($p['name']) ?>" class="w-24 h-24 object-cover rounded-lg">
            <?php else: ?>
              <div class="w-24 h-24 flex items-center justify-center bg-gray-200 text-gray-400 rounded">No Photo</div>
            <?php endif; ?>

            <div class="flex-1">
              <h3 class="text-xl font-bold"><?= htmlspecialchars($p['name']) ?></h3>
              <?php if (isset($p['category_name'])): ?>
                <p class="text-gray-700 text-sm leading-relaxed">Category: <?= htmlspecialchars($p['category_name']) ?></p>
              <?php endif; ?>
              <?php if (isset($p['description'])): ?>
                <p class="text-gray-700 text-sm leading-relaxed">Description: <?= htmlspecialchars($p['description']) ?></p>
              <?php endif; ?>
              <?php if (isset($p['itemcondition'])): ?>
                <p class="text-gray-700 text-sm leading-relaxed">Condition: <?= htmlspecialchars($p['itemcondition']) ?></p>
              <?php endif; ?>
              <?php if (isset($p['show_titles'])): ?>
                <p class="text-gray-700 text-sm leading-relaxed">Used In: <?= htmlspecialchars($p['show_titles']) ?></p>
              <?php endif; ?>

              <?php if ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin' || in_array($required_role, $_SESSION['student_roles'])) : ?>
                <div class="flex gap-4 mt-2 text-sm">
                  <a href="edit_prop.php?id=<?= $p['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                  <a href="../backend/props/delete_prop.php?id=<?= $p['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this prop?');">Delete</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
  <?php include '../footer.php'; ?>
</body>
</html>
