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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup | <?=htmlspecialchars($config['site_title'])?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="shortcut icon" href="/favicon.ico?v=<?php echo md5_file('/favicon.ico') ?>" />
  <link rel="manifest" href="/site.webmanifest">
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-lg mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4 text-[<?= htmlspecialchars($config['text_colour']) ?>]">Signup</h1>

    <?php if ($error): ?>
      <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <!-- Success Message -->
      <p class="text-green-600 mb-4"><?= $success ?></p>
      <a href="/login/" class="bg-blue-700 hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] text-white px-4 py-2 rounded">Go to Login</a>
    <?php else: ?>
        <!-- Signup Form -->
        <form method="POST" id="signup-form" class="space-y-4">
          <div>
            <label class="block font-semibold">Username</label>
            <input type="text" name="username" class="w-full border border-gray-300 rounded p-2" required>
          </div>
          <div>
            <label class="block font-semibold">First Name</label>
            <input type="text" name="first_name" class="w-full border border-gray-300 rounded p-2" required>
          </div>
          <div>
            <label class="block font-semibold">Last Name</label>
            <input type="text" name="last_name" class="w-full border border-gray-300 rounded p-2" required>
          </div>
          <div>
            <label class="block font-semibold">Email</label>
            <input type="email" name="email" class="w-full border border-gray-300 rounded p-2" required>
          </div>
          <div>
            <label class="block font-semibold">Password</label>
            <input type="password" name="password" class="w-full border border-gray-300 rounded p-2" required>
          </div>
          <div>
            <label class="block font-semibold">Confirm Password</label>
            <input type="password" name="confirm_password" class="w-full border border-gray-300 rounded p-2" required>
          </div>
          <button type="submit" name="signup_submit" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] text-white px-4 py-2 rounded">Sign Up</button>
        </form>
    <?php endif; ?>
  </main>
</body>
</html>