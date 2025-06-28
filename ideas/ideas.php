<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quote = trim($_POST['quote'] ?? '');
    $author = trim($_POST['author'] ?? '');

    if ($quote !== '') {
        $stmt = $pdo->prepare("INSERT INTO ideas (quote, author) VALUES (?, ?)");
        $stmt->execute([$quote, $author]);
    }
}

// Fetch all ideas
$ideas = $pdo->query("SELECT * FROM ideas ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ideas | QSS Drama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <h1 class="text-3xl font-bold text-[#7B1E3B] mb-6">💡 Future Line Ideas</h1>

    <!-- Add Idea Form -->
    <form method="POST" class="bg-white p-6 rounded-lg shadow space-y-4 mb-8">
      <div>
        <label for="quote" class="block font-semibold mb-1">Quote *</label>
        <textarea name="quote" id="quote" required rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]"></textarea>
      </div>
      <div>
        <label for="author" class="block font-semibold mb-1">Author (optional)</label>
        <input type="text" name="author" id="author" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B1E3B]">
      </div>
      <button type="submit" class="bg-[#7B1E3B] hover:bg-[#9B3454] text-white px-6 py-2 rounded transition">
        Add Idea
      </button>
    </form>

    <!-- Ideas List -->
    <section class="space-y-6">
      <?php if (empty($ideas)): ?>
        <p class="text-gray-600 italic">No ideas yet. Start by adding a quote above.</p>
      <?php else: ?>
        <?php foreach ($ideas as $idea): ?>
          <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-lg italic text-gray-800">"<?= htmlspecialchars($idea['quote']) ?>"</p>
            <?php if (!empty($idea['author'])): ?>
              <p class="text-sm text-gray-500 mt-2">— <?= htmlspecialchars($idea['author']) ?></p>
            <?php endif; ?>
            <form method="POST" action="../backend/ideas/delete_idea.php" class="mt-4">
              <input type="hidden" name="id" value="<?= $idea['id'] ?>">
              <button type="submit" class="text-red-600 hover:underline text-sm" onclick="return confirm('Delete this idea?');">
                Delete
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </main>
  <?php include '../footer.php'; ?>
</body>
</html>
