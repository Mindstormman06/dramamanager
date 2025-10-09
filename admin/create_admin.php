<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include __DIR__ . '/../header.php'; // for consistent styling and $config (optional)

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Forbidden');
}

// Get secret from config or env
$config = require __DIR__ . '/../backend/load_site_config.php';
$adminKeyConfigured = $config['admin_creation_key'] ?? getenv('ADMIN_CREATION_KEY') ?: null;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $secret = trim($_POST['secret'] ?? '');

    // Basic validation
    if ($newUsername === '' || $password === '' || $confirm === '' || $secret === '') {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($adminKeyConfigured === null) {
        $error = 'Admin account creation is disabled (no creation key configured).';
    } elseif (!hash_equals($adminKeyConfigured, $secret)) {
        $error = 'Incorrect admin creation password.';
    } else {
        // Check username not taken
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$newUsername]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username already exists.';
        } else {
            // Insert new admin user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$newUsername, $hash, 'admin']);

                // Log creation with client IP
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
                $ip = explode(',', $ip)[0];
                $ip = trim($ip);

                log_event("Admin account '{$newUsername}' created by '{$_SESSION['username']}' from IP {$ip}", 'INFO');

                $success = "Admin account '{$newUsername}' created.";
            } catch (Exception $e) {
                log_event("Failed to create admin '{$newUsername}': " . $e->getMessage(), 'ERROR');
                $error = 'Database error while creating admin.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Create Admin | <?= htmlspecialchars($config['site_title'] ?? 'Site') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="flex-1 w-full max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-4">Create Admin Account</h1>

    <?php if ($error): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="mb-4 p-3 bg-green-100 text-green-700 rounded"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <div>
        <label class="block font-semibold">Username</label>
        <input name="username" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-semibold">Password</label>
        <input type="password" name="password" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-semibold">Confirm Password</label>
        <input type="password" name="confirm_password" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-semibold">Admin creation password</label>
        <input type="password" name="secret" class="w-full border p-2 rounded" required>
        <p class="text-sm text-gray-500 mt-1">You must know the site admin creation password to create new admin accounts.</p>
      </div>

      <div>
        <button type="submit" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] text-white px-4 py-2 rounded">Create Admin</button>
        <a href="/admin/site_settings.php" class="ml-3 text-blue-600 hover:underline">Back</a>
      </div>
    </form>
  </main>
  <?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>
