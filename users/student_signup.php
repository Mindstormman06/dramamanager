<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../backend/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $teacherCode = trim($_POST['teacher_code'] ?? '');

    // Validate form inputs
    if (empty($firstName) || empty($lastName) || empty($password) || empty($confirmPassword) || empty($teacherCode)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Check if the teacher code exists
        $stmt = $pdo->prepare("SELECT id, username, preferred_name FROM teachers WHERE teacher_code = ?");
        $stmt->execute([$teacherCode]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$teacher) {
            $error = 'Invalid teacher code.';
        } else {
            // Generate username for the student
            $username = strtolower(substr($firstName, 0, 1) . $lastName);

            // Hash the password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insert student into the students table
            $stmt = $pdo->prepare("
                INSERT INTO students (username, first_name, last_name, teacher_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$username, $firstName, $lastName, $teacher['id']]);

            // Insert student into the users table
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password_hash, role)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$username, $passwordHash, 'student']);

            // Set success message
            $success = "Welcome $firstName! Your username is <strong>$username</strong>. You are now linked to teacher: <strong>{$teacher['preferred_name']}</strong>.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Signup | QSS Drama</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-lg mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">🎭 Student Signup</h1>

    <?php if ($error): ?>
      <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <!-- Success Message -->
      <p class="text-green-600 mb-4"><?= $success ?></p>
      <a href="login.php" class="bg-blue-700 hover:bg-blue-600 text-white px-4 py-2 rounded">Go to Login</a>
    <?php else: ?>
      <!-- Signup Form -->
      <form method="POST" id="signup-form" class="space-y-4">
        <div>
          <label class="block font-semibold">First Name</label>
          <input type="text" name="first_name" class="w-full border-gray-300 rounded p-2" required>
        </div>
        <div>
          <label class="block font-semibold">Last Name</label>
          <input type="text" name="last_name" class="w-full border-gray-300 rounded p-2" required>
        </div>
        <div>
          <label class="block font-semibold">Password</label>
          <input type="password" name="password" class="w-full border-gray-300 rounded p-2" required>
        </div>
        <div>
          <label class="block font-semibold">Re-enter Password</label>
          <input type="password" name="confirm_password" class="w-full border-gray-300 rounded p-2" required>
        </div>
        <div>
          <label class="block font-semibold">Teacher Code</label>
          <input type="text" name="teacher_code" class="w-full border-gray-300 rounded p-2" required>
        </div>
        <button type="submit" class="bg-blue-700 hover:bg-blue-600 text-white px-4 py-2 rounded">Sign Up</button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>