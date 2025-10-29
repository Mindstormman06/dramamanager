<?php
// =============================================
// album.php
// =============================================
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_show'])) {
  header('Location: /login/');
  exit;
}

$user_id = $_SESSION['user_id'];
$show_id = $_SESSION['active_show'];
$is_manager = in_array($_SESSION['role'] ?? '', ['director','manager','admin']);

// Filters
$type   = $_GET['type']  ?? 'all';  // all|images|videos
$labelF = $_GET['label'] ?? 'all';  // all|Rehearsal|Performance|Behind|Promo|Other (free text permitted)
$search = trim($_GET['q'] ?? '');

$where = ['ma.show_id = ?'];
$params = [$show_id];

if ($type === 'images') {
  $where[] = "ma.file_type = 'image'";
} elseif ($type === 'videos') {
  $where[] = "ma.file_type = 'video'";
}

if ($labelF !== 'all' && $labelF !== '') {
  $where[] = 'ma.label = ?';
  $params[] = $labelF;
}

if ($search !== '') {
  $where[] = '(ma.description LIKE ? OR u.full_name LIKE ?)';
  $like = '%' . $search . '%';
  $params[] = $like; $params[] = $like;
}

$sql = "
  SELECT ma.*, u.full_name AS uploader_name
  FROM media_album ma
  JOIN users u ON u.id = ma.uploader_id
  WHERE " . implode(' AND ', $where) . "
  ORDER BY ma.uploaded_at DESC, ma.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$button = htmlspecialchars($config['button_colour'] ?? '#ef4444');
$buttonHover = htmlspecialchars($config['button_hover_colour'] ?? '#dc2626');
$textColour = htmlspecialchars($config['text_colour'] ?? '#111827');
$border = htmlspecialchars($config['border_colour'] ?? '#ef4444');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Album | <?= htmlspecialchars($config['site_title']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .video-badge{position:absolute;right:.5rem;bottom:.5rem;background:rgba(0,0,0,.6);color:#fff;font-size:.75rem;padding:.25rem .5rem;border-radius:.375rem}
    .card-img{aspect-ratio:1/1;object-fit:cover}
  </style>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold text-[<?= $textColour ?>]">📸 Show Album</h1>
      <div class="flex gap-2">
        <?php if ($is_manager): ?>
          <a href="/album/upload/" class="bg-[<?= $button ?>] hover:bg-[<?= $buttonHover ?>] text-white px-4 py-2 rounded shadow transition">+ Add Media</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Filters -->
    <form class="flex flex-wrap items-center gap-3 mb-6" method="GET">
      <!-- Type chips -->
      <?php
        $baseChip = 'px-3 py-1 rounded-full border text-sm font-medium transition';
        function chip($active,$button,$label,$href){
          $base = 'px-3 py-1 rounded-full border text-sm font-medium transition';
          $cls  = $active ? "bg-[{$button}] text-white border-[{$button}]" : 'border-gray-300 text-gray-700 hover:bg-gray-100';
          return "<a class=\"$base $cls\" href=\"$href\">$label</a>";
        }
        $qs = fn($arr)=>'?' . http_build_query(array_merge($_GET,$arr));
        echo chip($type==='all',$button,'All',$qs(['type'=>'all']));
        echo chip($type==='images',$button,'Images',$qs(['type'=>'images']));
        echo chip($type==='videos',$button,'Videos',$qs(['type'=>'videos']));
      ?>

      <!-- Labels -->
      <select name="label" class="ml-2 border border-gray-300 rounded p-2 text-sm">
        <?php
          $labels = ['all'=>'All Labels','Rehearsal'=>'Rehearsal','Performance'=>'Performance','Promo'=>'Promo','Other'=>'Other'];
          foreach ($labels as $val=>$lab){
            $sel = ($labelF===$val) ? 'selected' : '';
            echo "<option value=\"".htmlspecialchars($val)."\" $sel>".htmlspecialchars($lab)."</option>";
          }
        ?>
      </select>

      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search description/uploader" class="border border-gray-300 rounded p-2 text-sm w-56">
      <button class="bg-[<?= $button ?>] hover:bg-[<?= $buttonHover ?>] text-white px-3 py-2 rounded text-sm">Apply</button>
    </form>

    <?php if (count($items) === 0): ?>
      <div class="text-center py-10 text-gray-500 italic">No media found.</div>
    <?php else: ?>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($items as $m): ?>
          <div class="bg-white rounded-xl p-3 shadow-md border-l-4 hover:shadow-lg transition" style="border-left-color: <?= $border ?>;">
            <div class="relative">
              <?php if ($m['file_type']==='image'): ?>
                <img src="<?= htmlspecialchars($m['file_url']) ?>" alt="Media" class="w-full card-img rounded-lg">
              <?php else: ?>
                <a href="#" data-video="<?= htmlspecialchars($m['file_url']) ?>" class="block relative group">
                    <img src="<?= htmlspecialchars($m['thumbnail_url'] ?: '/assets/media-video-placeholder.png') ?>" 
                        alt="Video" class="w-full card-img rounded-lg">
                    <div class="video-badge group-hover:scale-110 transition-transform">▶ Play</div>
                </a>
              <?php endif; ?>

            </div>
            <div class="mt-3">
              <div class="flex items-center justify-between">
                <h3 class="font-semibold text-lg truncate" title="<?= htmlspecialchars($m['label'] ?: 'Media') ?>">
                  <?= htmlspecialchars($m['label'] ?: 'Media') ?>
                </h3>
                <?php if ($is_manager || (int)$m['uploader_id'] === (int)$user_id): ?>
                  <form method="POST" action="/album/delete/" onsubmit="return confirm('Delete this media?');">
                    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                    <button class="text-red-600 text-sm hover:underline">Delete</button>
                  </form>
                <?php endif; ?>
              </div>
              <div class="text-gray-600 text-sm mt-1">By <?= htmlspecialchars($m['uploader_name']) ?> • <?= htmlspecialchars(date('M j, Y g:ia', strtotime($m['uploaded_at']))) ?></div>
              <?php if (!empty($m['description'])): ?>
                <p class="text-gray-700 text-sm mt-2 line-clamp-3"><?= htmlspecialchars($m['description']) ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
  <?php include '../footer.php'; ?>

  <!-- Video Modal -->
    <div id="videoModal" class="fixed inset-0 bg-black bg-opacity-80 hidden items-center justify-center z-50">
    <div class="relative bg-black rounded-lg overflow-hidden max-w-3xl w-full">
        <button id="closeModal" class="absolute top-2 right-3 text-white text-2xl font-bold">×</button>
        <video id="modalVideo" controls autoplay preload="metadata" class="w-full h-auto rounded-lg"></video>
    </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('videoModal');
    const video = document.getElementById('modalVideo');
    const close = document.getElementById('closeModal');

    // open modal when clicking any video thumbnail
    document.querySelectorAll('[data-video]').forEach(el => {
        el.addEventListener('click', e => {
        e.preventDefault();
        const src = el.dataset.video;
        video.src = src;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        });
    });

    // close modal
    close.addEventListener('click', () => {
        modal.classList.add('hidden');
        video.pause();
        video.src = '';
    });

    // close if clicking outside the video box
    modal.addEventListener('click', e => {
        if (e.target === modal) {
        modal.classList.add('hidden');
        video.pause();
        video.src = '';
        }
    });
    });
    </script>

</body>
</html>