<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_show'])) {
  header('Location: /login/');
  exit;
}

$user_id = $_SESSION['user_id'];
$show_id = $_SESSION['active_show'];

$is_manager = in_array($_SESSION['role'] ?? '', ['director','manager','admin']);
if (!$is_manager) {
  die('<p class="text-center text-red-600 mt-10 font-semibold">Access Denied.</p>');
}

// Fetch show users for attendee list
$stmt = $pdo->prepare("
  SELECT u.id, u.full_name, su.role
  FROM show_users su
  JOIN users u ON u.id = su.user_id
  WHERE su.show_id = ?
  ORDER BY u.full_name ASC
");
$stmt->execute([$show_id]);
$people = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $date = $_POST['date'] ?? '';
  $start_time = $_POST['start_time'] ?? '';
  $end_time = $_POST['end_time'] ?? '';
  $location = trim($_POST['location'] ?? '');
  $notes = trim($_POST['notes'] ?? '');
  $selectAll = isset($_POST['select_all']);
  $attendees = $_POST['attendees'] ?? [];

  if ($title === '' || $date === '' || $start_time === '' || $end_time === '') {
    $error = 'Please fill in title, date, start time, and end time.';
  } else {
    // Insert rehearsal
    $stmt = $pdo->prepare("INSERT INTO rehearsals (show_id, title, date, start_time, end_time, location, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$show_id, $title, $date, $start_time, $end_time, $location, $notes, $user_id]);
    $rehearsal_id = $pdo->lastInsertId();

    // Attendees
    if ($selectAll) {
      // add everyone in the show
      $stmt = $pdo->prepare("INSERT INTO rehearsal_attendees (rehearsal_id, user_id)
                             SELECT ?, su.user_id FROM show_users su WHERE su.show_id = ?");
      $stmt->execute([$rehearsal_id, $show_id]);
    } else if (!empty($attendees)) {
      $ins = $pdo->prepare("INSERT INTO rehearsal_attendees (rehearsal_id, user_id) VALUES (?, ?)");
      foreach ($attendees as $uid) {
        $ins->execute([$rehearsal_id, (int)$uid]);
      }
    }

    header('Location: /rehearsals/rehearsals.php');
    exit;
  }
}

// colours
$textColour = htmlspecialchars($config['text_colour'] ?? '#111827');
$button = htmlspecialchars($config['button_colour'] ?? '#ef4444');
$buttonHover = htmlspecialchars($config['button_hover_colour'] ?? '#dc2626');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Rehearsal | <?= htmlspecialchars($config['site_title']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4 text-[<?= $textColour ?>]">Add Rehearsal</h1>
    <?php if ($error): ?><p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">Title</label>
        <input type="text" name="title" class="w-full border rounded p-2" required>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
          <label class="block font-semibold mb-1">Date</label>
          <input type="date" name="date" class="w-full border rounded p-2" required>
        </div>
        <div>
          <label class="block font-semibold mb-1">Start</label>
          <input type="time" name="start_time" class="w-full border rounded p-2" required>
        </div>
        <div>
          <label class="block font-semibold mb-1">End</label>
          <input type="time" name="end_time" class="w-full border rounded p-2" required>
        </div>
      </div>

      <div>
        <label class="block font-semibold mb-1">Location</label>
        <input type="text" name="location" class="w-full border rounded p-2" placeholder="Main Hall">
      </div>

      <div>
        <label class="block font-semibold mb-1">Notes</label>
        <textarea name="notes" class="w-full border rounded p-2" placeholder="e.g., Focus on Scene 3 transitions"></textarea>
      </div>

      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="block font-semibold">Attendees</label>
          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="select_all" id="select_all" class="h-4 w-4">
            <span>Select Everyone</span>
          </label>
        </div>
        <select name="attendees[]" id="attendees" class="w-full border rounded p-2" multiple size="10">
          <?php foreach ($people as $p): ?>
            <option value="<?= $p['id'] ?>">
              <?= htmlspecialchars($p['full_name']) ?><?= $p['role'] ? ' — ' . htmlspecialchars(ucfirst($p['role'])) : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="text-xs text-gray-500 mt-1">Tip: Hold Ctrl/Cmd to multi-select.</p>
      </div>

      <button type="submit" class="bg-[<?= $button ?>] hover:bg-[<?= $buttonHover ?>] text-white px-4 py-2 rounded">
        Create Rehearsal
      </button>

      <a href="/rehearsals/rehearsals.php" class="ml-2 text-blue-600 hover:underline">Cancel</a>
    </form>
  </main>

  <script>
    const selectAll = document.getElementById('select_all');
    const attendees = document.getElementById('attendees');

    selectAll.addEventListener('change', () => {
      // purely cosmetic; server will handle real select-all
      for (const opt of attendees.options) opt.selected = selectAll.checked;
    });
  </script>
</body>
</html>
