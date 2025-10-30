<?php
require_once __DIR__ . '/../session_bootstrap.php';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../log.php';
$config = require '../backend/load_site_config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Username and password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Note: Check against 'password_hash' column
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            session_write_close();
            log_event("User '{$user['username']}' logged in.", 'INFO');
        
            if (!empty($_POST['remember'])) {
                // Generate a random token
                $token = bin2hex(random_bytes(32));
        
                // Store it in DB
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $user['id']]);
        
                // Set cookie for 30 days
                setcookie('remember_token', $token, time() + (86400 * 30), "/", "", false, true); // HttpOnly
            }
        
            header("Location: /");
            exit;
        } else {
            $error = "Invalid username or password.";
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
  <title>Login | <?= htmlspecialchars($config['site_title']) ?></title>
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
    <h1 class="text-2xl font-bold text-center text-[<?= $textColour ?>] mb-6">Login</h1>

    <?php if ($error): ?>
      <p class="text-red-600 text-center mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">Username or Email</label>
        <input type="text" name="username" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[<?= $button ?>]" required>
      </div>

      <div>
        <label class="block font-semibold mb-1">Password</label>
        <input type="password" name="password" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[<?= $button ?>]" required>
      </div>

      <button type="submit" name="login"
        class="w-full bg-[<?= $button ?>] hover:bg-[<?= $buttonHover ?>] text-white py-3 rounded-lg text-lg font-semibold shadow transition">
        Sign In
      </button>

      <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-gray-600 mt-3">
        <a href="/signup/" class="hover:underline">Create Account</a>
        <a href="/reset_password/" class="hover:underline mt-2 sm:mt-0">Forgot Password?</a>
      </div>
    </form>
  </main>
</body>
</html>