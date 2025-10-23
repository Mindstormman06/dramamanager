<?php
include '../header.php';
require_once __DIR__ . '/../backend/db.php';

$show_id = $_SESSION['active_show'] ?? null;
$id = $_GET['id'] ?? null;
if (!$show_id || !$id) header('Location: /characters/');

$stmt = $pdo->prepare("
  SELECT c.*, u.full_name AS actor_name, ca.user_id AS actor_id
  FROM characters c
  LEFT JOIN casting ca ON c.id = ca.character_id
  LEFT JOIN users u ON ca.user_id = u.id
  WHERE c.id = ? AND c.show_id = ?
");
$stmt->execute([$id, $show_id]);
$char = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$char) die("Character not found.");

$stmt = $pdo->prepare("
  SELECT u.id, u.full_name
  FROM show_users su
  JOIN users u ON su.user_id = u.id
  WHERE su.show_id = ? AND su.role IN ('cast','manager','director')
  ORDER BY u.full_name ASC
");
$stmt->execute([$show_id]);
$cast = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $desc = trim($_POST['description']);
  $actor_id = $_POST['actor_id'] ?: null;

  $pdo->prepare("UPDATE characters SET name = ?, description = ? WHERE id = ? AND show_id = ?")
      ->execute([$name, $desc, $id, $show_id]);

  $pdo->prepare("DELETE FROM casting WHERE character_id = ?")->execute([$id]);
  if ($actor_id) {
    $pdo->prepare("INSERT INTO casting (show_id, character_id, user_id) VALUES (?, ?, ?)")
        ->execute([$show_id, $id, $actor_id]);
  }

  header("Location: /characters/");
  exit;
}
?>


  <main class="flex-1 w-full max-w-6xl px-4 py-12 mx-auto">
    <h1 class="text-2xl font-bold mb-4">Edit Character</h1>

    <form method="POST" class="bg-white p-6 rounded shadow space-y-4">
      <div>
        <label class="block font-semibold mb-1">Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($char['name']) ?>" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block font-semibold mb-1">Description</label>
        <textarea name="description" class="w-full border rounded p-2"><?= htmlspecialchars($char['description']) ?></textarea>
      </div>
      <div>
        <label class="block font-semibold mb-1">Assign Actor</label>
        <select name="actor_id" class="w-full border rounded p-2">
          <option value="">— None —</option>
          <?php foreach ($cast as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $char['actor_id'] == $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['full_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded">Save Changes</button>
    </form>

    <div class="mt-4">
      <a href="characters.php" class="text-blue-600 hover:underline">Back to Character List</a>
    </div>
  </main>
</body>
</html>
