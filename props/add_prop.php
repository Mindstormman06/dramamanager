<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_show'])) {
  header('Location: /login/');
  exit;
}
$show_id = $_SESSION['active_show'];

$error = '';
$success = '';

// Fetch cast/crew to assign props (same roles as costumes)
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
  $name = trim($_POST['name'] ?? '');
  $owner_id = !empty($_POST['owner_id']) ? intval($_POST['owner_id']) : null;
  $notes = trim($_POST['notes'] ?? '');
  $photo = null;

  // Handle photo upload
  if (!empty($_FILES['photo']['name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
    $uploadDir = __DIR__ . '/../uploads/props/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetFile = $uploadDir . $fileName;

    if (!@move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
      $last = error_get_last();
      log_event("Failed to move uploaded prop photo: " . json_encode($last), 'ERROR');
      $error = 'Failed to save uploaded photo.';
    } else {
      $photo = '/uploads/props/' . $fileName;
    }
  }

  if ($error === '') {
    if (empty($name)) $name = 'Unnamed Prop';
    $stmt = $pdo->prepare("
      INSERT INTO assets (name, type, show_id, owner_id, notes, photo_url, created_at)
      VALUES (?, 'prop', ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$name, $show_id, $owner_id, $notes ?: null, $photo]);
    $success = 'Prop added successfully!';
    header("Location: /props/");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Prop | <?=htmlspecialchars($config['site_title'])?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <h1 class="text-2xl font-bold mb-4">Add Prop</h1>

    <?php if ($error): ?><p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
      <div>
        <label class="block font-semibold mb-1">Photo</label>
        <input type="file" name="photo" accept="image/*" class="w-full border rounded p-2">
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
      <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">Add Prop</button>
    </form>

    <div class="mt-4">
      <a href="/props/" class="text-blue-600 hover:underline">Back to Prop List</a>
    </div>
  </main>
  <?php include '../footer.php'; ?>
</body>
</html>
