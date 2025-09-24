<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../backend/db.php';

//Default required role
$required_role = 'stage_crew';
// Fetch shows for dropdown
$shows = $pdo->query("SELECT title FROM shows")->fetchAll(PDO::FETCH_COLUMN);

// Fetch photos
$showFilter = $_GET['show'] ?? '';
if ($showFilter) {
    $stmt = $pdo->prepare("SELECT * FROM album_photos WHERE showid = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$showFilter]);
} else {
    $stmt = $pdo->query("SELECT * FROM album_photos ORDER BY uploaded_at DESC");
}
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../header.php'; ?>
<main class="flex-1 w-full max-w-4xl mx-auto px-4 py-10">
    
    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h1 class="text-2xl font-bold text-[#7B1E3B] mb-6">📸 Photo Album</h1>

        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin' || in_array($required_role, $_SESSION['student_roles']))): ?>
        <form action="../backend/album/upload_photo.php" method="POST" enctype="multipart/form-data" class="mb-8 space-y-4" id="photoUploadForm">
            
            <div>
                <label class="block font-semibold">Photo Upload</label>
                <input type="file" name="photo" id="photoInput" accept=".jpg,.jpeg,.png,.gif,.webp,image/*" required>
                <p id="photoError" class="text-red-600 text-sm mt-1 hidden"></p>
            </div>
            <div>
                <label class="block font-semibold">Assign to Show (optional)</label>
                <select name="show" class="w-full border border-gray-300 rounded p-2">
                    <option value="">General Photo</option>
                    <?php foreach ($shows as $show): ?>
                        <option value="<?= htmlspecialchars($show) ?>"><?= htmlspecialchars($show) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="bg-[#7B1E3B] hover:bg-[#9B3454] text-white px-4 py-2 rounded">Upload Photo</button>
        </form>
        <?php endif; ?>

        <form method="GET" class="mb-6">
            <label class="block font-semibold mb-1">Filter by Show:</label>
            <select name="show" onchange="this.form.submit()" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
                <option value="">All Photos</option>
                <?php foreach ($shows as $show): ?>
                    <option value="<?= htmlspecialchars($show) ?>" <?= $show === $showFilter ? 'selected' : '' ?>><?= htmlspecialchars($show) ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <?php foreach ($photos as $photo): 
                $showDir = $photo['showid'] ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $photo['showid']) : 'general';
                $imgPath = "../uploads/photos/$showDir/" . $photo['filename'];
            ?>
                <div class="relative rounded-xl shadow-md bg-white overflow-hidden border border-gray-200">
                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="" class="w-full h-48 object-cover">
                    <div class="p-2 text-sm">
                        <div class="font-semibold text-[#7B1E3B]"><?= htmlspecialchars($photo['showid'] ?? 'General') ?></div>
                        <div class="text-gray-500 text-xs"><?= date('M j, Y', strtotime($photo['uploaded_at'])) ?></div>
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin' || in_array($required_role, $_SESSION['student_roles']))): ?>
                        <form method="POST" action="../backend/album/delete_photo.php" class="absolute bottom-2 right-2">
                            <input type="hidden" name="photo_id" value="<?= $photo['id'] ?>">
                            <input type="hidden" name="filename" value="<?= htmlspecialchars($photo['filename']) ?>">
                            <input type="hidden" name="showid" value="<?= htmlspecialchars($photo['showid']) ?>">
                            <button type="submit" onclick="return confirm('Delete this photo?')" class="text-xs text-red-600 hover:underline bg-white bg-opacity-80 px-2 py-1 rounded shadow">Delete</button>
                        </form>
                    <?php endif; ?>
                    </div>

                    
                    
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php include '../footer.php'; ?>
<script>
document.getElementById('photoInput').addEventListener('change', function(e) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const file = this.files[0];
    const errorElem = document.getElementById('photoError');
    errorElem.classList.add('hidden');
    errorElem.textContent = '';

    if (file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowedExts.includes(ext) || !allowedTypes.includes(file.type)) {
            errorElem.textContent = 'Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.';
            errorElem.classList.remove('hidden');
            this.value = '';
        }
    }
});

document.getElementById('photoUploadForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('photoInput');
    if (!fileInput.value) {
        e.preventDefault();
        document.getElementById('photoError').textContent = 'Please select a valid image file.';
        document.getElementById('photoError').classList.remove('hidden');
    }
});
</script>