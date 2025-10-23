<?php
include '../header.php';
require_once __DIR__ . '/../backend/db.php';
$config = require '../backend/load_site_config.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Step 1: Fetch all shows this user belongs to
$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.show_code, su.role
    FROM show_users su
    JOIN shows s ON su.show_id = s.id
    WHERE su.user_id = ?
    ORDER BY s.title ASC
");
$stmt->execute([$user_id]);
$shows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Step 2: Handle selecting an existing show
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_show'])) {
    $show_id = $_POST['show_id'] ?? null;

    if ($show_id) {
        $stmt = $pdo->prepare("
            SELECT s.title, su.role 
            FROM shows s 
            JOIN show_users su ON su.show_id = s.id 
            WHERE s.id = ? AND su.user_id = ?
        ");
        $stmt->execute([$show_id, $user_id]);
        $show = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($show) {
            $_SESSION['active_show'] = $show_id;
            $_SESSION['active_show_name'] = $show['title'];
            $_SESSION['role'] = $show['role'];
            header('Location: /index.php');
            exit;
        } else {
            $error = 'That show no longer exists or you don’t have access.';
        }
    } else {
        $error = 'Please select a show.';
    }
}

// Step 3: Handle creating a new show
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_show'])) {
    $title = trim($_POST['title'] ?? '');
    $creatorRole = $_POST['creator_role'] ?? 'manager';
    $allowedRoles = ['director', 'manager'];
    if (!in_array($creatorRole, $allowedRoles, true)) {
        $creatorRole = 'manager';
    }

    $show_code = strtoupper(substr(md5(uniqid()), 0, 6)); // Random short code

    if ($title === '') {
        $error = 'Please enter a show title.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO shows (title, show_code) VALUES (?, ?)");
        $stmt->execute([$title, $show_code]);
        $newShowId = $pdo->lastInsertId();

        // Add creator to show_users with selected role
        $stmt = $pdo->prepare("INSERT INTO show_users (user_id, show_id, role) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $newShowId, $creatorRole]);

        $_SESSION['active_show'] = $newShowId;
        $_SESSION['active_show_name'] = $title;
        $_SESSION['role'] = $creatorRole;

        header('Location: /index.php');
        exit;
    }
}

// Step 4: Handle joining a show by show code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_show'])) {
    $code = strtoupper(trim($_POST['show_code'] ?? ''));

    if ($code === '') {
        $error = 'Please enter a show code.';
    } else {
        // Look up show
        $stmt = $pdo->prepare("SELECT id, title FROM shows WHERE show_code = ?");
        $stmt->execute([$code]);
        $show = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$show) {
            $error = 'Invalid show code.';
        } else {
            // Check if already in show
            $stmt = $pdo->prepare("SELECT * FROM show_users WHERE user_id = ? AND show_id = ?");
            $stmt->execute([$user_id, $show['id']]);
            if ($stmt->fetch()) {
                $error = 'You are already part of this show.';
            } else {
                // Add as cast member
                $stmt = $pdo->prepare("INSERT INTO show_users (user_id, show_id, role) VALUES (?, ?, 'cast')");
                $stmt->execute([$user_id, $show['id']]);

                $_SESSION['active_show'] = $show['id'];
                $_SESSION['active_show_name'] = $show['title'];
                $_SESSION['role'] = 'cast';

                header('Location: /index.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Select or Create Show | <?=htmlspecialchars($config['site_title'])?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-lg mx-auto mt-20 bg-white p-8 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">🎭 Select, Create, or Join a Show</h1>

    <?php if ($error): ?>
      <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
      <p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <!-- Existing Shows -->
    <?php if (count($shows) > 0): ?>
      <form method="POST" class="space-y-4 mb-8">
        <label class="block font-semibold text-gray-700 mb-2">Select an Existing Show</label>
        <select name="show_id" class="w-full border border-gray-300 rounded p-2">
          <option value="">-- Select a Show --</option>
          <?php foreach ($shows as $show): ?>
            <option value="<?= $show['id'] ?>">
              <?= htmlspecialchars($show['title']) ?> (<?= htmlspecialchars($show['role']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" name="select_show" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded w-full">
          Continue
        </button>
      </form>
    <?php endif; ?>

    <!-- Join a Show -->
    <form method="POST" class="space-y-4 mb-8">
      <label class="block font-semibold text-gray-700 mb-2">Join an Existing Show</label>
      <input type="text" name="show_code" placeholder="Enter Show Code (e.g. ABC123)" class="w-full border border-gray-300 rounded p-2 uppercase" required>
      <button type="submit" name="join_show" class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded w-full">
        Join Show
      </button>
    </form>

    <!-- Create New Show -->
    <form method="POST" class="space-y-4 mb-8">
      <label class="block font-semibold text-gray-700 mb-2">Create a New Show</label>
      <input type="text" name="title" placeholder="Show Title" class="w-full border border-gray-300 rounded p-2" required>

      <div>
        <label class="block font-semibold text-gray-700 mb-2">Your Role for This Show</label>
        <select name="creator_role" class="w-full border border-gray-300 rounded p-2">
          <option value="director">Director</option>
          <option value="manager">Stage Manager</option>
        </select>
      </div>

      <button type="submit" name="create_show" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded w-full">
        Create Show
      </button>
    </form>

    

    <div class="mt-6 text-center">
      <a href="/logout/" class="text-blue-600 hover:underline">Logout</a>
    </div>
  </main>
</body>
</html>
