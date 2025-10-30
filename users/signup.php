<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../backend/db.php';
$config = require '../backend/load_site_config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signup_submit'])) {
        // Process the signup form
        $username = trim($_POST['username'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $fullName = trim($firstName . ' ' . $lastName);
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = 'user';

        if ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (empty($username) || empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            $error = 'All fields are required.';
        } else {

            // Hash the password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into the users table
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password_hash, email, full_name, role)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $passwordHash, $email, $fullName, $role]);

            // Set success message
            $success = "Account created successfully!";
        }
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
  <title>Sign Up | <?= htmlspecialchars($config['site_title']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @media (max-width: 640px) {
      body { font-size: 0.95rem; padding: 0.5rem; }
      input, select, textarea, button { font-size: 1rem !important; padding: 0.6rem !important; }
      .flex, .grid { flex-direction: column; }
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-800 flex items-center justify-center min-h-screen px-4">
  <main class="w-full max-w-sm bg-white rounded-xl shadow-md p-6 sm:p-8">
    <h1 class="text-2xl font-bold text-center text-[<?= $textColour ?>] mb-6">Create Account</h1>

    <?php if ($error): ?>
      <p class="text-red-600 text-center mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <p class="text-green-600 text-center mb-4"><?= htmlspecialchars($success) ?></p>
      <div class="text-center mb-6">
        <a href="/login/" class="text-[<?= $button ?>] hover:underline font-medium">Go to Login</a>
      </div>
    <?php else: ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block font-semibold mb-1">Username</label>
          <input type="text" name="username" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[<?= $button ?>]" required>
        </div>

        <div>
          <label class="block font-semibold mb-1">Email</label>
          <input type="email" name="email" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[<?= $button ?>]" required>
        </div>

        <div>
          <label class="block font-semibold mb-1">Password</label>
          <input type="password" name="password" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[<?= $button ?>]" required>
        </div>

        <div>
          <label class="block font-semibold mb-1">Confirm Password</label>
          <input type="password" name="confirm" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[<?= $button ?>]" required>
        </div>

        <button type="submit" name="signup"
          class="w-full bg-[<?= $button ?>] hover:bg-[<?= $buttonHover ?>] text-white py-3 rounded-lg text-lg font-semibold shadow transition">
          Sign Up
        </button>

        <div class="text-center text-sm text-gray-600 mt-3">
          Already have an account?
          <a href="/login/" class="text-[<?= $button ?>] hover:underline font-medium">Login</a>
        </div>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>