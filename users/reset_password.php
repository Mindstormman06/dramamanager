<?php
require_once __DIR__ . '/../backend/db.php';
$config = require '../backend/load_site_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer (adjust path if needed)
require '../vendor/autoload.php'; 

$error = '';
$success = '';
$step = 1;

// Step 1: Request reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Please enter your email address.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Don’t reveal account existence
            $success = 'If an account with that email exists, a reset link has been sent.';
        } else {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry

            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);

            $resetLink = "https://" . $_SERVER['HTTP_HOST'] . "/register/reset/?token=" . $token;

            // Email setup
            $mail = new PHPMailer(true);
            try {
                // SMTP settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'theatre.manager.site@gmail.com'; // your SMTP username
                $mail->Password = 'kmue fvjy kqkg niju'; // your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('theatre.manager.site@gmail.com', 'Theatre Manager');
                $mail->addAddress($email);
                $mail->Subject = 'Password Reset Request for ' . $config['site_title'];

                $body = "
                    <p>Hello {$user['username']},</p>
                    <p>We received a request to reset your password for <strong>{$config['site_title']}</strong>.</p>
                    <p><a href='{$resetLink}' style='color: #2563eb; text-decoration: none;'>Click here to reset your password</a></p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you didn’t request this, you can safely ignore this email.</p>
                    <p>– {$config['site_title']} Team</p>
                ";

                $mail->isHTML(true);
                $mail->Body = $body;
                $mail->AltBody = "Reset your password using this link: $resetLink";

                $mail->send();
            } catch (Exception $e) {
                $error = 'Failed to send reset email. Please try again later.';
            }

            if (!$error) {
                $success = 'If an account with that email exists, a reset link has been sent.';
            }
        }
    }
}

// Step 2: Verify token and handle reset
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT id, username, reset_expires FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || strtotime($user['reset_expires']) < time()) {
        $error = 'Invalid or expired reset link.';
    } else {
        $step = 2;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($password === '' || $confirm === '') {
                $error = 'Please fill out both fields.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                $stmt->execute([$hash, $user['id']]);
                $success = 'Your password has been reset successfully!';
                $step = 3;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | <?=htmlspecialchars($config['site_title'])?></title>
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
      <a href="/login/" class="bg-blue-700 hover:bg-blue-600 text-white px-4 py-2 rounded">Go to Login</a>
    <?php endif; ?>

    <?php if ($step === 1 && !$success): ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block font-semibold">Email</label>
          <input type="email" name="email" class="w-full border border-gray-300 rounded p-2" required>
        </div>
        <button type="submit" name="request_reset" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded">Send Reset Link</button>
      </form>

    <?php elseif ($step === 2 && !$success): ?>
      <form method="POST" class="space-y-4">
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
    <?php endif; ?>

  </main>
</body>
</html>
