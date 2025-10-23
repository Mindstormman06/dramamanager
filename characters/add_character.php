<?php
include '../header.php';
require_once __DIR__ . '/../backend/db.php';

if (!isset($_SESSION['active_show'])) {
  header('Location: /show_select.php');
  exit;
}
$show_id = $_SESSION['active_show'];

// Fetch cast members to assign
$stmt = $pdo->prepare("
  SELECT u.id, u.full_name
  FROM show_users su
  JOIN users u ON su.user_id = u.id
  WHERE su.show_id = ? AND su.role IN ('cast','manager','director')
  ORDER BY u.full_name ASC
");
$stmt->execute([$show_id]);
$cast = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $desc = trim($_POST['description']);
  $actor_id = $_POST['actor_id'] ?: null;

  if ($name === '') {
    $error = 'Character name is required.';
  } else {
    $stmt = $pdo->prepare("INSERT INTO characters (name, show_id, description) VALUES (?, ?, ?)");
    $stmt->execute([$name, $show_id, $desc]);
    $charId = $pdo->lastInsertId();

    if ($actor_id) {
      $stmt = $pdo->prepare("INSERT INTO casting (show_id, character_id, user_id) VALUES (?, ?, ?)");
      $stmt->execute([$show_id, $charId, $actor_id]);
    }

    $success = 'Character added successfully.';
    header("Location: /characters/");
  }
}
?>


  <main class="flex-1 w-full max-w-6xl px-4 py-12 mx-auto">
    <h1 class="text-2xl font-bold mb-4">Add Character</h1>
    <?php if ($error): ?><p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <form method="POST" class="bg-white p-6 rounded shadow space-y-4">
      <div>
        <label class="block font-semibold mb-1">Character Name</label>
        <input type="text" name="name" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block font-semibold mb-1">Description</label>
        <textarea name="description" class="w-full border rounded p-2" placeholder="e.g. The lead detective in Act 2"></textarea>
      </div>
      <div>
        <label class="block font-semibold mb-1">Assign Actor</label>
        <select name="actor_id" class="w-full border rounded p-2">
          <option value="">— None —</option>
          <?php foreach ($cast as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">Add Character</button>
    </form>

    <div class="mt-4">
      <a href="characters.php" class="text-blue-600 hover:underline">Back to Character List</a>
    </div>
  </main>
</body>
</html>
