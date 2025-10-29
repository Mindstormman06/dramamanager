<?php
require_once __DIR__ . '/../backend/upload_image.php';
include '../header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_show'])) {
  header('Location: /login/');
  exit;
}

$show_id = $_SESSION['active_show'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
  header('Location: /props/');
  exit;
}

// Fetch prop from assets table
$stmt = $pdo->prepare("
  SELECT a.*, u.full_name AS owner_name
  FROM assets a
  LEFT JOIN users u ON a.owner_id = u.id
  WHERE a.id = ? AND a.show_id = ? AND a.type = 'prop'
");
$stmt->execute([$id, $show_id]);
$prop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prop) {
  die('<p class="text-center text-red-600 mt-10 font-semibold">Prop not found or belongs to another show.</p>');
}

// Fetch people for owner assignment
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
  $owner_id = !empty($_POST['owner_id']) ? intval($_POST['owner_id']) : null;
  $notes = trim($_POST['notes'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $photo = $prop['photo_url'];

  // Handle new photo upload (optional)
  $photo = handle_image_upload('photo', __DIR__.'/../uploads/props', '/uploads/props', $error);
  
    if (empty($name)) $name = 'Unnamed Prop';
    $stmt = $pdo->prepare("
      UPDATE assets
      SET name = ?, owner_id = ?, notes = ?, photo_url = ?
      WHERE id = ? AND show_id = ? AND type = 'prop'
    ");
    $stmt->execute([$name, $owner_id, $notes ?: null, $photo, $id, $show_id]);

    log_event("Prop '{$name}' (ID: {$id}) updated by user '{$_SESSION['username']}'", 'INFO');

    header("Location: /props/");
    exit;
  
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Prop | <?=htmlspecialchars($config['site_title'])?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <h1 class="text-3xl font-bold text-[<?= htmlspecialchars($config['text_colour']) ?>] mb-6">Edit Prop</h1>
    <a href="/props/" class="text-blue-600 hover:underline mb-4">← Back to Prop List</a>

    <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
      <div>
        <label class="block font-semibold mb-1">Current Photo</label>
        <?php if ($prop['photo_url']): ?>
          <img src="<?= htmlspecialchars($prop['photo_url']) ?>" alt="Prop" class="w-48 h-48 object-cover rounded mb-2">
        <?php else: ?>
          <p class="text-gray-500 italic">No photo uploaded</p>
        <?php endif; ?>
        <input type="file" name="photo" accept="image/*" class="w-full border rounded p-2">
      </div>

      <div>
        <label class="block font-semibold mb-1">Name (optional)</label>
        <input type="text" name="name" value="<?= htmlspecialchars($prop['name']) ?>" class="w-full border rounded p-2">
      </div>

      <div>
        <label class="block font-semibold mb-1">Belongs to</label>
        <select name="owner_id" class="w-full border rounded p-2">
          <option value="">— Select a Person —</option>
          <?php foreach ($people as $p): ?>
            <option value="<?= $p['id'] ?>" <?= ($prop['owner_id'] == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block font-semibold mb-1">Notes</label>
        <textarea name="notes" class="w-full border rounded p-2"><?= htmlspecialchars($prop['notes']) ?></textarea>
      </div>

      <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">Save Changes</button>
    </form>
  </main>
  <?php include '../footer.php'; ?>
</body>
</html>
