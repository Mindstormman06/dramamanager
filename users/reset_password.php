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

$button = htmlspecialchars($config['button_colour'] ?? '#ef4444');
$buttonHover = htmlspecialchars($config['button_hover_colour'] ?? '#dc2626');
$textColour = htmlspecialchars($config['text_colour'] ?? '#111827');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | <?= htmlspecialchars($config['site_title']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @media (max-width: 640px) {
      body { font-size: 0.95rem; padding: 0.5rem; }
      input, button { font-size: 1rem !important; padding: 0.6rem !important; }
      .flex, .grid { flex-direction: column; }
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-800 flex items-center justify-center min-h-screen px-4">
  <main class="w-full max-w-sm bg-white rounded-xl shadow-md p-6 sm:p-8">
    <h1 class="text-2xl font-bold text-center text-[<?= $textColour ?>] mb-6">Reset Password</h1>

    <?php if ($error): ?>
      <p class="text-red-600 text-center mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <p class="text-green-600 text-center mb-6"><?= htmlspecialchars($success) ?></p>
      <div class="text-center">
        <a href="/login/" class="text-[<?= $button ?>] hover:underline font-medium">Return to Login</a>
      </div>
    <?php else: ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block font-semibold mb-1">Email Address</label>
          <input type="email" name="email" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[<?= $button ?>]" required>
        </div>

        <button type="submit" name="reset_request"
          class="w-full bg-[<?= $button ?>] hover:bg-[<?= $buttonHover ?>] text-white py-3 rounded-lg text-lg font-semibold shadow transition">
          Send Reset Link
        </button>

        <div class="text-center text-sm text-gray-600 mt-3">
          <a href="/login/" class="text-[<?= $button ?>] hover:underline font-medium">Back to Login</a>
        </div>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>