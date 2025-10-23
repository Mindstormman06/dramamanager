<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

$show_id = $_SESSION['active_show'] ?? null;

// Fetch all costumes for the current show
$stmt = $pdo->prepare("
  SELECT 
    a.id, a.name, a.notes, a.photo_url, a.created_at,
    u.full_name AS owner_name
  FROM assets a
  LEFT JOIN users u ON a.owner_id = u.id
  WHERE a.show_id = ? AND a.type = 'costume'
  ORDER BY owner_name ASC
");
$stmt->execute([$show_id]);
$costumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-[<?= htmlspecialchars($config['text_colour']) ?>]">Costumes</h1>
      <?php if ($_SESSION['role'] === 'director' || $_SESSION['role'] === 'manager'): ?>
        <a href="/costumes/add_costume.php" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] text-white px-4 py-2 rounded shadow hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] transition">
          + Add Costume
        </a>
      <?php endif; ?>
    </div>

    <?php if (count($costumes) === 0): ?>
      <div class="text-center py-10 text-gray-500 italic">
        No costumes found for this show.
        <?php if (
          $_SESSION['role'] === 'director' || $_SESSION['role'] === 'manager'): ?>
          Click <strong class="text-[<?= htmlspecialchars($config['text_colour']) ?>]">“Add Costume”</strong> to get started!
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="grid gap-4 md:grid-cols-2">
        <?php foreach ($costumes as $c): ?>
          <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-[<?= $config['border_colour'] ?>] flex gap-4 hover:shadow-lg transition">
            <?php if (!empty($c['photo_url'])): ?>
              <img src="../<?= ltrim(htmlspecialchars($c['photo_url']), '/') ?>" alt="Photo of <?= htmlspecialchars($c['name']) ?>" class="w-24 h-24 object-cover rounded-lg">
            <?php else: ?>
              <div class="w-24 h-24 flex items-center justify-center bg-gray-200 text-gray-400 rounded">No Photo</div>
            <?php endif; ?>

            <div class="flex-1">
              <h3 class="text-xl font-bold"><?= htmlspecialchars($c['name']) ?></h3>
              <?php if ($c['owner_name']): ?>
                <p class="text-gray-700 text-sm leading-relaxed"><?= htmlspecialchars($c['owner_name']) ?></p>
              <?php endif; ?>
              <?php if ($c['notes']): ?>
                <p class="text-gray-700 text-sm leading-relaxed italic"><?= htmlspecialchars($c['notes']) ?></p>
              <?php endif; ?>

              <?php if (
                $_SESSION['role'] === 'director' || $_SESSION['role'] === 'manager'): ?>
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
