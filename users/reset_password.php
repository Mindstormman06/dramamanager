<?php
require_once __DIR__ . '/../backend/db.php';
$config = require '../backend/load_site_config.php';

$error = '';
$success = '';
$step = 1;
$username = '';
$student = null;

// Step 1: Check eligibility
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_eligibility'])) {
    $username = trim($_POST['username'] ?? '');

    if ($username === '') {
        $error = 'Please enter your username.';
    } else {
        $stmt = $pdo->prepare("
            SELECT s.first_name, s.last_name, t.preferred_name, u.reset_requested
            FROM students s
            JOIN users u ON s.username = u.username
            JOIN teachers t ON s.teacher_id = t.id
            WHERE s.username = ?
        ");
        $stmt->execute([$username]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student || !$student['reset_requested']) {
            $error = 'Invalid permissions. Please ask your teacher to reset your password.';
        } else {
            $step = 2;
        }
    }
}

// Step 2: Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate password
    if ($password === '' || $confirmPassword === '') {
        $error = 'All fields are required.';
        $step = 2;
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
        $step = 2;
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';
        $step = 2;
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must contain at least one lowercase letter.';
        $step = 2;
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';
        $step = 2;
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
        $step = 2;
    } else {
        // Hash the new password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Update the user's password and reset the reset_requested flag
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_requested = 0 WHERE username = ?");
        $stmt->execute([$passwordHash, $username]);

        $success = 'Your password has been reset successfully!';
        $step = 3;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | <?=htmlspecialchars($config['site_title'])?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="shortcut icon" href="/favicon.ico?v=<?php echo md5_file('/favicon.ico') ?>" />
  <link rel="manifest" href="/site.webmanifest">
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-md mx-auto mt-20 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">🎭 Reset Password</h1>

    <?php if ($error): ?>
      <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p>
      <a href="/login/" class="bg-blue-700 hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] text-white px-4 py-2 rounded">Go to Login</a>
    <?php elseif ($step === 1): ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block font-semibold">Username</label>
          <input type="text" name="username" class="w-full border border-gray-300 rounded p-2" required>
        </div>
        <button type="submit" name="check_eligibility" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded">Check Reset Eligibility</button>
      </form>
      <div class="mt-4">
        <a href="/login/" class="text-blue-600 hover:underline">Back to Login</a>
      </div>
    <?php elseif ($step === 2): ?>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
        <div>
          <label class="block font-semibold">New Password</label>
          <input type="password" name="password" class="w-full border border-gray-300 rounded p-2" required>
        </div>
        <div>
          <label class="block font-semibold">Confirm Password</label>
          <input type="password" name="confirm_password" class="w-full border border-gray-300 rounded p-2" required>
        </div>
        <button type="submit" name="reset_password" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded">Reset Password</button>
      </form>
      <div class="mt-4">
        <a href="/login/" class="text-blue-600 hover:underline">Back to Login</a>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>