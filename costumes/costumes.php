<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php'; // Include header for authentication and session management

// Allowed sorting options
$allowedSorts = ['name', 'era', 'style', 'category', 'show', 'condition'];
$sort = $_GET['sort'] ?? 'name'; // Default sort by name
$sort = in_array($sort, $allowedSorts) ? $sort : 'name';

// Default allowed roles
$required_role='costumes';

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
    global $config;
    $base = 'text-blue-600 hover:underline';
    $textColour = htmlspecialchars($config['text_colour'] ?? '#000');
    $textColourClass = "text-[$textColour]";
    $active = $key === $currentSort ? "font-bold underline $textColourClass" : $base;

    $labelEsc = htmlspecialchars($label);
    $keyEsc = urlencode($key);

    return "<a href=\"?sort={$keyEsc}\" class=\"{$active}\">{$labelEsc}</a>";
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
  <title>Costumes | <?=htmlspecialchars($config['site_title'])?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-[<?= htmlspecialchars($config['text_colour']) ?>]">Costumes</h1>
      <?php if ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin' || in_array($required_role, $_SESSION['student_roles'])) : ?>
        <a href="/costumes/add/" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] text-white px-4 py-2 rounded shadow hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] transition">
          + Add Costume
        </a>
      <?php endif; ?>
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
      <?php if ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin' || in_array($required_role, $_SESSION['student_roles'])) : ?>
      <div class="text-center py-10 text-gray-500 italic">
        No costumes found. Click <strong class="text-[<?= htmlspecialchars($config['text_colour']) ?>]">“Add Costume”</strong> to get started!
      </div>
      <?php else: ?>
        <div class="text-center py-2 text-gray-500 italic">
          No costumes found.
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="grid gap-4 md:grid-cols-2">
        <?php foreach ($costumes as $c): ?>
          <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-[<?= $config['border_colour'] ?>] flex gap-4 hover:shadow-lg transition">

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
              <?php if ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin' || in_array($required_role, $_SESSION['student_roles'])) : ?>
                <div class="flex gap-4 mt-2 text-sm">
                  <a href="/costumes/edit/?id=<?= $c['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                  <a href="../backend/costumes/delete_costume.php?id=<?= $c['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this costume?');">Delete</a>
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
