<?php
include '../header.php';

$show_id = $_SESSION['active_show'] ?? null;

// Fetch characters & actor assignments
$stmt = $pdo->prepare("
  SELECT 
    c.id, c.name, c.description, u.full_name AS actor_name, sup.photo_url
  FROM characters c
  LEFT JOIN casting ca ON c.id = ca.character_id
  LEFT JOIN users u ON ca.user_id = u.id
  LEFT JOIN show_user_photos sup ON sup.user_id = u.id AND sup.show_id = c.show_id
  WHERE c.show_id = ?
  ORDER BY c.name ASC
");

$stmt->execute([$show_id]);
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Characters | <?=htmlspecialchars($config['site_title'])?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-[<?= htmlspecialchars($config['text_colour']) ?>]">Characters</h1>
      <?php if ($_SESSION['role'] === 'director' || $_SESSION['role'] === 'manager' || $_SESSION['role'] === 'admin'): ?>
        <a href="/characters/add_character.php" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] text-white px-4 py-2 rounded shadow hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] transition">
          + Add Character
        </a>
      <?php endif; ?>
    </div>

    <?php if (count($characters) === 0): ?>
      <div class="text-center py-10 text-gray-500 italic">
        No characters found for this show.
      </div>
    <?php else: ?>
      <div class="grid gap-4 md:grid-cols-2">
        <?php foreach ($characters as $ch): ?>
          <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-[<?= $config['border_colour'] ?>] flex gap-4 hover:shadow-lg transition">

              <?php if (!empty($ch['photo_url'])): ?>
                <img src="../<?= ltrim(htmlspecialchars($ch['photo_url']), '/') ?>" alt="Photo of <?= htmlspecialchars($ch['actor_name'] ?? $ch['name']) ?>" class="w-24 h-24 object-cover rounded-lg">
              <?php else: ?>
                <div class="w-24 h-24 flex items-center justify-center bg-gray-200 text-gray-400 rounded">🎭</div>
              <?php endif; ?>

            <div class="flex-1">
              <h3 class="text-xl font-bold"><?= htmlspecialchars($ch['name']) ?></h3>
              <?php if ($ch['actor_name']): ?>
                <p class="text-gray-700 text-sm leading-relaxed">Played by: <?= htmlspecialchars($ch['actor_name']) ?></p>
              <?php endif; ?>
              <?php if ($ch['description']): ?>
                <p class="text-gray-700 text-sm leading-relaxed italic"><?= htmlspecialchars($ch['description']) ?></p>
              <?php endif; ?>

              <?php if ($_SESSION['role'] === 'director' || $_SESSION['role'] === 'manager' || $_SESSION['role'] === 'admin'): ?>
                <div class="flex gap-4 mt-2 text-sm">
                  <a href="/characters/edit/?id=<?= $ch['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                  <a href="../backend/characters/delete_character.php?id=<?= $ch['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this character?');">Delete</a>
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
