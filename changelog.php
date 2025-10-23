<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/vendor/autoload.php';
include 'header.php'; 
$parsedown = new Parsedown();

if (session_status() === PHP_SESSION_NONE) session_start();

// Check if the user is logged in and has the admin role
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Handle form submission for creating, editing, or deleting changelog posts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create' && $isAdmin) {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($title === '' || $content === '') {
            $error = 'Both title and content are required.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO changelog (title, content) VALUES (?, ?)");
            $stmt->execute([$title, $content]);
            log_event("Changelog post '$title' created by user '{$_SESSION['username']}'");
            
            header('Location: /changelog/');
            exit;
        }
    } elseif ($action === 'edit' && $isAdmin) {
        $id = intval($_POST['id']);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($title === '' || $content === '') {
            $error = 'Both title and content are required.';
        } else {
            $stmt = $pdo->prepare("UPDATE changelog SET title = ?, content = ? WHERE id = ?");
            $stmt->execute([$title, $content, $id]);
            log_event("Changelog post '$title' edited by user '{$_SESSION['username']}'");
            header('Location: /changelog/');
            exit;
        }
    } elseif ($action === 'delete' && $isAdmin) {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("SELECT title FROM changelog WHERE id = ?");
        $stmt->execute([$id]);
        $title = $stmt->fetchColumn();
        $stmt = $pdo->prepare("DELETE FROM changelog WHERE id = ?");
        $stmt->execute([$id]);
        log_event("Changelog post '$title' deleted by user '{$_SESSION['username']}'");
        header('Location: /changelog/');
        exit;
    }
}

// Fetch changelog posts from the database
$stmt = $pdo->query("SELECT * FROM changelog ORDER BY created_at DESC");
$changelogPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">
  

  <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-10">
    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
      <h1 class="text-3xl font-bold text-[<?= htmlspecialchars($config['text_colour']) ?>] mb-6">📝 Changelog</h1>

      <?php if ($isAdmin): ?>
        <!-- Admin Form for Creating Changelog Posts -->
        <section class="mb-8">
          <h2 class="text-xl font-semibold text-gray-700 mb-4">Create New Changelog Post</h2>
          <?php if (!empty($error)): ?>
            <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
          <?php endif; ?>
          <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">
            <div>
              <label for="title" class="block font-semibold">Title</label>
              <input type="text" name="title" id="title" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($config['highlight_colour']) ?>]" required>
            </div>
            <div>
              <label for="content" class="block font-semibold">Content (Markdown Supported)</label>
              <textarea name="content" id="content" rows="6" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($config['highlight_colour']) ?>]" required></textarea>
            </div>
            <button type="submit" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] text-white px-4 py-2 rounded">Post</button>
          </form>
        </section>
      <?php endif; ?>

      <!-- Display Changelog Posts -->
      <?php if (count($changelogPosts) > 0): ?>
        <?php foreach ($changelogPosts as $post): ?>
          <article class="mb-6">
            <h2 class="text-xl font-semibold text-gray-700"><?= htmlspecialchars($post['title']) ?></h2>
            <p class="text-sm text-gray-500 mb-2">Posted on <?= date('F j, Y', strtotime($post['created_at'])) ?></p>
            <article class="prose">
              <?= $parsedown->text($post['content']) ?>
            </article>

            <?php if ($isAdmin): ?>
              <!-- Edit and Delete Buttons -->
              <div class="mt-4 flex gap-4">
                <!-- Edit Button -->
                <button
                  type="button"
                  class="bg-yellow-500 hover:bg-yellow-400 text-white px-4 py-2 rounded"
                  data-id="<?= $post['id'] ?>"
                  data-title="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>"
                  data-content="<?= htmlspecialchars($post['content'], ENT_QUOTES) ?>"
                  onclick="openEditModalFromButton(this)"
                >
                  Edit
                </button>

                <!-- Delete Button -->
                <form method="POST" class="inline">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $post['id'] ?>">
                  <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
                </form>
              </div>
            <?php endif; ?>
          </article>
          <hr class="my-4">
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-gray-600">No changelog posts available.</p>
      <?php endif; ?>

      <div class="mt-6">
        <a href="/" class="text-blue-600 hover:underline">← Back to Dashboard</a>
      </div>
    </div>
  </main>

  <?php include 'footer.php'; ?>

  <!-- Edit Modal -->
  <div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded shadow-lg w-96">
      <h2 class="text-xl font-bold mb-4">Edit Changelog Post</h2>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit-id">
        <div>
          <label for="edit-title" class="block font-semibold">Title</label>
          <input type="text" name="title" id="edit-title" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($config['highlight_colour']) ?>]" required>
        </div>
        <div>
          <label for="edit-content" class="block font-semibold">Content</label>
          <textarea name="content" id="edit-content" rows="6" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-[<?= htmlspecialchars($config['highlight_colour']) ?>]" required></textarea>
        </div>
        <div class="flex justify-end gap-4">
          <button type="button" onclick="closeEditModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancel</button>
          <button type="submit" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] text-white px-4 py-2 rounded">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openEditModalFromButton(button) {
      const id = button.getAttribute('data-id');
      const title = button.getAttribute('data-title');
      const content = button.getAttribute('data-content');

      document.getElementById('edit-id').value = id;
      document.getElementById('edit-title').value = title;
      document.getElementById('edit-content').value = content;
      document.getElementById('edit-modal').classList.remove('hidden');
    }

    function closeEditModal() {
      document.getElementById('edit-modal').classList.add('hidden');
    }
  </script>

</body>
</html>