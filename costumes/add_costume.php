<?php
include '../header.php';
require_once __DIR__ . '/../backend/upload_image.php';


if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_show'])) {
  header('Location: /login/');
  exit;
}

$show_id = $_SESSION['active_show'];
$error = '';
$success = '';

// Fetch cast/crew to assign costumes
$stmt = $pdo->prepare("
  SELECT u.id, u.full_name
  FROM show_users su
  JOIN users u ON su.user_id = u.id
  WHERE su.show_id = ? AND su.role IN ('cast', 'crew', 'manager', 'director')
  ORDER BY u.full_name ASC
");
$stmt->execute([$show_id]);
$people = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $owner_id = $_POST['owner_id'] ?? null;
  $notes = trim($_POST['notes']);
  $photo = null;

  

  if ($owner_id == null) {
    $error = 'Please select who the costume belongs to.';
  } else {
    // Handle photo upload
    $photo = handle_image_upload('photo', __DIR__.'/../uploads/costumes', '/uploads/costumes', $error);

    if ($uploadErr) {
        $error = $uploadErr; // re-render form with all fields preserved
    } else {
      $stmt = $pdo->prepare("
        INSERT INTO assets (name, type, show_id, owner_id, notes, photo_url)
        VALUES (?, 'costume', ?, ?, ?, ?)
      ");
      $stmt->execute([$name ?: 'Unnamed Costume', $show_id, $owner_id, $notes, $photo]);
      $success = 'Costume added successfully!';
      header("Location: /costumes/");
      }
  }
}
?>

  <main class="flex-1 w-full max-w-6xl px-4 py-12 mx-auto">
    <h1 class="text-2xl font-bold mb-4">Add Costume</h1>

    <?php if ($error): ?><p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
      <div>
        <label class="block font-semibold mb-1">Photo</label>
        <input type="file" name="photo" id="photo" accept="image/*" class="w-full border rounded p-2">
        <img id="preview" class="hidden mt-2 w-24 h-24 object-cover rounded border" alt="Preview">
      </div>
      <div>
        <label class="block font-semibold mb-1">Name (optional)</label>
        <input type="text" name="name" class="w-full border rounded p-2">
      </div>
      <div>
        <label class="block font-semibold mb-1">Belongs to</label>
        <select name="owner_id" class="w-full border rounded p-2">
          <option value="">— Select a Person —</option>
          <?php foreach ($people as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block font-semibold mb-1">Notes</label>
        <textarea name="notes" class="w-full border rounded p-2" placeholder="e.g. Only used in Act 2"></textarea>
      </div>
      <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">Add Costume</button>
    </form>

    <div class="mt-4">
      <a href="/costumes/" class="text-blue-600 hover:underline">Back to Costume List</a>
    </div>
  </main>
  <?php include '../footer.php'; ?>
</body>
</html>
