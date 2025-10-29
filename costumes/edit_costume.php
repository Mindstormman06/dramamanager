<?php
include '../header.php';
require_once __DIR__ . '/../backend/upload_image.php';


if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_show'])) {
  header('Location: /login/');
  exit;
}

$show_id = $_SESSION['active_show'];
$id = $_GET['id'] ?? null;

if (!$id) {
  header('Location: costumes.php');
  exit;
}

// Fetch costume info
$stmt = $pdo->prepare("
  SELECT a.*, u.full_name AS owner_name
  FROM assets a
  LEFT JOIN users u ON a.owner_id = u.id
  WHERE a.id = ? AND a.show_id = ? AND a.type = 'costume'
");
$stmt->execute([$id, $show_id]);
$costume = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$costume) {
  die('<p class="text-center text-red-600 mt-10 font-semibold">Costume not found or belongs to another show.</p>');
}

// Fetch cast/crew for assigning
$stmt = $pdo->prepare("
  SELECT u.id, u.full_name
  FROM show_users su
  JOIN users u ON su.user_id = u.id
  WHERE su.show_id = ? AND su.role IN ('cast','crew','manager','director')
  ORDER BY u.full_name ASC
");
$stmt->execute([$show_id]);
$people = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $owner_id = $_POST['owner_id'] ?? null;
  $notes = trim($_POST['notes']);
  $name = trim($_POST['name']);
  $photo = $costume['photo_url'];

  // Handle new photo upload (optional)
  // if (!empty($_FILES['photo']['name'])) {
  //   $targetDir = "../uploads/costumes/";
  //   if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
  //   $fileName = time() . '_' . basename($_FILES["photo"]["name"]);
  //   $targetFile = $targetDir . $fileName;
  //   move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile);
  //   $photo = "/uploads/costumes/" . $fileName;
  // }

  $photo = handle_image_upload('photo', __DIR__.'/../uploads/costumes', '/uploads/costumes', $error);

  if ($owner_id == null) {
    $error = 'Please select who the costume belongs to.';
  } else {
    $stmt = $pdo->prepare("
      UPDATE assets 
      SET name = ?, owner_id = ?, notes = ?, photo_url = ?
      WHERE id = ? AND show_id = ? AND type = 'costume'
    ");
    $stmt->execute([$name ?: 'Unnamed Costume', $owner_id, $notes, $photo, $id, $show_id]);
    $success = 'Costume updated successfully.';
    header("Location: /costumes/");
  }

}
?>

  <main class="flex-1 w-full max-w-6xl px-4 py-12 mx-auto">
    <h1 class="text-2xl font-bold mb-4">Edit Costume</h1>

    <?php if ($error): ?><p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
      <div>
        <label class="block font-semibold mb-1">Current Photo</label>
        <?php if ($costume['photo_url']): ?>
          <img src="<?= htmlspecialchars($costume['photo_url']) ?>" alt="Costume" class="w-48 h-48 object-cover rounded mb-2">
        <?php else: ?>
          <p class="text-gray-500 italic">No photo uploaded</p>
        <?php endif; ?>
        <input type="file" name="photo" accept="image/*" class="w-full border rounded p-2">
      </div>

      <div>
        <label class="block font-semibold mb-1">Name (optional)</label>
        <input type="text" name="name" value="<?= htmlspecialchars($costume['name']) ?>" class="w-full border rounded p-2">
      </div>

      <div>
        <label class="block font-semibold mb-1">Belongs to</label>
        <select name="owner_id" class="w-full border rounded p-2">
          <option value="">— Select a Person —</option>
          <?php foreach ($people as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $costume['owner_id'] == $p['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['full_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block font-semibold mb-1">Notes</label>
        <textarea name="notes" class="w-full border rounded p-2"><?= htmlspecialchars($costume['notes']) ?></textarea>
      </div>

      <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">Save Changes</button>
    </form>

    <div class="mt-4">
      <a href="costumes.php" class="text-blue-600 hover:underline">Back to Costume List</a>
    </div>
  </main>
</body>
<?php include '../footer.php'; ?>
</html>
