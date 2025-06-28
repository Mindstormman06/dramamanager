<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

// Get selected show_id
$show_id = $_GET['show_id'] ?? null;

// Sort Options
$sort = $_GET['sort'] ?? 'alpha';

$direction = $_GET['direction'] ?? null;

if (!$direction) {
  // Default direction based on sort
  $direction = ($sort === 'alpha') ? 'ASC' : 'DESC';
} else {
  $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
}

switch ($sort) {
  case 'lines':
    $orderBy = "line_count $direction";
    break;
  case 'mentions':
    $orderBy = "mention_count $direction";
    break;
  case 'alpha':
  default:
    $orderBy = "characters.stage_name $direction";
    break;
}


// Fetch all shows for dropdown
$allShows = $pdo->query("SELECT id, title, year, semester FROM shows ORDER BY year DESC, title ASC")->fetchAll(PDO::FETCH_ASSOC);

// If no show selected, render show selection page
if (!$show_id):
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Select Show | Characters</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-6 text-[#7B1E3B]">👥 View Characters by Show</h1>

    <form method="GET" action="characters.php" class="space-y-4">
      <label for="show_id" class="block text-sm font-medium">Select a Show:</label>
      <select name="show_id" id="show_id"
              class="w-full border rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]" required>
        <option value="">-- Select a Show --</option>
        <?php foreach ($allShows as $s): ?>
          <option value="<?= $s['id'] ?>">
            <?= htmlspecialchars($s['title']) ?> (<?= $s['year'] ?><?= $s['semester'] ? " – Semester " . $s['semester'] : '' ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <div class="flex justify-end">
        <button type="submit" class="bg-[#7B1E3B] text-white px-4 py-2 rounded hover:bg-[#9B3454] transition">
          View Characters
        </button>
      </div>
    </form>
  </main>
</body>
</html>
<?php
exit;
endif;

// Fetch characters for selected show
$stmt = $pdo->prepare("
    SELECT characters.*, shows.title AS show_title
    FROM characters
    LEFT JOIN shows ON characters.show_id = shows.id
    WHERE characters.show_id = ?
    ORDER BY $orderBy
");
$stmt->execute([$show_id]);
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Characters | QSS Drama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-[#7B1E3B]">Characters</h1>
      <a href="add_character.php?show_id=<?= $show_id ?>" class="bg-[#7B1E3B] text-white px-4 py-2 rounded hover:bg-[#9B3454] transition">
        + Add Character
      </a>
    </div>

    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
      <form method="GET" class="flex items-center gap-2">
        <input type="hidden" name="show_id" value="<?= $show_id ?>">
        <label for="sort" class="text-sm font-medium text-gray-700">Sort by:</label>
        <select name="sort" id="sort" class="border rounded px-3 py-1 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
          <option value="alpha" <?= $sort === 'alpha' ? 'selected' : '' ?>>A–Z</option>
          <option value="lines" <?= $sort === 'lines' ? 'selected' : '' ?>>Line Count</option>
          <option value="mentions" <?= $sort === 'mentions' ? 'selected' : '' ?>>Mention Count</option>
        </select>
        <button type="submit" class="bg-[#7B1E3B] text-white px-4 py-1 rounded hover:bg-[#9B3454] transition">
          Apply
        </button>
      </form>
    </div>

    <?php if (count($characters) === 0): ?>
      <p class="text-gray-600">No characters found. Click "Add Character" to start.</p>
    <?php else: ?>
      <div class="grid gap-4">
        <?php foreach ($characters as $char): ?>
          <div class="bg-white rounded-lg p-4 shadow border-l-4 border-[#7B1E3B]">
            <h3 class="text-xl font-bold"><?= htmlspecialchars($char['stage_name']) ?></h3>
            <?php if (!empty($char['real_name'])): ?>
              <p class="text-gray-700">Real Name: <?= htmlspecialchars($char['real_name']) ?></p>
            <?php endif; ?>
            <div class="mt-2 text-sm text-gray-700">
              Mentions: <span class="font-semibold"><?= $char['mention_count'] ?? 0 ?></span>,
              Lines: <span class="font-semibold"><?= $char['line_count'] ?? 0 ?></span>
            </div>
            <div class="flex gap-4 mt-2 text-sm">
              <a href="edit_character.php?id=<?= $char['id'] ?>&show_id=<?= $show_id ?>" class="text-blue-600 hover:underline">Edit</a>
              <a href="../backend/characters/delete_character.php?id=<?= $char['id'] ?>&show_id=<?= $show_id ?>" class="text-red-600 hover:underline"
                 onclick="return confirm('Are you sure you want to delete this character?');">Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
  <?php include '../footer.php'; ?>
</body>
</html>
