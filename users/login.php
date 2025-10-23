<?php
if (session_status() === PHP_SESSION_NONE) session_start();
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | <?=htmlspecialchars($config['site_title'])?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="shortcut icon" href="/favicon.ico?v=<?php echo md5_file('/favicon.ico') ?>" />
  <link rel="manifest" href="/site.webmanifest">

</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-md mx-auto mt-20 bg-white p-6 rounded shadow">
    <div class="flex items-center gap-3 pb-4">
        <a href="/"><img src="/assets/logo.png" alt="QSS Logo" class="h-10" /></a>
        <p class="text-2xl font-bold text-[<?= htmlspecialchars($config['text_colour']) ?>]">
            <?=htmlspecialchars($config['site_title'])?>
        </a>
    </div>

    <?php if ($error): ?>
        <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block font-semibold">Username</label>
            <input type="text" name="username" class="w-full border border-gray-300 rounded p-2" required>
        </div>
        <div>
            <label class="block font-semibold">Password</label>
            <input type="password" name="password" class="w-full border border-gray-300 rounded p-2" required>
        </div>
        <div class="flex items-center">
            <input type="checkbox" name="remember" id="remember" class="mr-2">
            <label for="remember" class="text-sm">Remember Me</label>
        </div>

        <button type="submit" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] text-white px-4 py-2 rounded">Login</button>
    </form>

    <!-- Signup Buttons -->
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">Don't have an account?</p>
        <div class="flex justify-center gap-4 mt-2">
            <a href="/register/user/" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">Signup</a>
        </div>
    </div>

    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600">Forgot your password?</p>
      <a href="/register/reset/" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded mt-2 inline-block">
        Reset Password
      </a>
    </div>
  </main>
</body>
</html>
