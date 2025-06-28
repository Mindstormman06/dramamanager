<?php
require_once __DIR__ . '/../backend/db.php';

$error = '';
$success = '';

$username = $_GET['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate password
    if ($password === '' || $confirmPassword === '') {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Hash the new password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Update the user's password and reset the reset_requested flag
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_requested = 0 WHERE username = ?");
        $stmt->execute([$passwordHash, $username]);

        $success = 'Your password has been reset successfully!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | QSS Drama</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-md mx-auto mt-20 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">🎭 Reset Password</h1>

    <?php if ($error): ?>
      <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p>
      <a href="login.php" class="bg-blue-700 hover:bg-[#9B3454] text-white px-4 py-2 rounded">Go to Login</a>
    <?php else: ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block font-semibold">New Password</label>
          <input type="password" name="password" class="w-full border border-gray-300 rounded p-2" required>
        </div>
        <div>
          <label class="block font-semibold">Confirm Password</label>
          <input type="password" name="confirm_password" class="w-full border border-gray-300 rounded p-2" required>
        </div>
        <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded">Reset Password</button>
      </form>
      <div class="mt-4">
        <a href="login.php" class="text-blue-600 hover:underline">Back to Login</a>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>