<?php
// =============================================
// add_media.php
// =============================================
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/upload_media.php';
include '../header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_show'])) {
  header('Location: /login/');
  exit;
}

$user_id = $_SESSION['user_id'];
$show_id = $_SESSION['active_show'];
$is_manager = in_array($_SESSION['role'] ?? '', ['director','manager','admin']);
if (!$is_manager) {
  die('<p class="text-center text-red-600 mt-10 font-semibold">Access Denied: Only managers can upload media.</p>');
}

$error = '';
$success = '';
$label = $_POST['label'] ?? '';
$description = $_POST['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_media'])) {
    $result = handle_media_upload(
    'file',
    __DIR__ . '/../uploads/media/images',
    __DIR__ . '/../uploads/media/videos',
    __DIR__ . '/../uploads/media/thumbnails',
    '/uploads/media/images',
    '/uploads/media/videos',
    '/uploads/media/thumbnails',
    $uploadErr
    );


  if ($uploadErr) {
    $error = $uploadErr; // show inline
  } elseif (!$result) {
    $error = 'Please choose a file to upload.';
  } else {
    $fileUrl  = $result['url'];
    $fileType = $result['type']; // image|video

    $thumbUrl = $result['thumbnail_url'] ?? null;

    $stmt = $pdo->prepare("
    INSERT INTO media_album 
    (show_id, uploader_id, file_url, thumbnail_url, file_type, label, description)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
    $show_id,
    $user_id,
    $fileUrl,
    $thumbUrl,
    $fileType,
    $label ?: null,
    $description ?: null
    ]);

    $success = 'Media uploaded successfully!';
    header('Location: /album/');

    // Reset form
    $label = '';
    $description = '';
  }
}

$button = htmlspecialchars($config['button_colour'] ?? '#ef4444');
$buttonHover = htmlspecialchars($config['button_hover_colour'] ?? '#dc2626');
$textColour = htmlspecialchars($config['text_colour'] ?? '#111827');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Media | <?= htmlspecialchars($config['site_title']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-xl mx-auto mt-10 bg-white p-8 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">+ Add Media</h1>

    <?php if ($error): ?>
      <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
      <p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">File (image or video)</label>
        <input type="file" name="file" id="file" accept="image/*,video/mp4,video/quicktime,video/webm" class="w-full border rounded p-2" required>
        <div id="fileHint" class="text-xs text-gray-500 mt-1">Allowed: JPG, PNG, GIF, WEBP, MP4, MOV, WEBM</div>
        <img id="imgPreview" class="hidden mt-3 w-40 h-40 object-cover rounded border" alt="Preview">
        <div id="vidPreview" class="hidden mt-3 text-sm text-gray-600">🎬 Video selected</div>
      </div>

      <div>
        <label class="block font-semibold mb-1">Label</label>
        <select name="label" class="w-full border rounded p-2">
          <option value="">— Choose a label —</option>
          <?php $opts=['Rehearsal','Performance','Behind the Scenes','Promo','Other'];
          foreach ($opts as $opt){ $sel = ($label===$opt)?'selected':''; echo "<option $sel>".htmlspecialchars($opt)."</option>"; } ?>
        </select>
      </div>

      <div>
        <label class="block font-semibold mb-1">Description (optional)</label>
        <textarea name="description" rows="3" class="w-full border rounded p-2" placeholder="Short description..."><?= htmlspecialchars($description) ?></textarea>
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" name="submit_media" class="bg-[<?= $button ?>] hover:bg-[<?= $buttonHover ?>] text-white px-4 py-2 rounded">Upload</button>
        <a href="/album/" class="text-blue-600 hover:underline">Cancel</a>
      </div>
    </form>
  </main>
  <script>
  // Basic preview: show image preview or a small note for videos
  document.getElementById('file').addEventListener('change', function(){
    const file = this.files && this.files[0];
    const img = document.getElementById('imgPreview');
    const vid = document.getElementById('vidPreview');
    img.classList.add('hidden');
    vid.classList.add('hidden');
    if (!file) return;
    if (file.type.startsWith('image/')){
      const url = URL.createObjectURL(file);
      img.src = url;
      img.classList.remove('hidden');
    } else {
      vid.classList.remove('hidden');
    }
  });
  </script>
</body>
</html>
