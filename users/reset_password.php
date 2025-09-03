<?php
require_once __DIR__ . '/../backend/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    if ($username === '') {
        $error = 'Please enter your username.';
    } else {
        // Check if the username exists in the students table
        $stmt = $pdo->prepare("
            SELECT s.first_name, s.last_name, t.preferred_name, u.reset_requested
            FROM students s
            JOIN users u ON s.username = u.username
            JOIN teachers t ON s.teacher_id = t.id
            WHERE s.username = ?
        ");
        $stmt->execute([$username]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            $error = 'Username not found or not a student.';
        } elseif (!$student['reset_requested']) {
            $error = "Please ask your teacher, <strong>{$student['preferred_name']}</strong>, to allow you to reset your password.";
        } else {
            // Redirect to the password reset form
            header("Location: reset_password_form.php?username=" . urlencode($username));
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | QSS Drama</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="shortcut icon" href="/favicon.ico?v=<?php echo md5_file('/favicon.ico') ?>" />
  <link rel="manifest" href="/site.webmanifest">

</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-md mx-auto mt-20 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">🎭 Reset Password</h1>

    <?php if ($error): ?>
      <p class="text-red-600 mb-4"><?php echo $error ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block font-semibold">Username</label>
        <input type="text" name="username" class="w-full border border-gray-300 rounded p-2" required>
      </div>
      <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded">Check Reset Eligibility</button>
    </form>
    <div class="mt-4">
      <a href="login.php" class="text-blue-600 hover:underline">Back to Login</a>
    </div>
  </main>
</body>
</html>