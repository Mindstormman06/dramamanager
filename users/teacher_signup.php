<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../backend/db.php';
$config = require '../backend/load_site_config.php';

// Reset access code validation on page reload
if (!isset($_POST['access_code_submit']) && !isset($_POST['signup_submit'])) {
    unset($_SESSION['access_code_valid']);
}

$error = '';
$success = '';
$accessCodeValid = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['access_code_submit'])) {
        // Validate access code
        $accessCode = trim($_POST['access_code'] ?? '');
        $validAccessCode = 'TEACHER2025'; // Replace with your actual access code
        if ($accessCode === $validAccessCode) {
            $accessCodeValid = true;
            $_SESSION['access_code_valid'] = true;
        } else {
            $error = 'Invalid access code.';
        }
    } elseif (isset($_POST['signup_submit']) && isset($_SESSION['access_code_valid']) && $_SESSION['access_code_valid']) {
        // Process the signup form
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $preferredName = trim($_POST['preferred_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $districtNo = trim($_POST['district_no'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (empty($firstName) || empty($lastName) || empty($email) || empty($districtNo) || empty($password)) {
            $error = 'All fields are required.';
        } else {
            // Generate username
            $username = strtolower(substr($firstName, 0, 1) . $lastName . $districtNo);

            // Generate teacher code
            $teacherCode = strtoupper(substr($lastName, 0, 3) . substr($firstName, 0, 2) . $districtNo);

            // Hash the password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insert teacher into the teachers table
            $stmt = $pdo->prepare("
                INSERT INTO teachers (username, first_name, last_name, preferred_name, email, district_no, password_hash, teacher_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $firstName, $lastName, $preferredName, $email, $districtNo, $passwordHash, $teacherCode]);

            // Insert user into the users table
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password_hash, email, role)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$username, $passwordHash, $email, 'teacher']);

            // Set success message
            $success = "Account created successfully! Your username is <strong>$username</strong> and your teacher code is <strong>$teacherCode</strong>.";
            unset($_SESSION['access_code_valid']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Teacher Signup | <?=htmlspecialchars($config['site_title'])?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="shortcut icon" href="/favicon.ico?v=<?php echo md5_file('/favicon.ico') ?>" />
  <link rel="manifest" href="/site.webmanifest">
</head>
<body class="bg-gray-100 text-gray-800">
  <main class="max-w-lg mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4 text-[<?= htmlspecialchars($config['text_colour']) ?>]">Teacher Signup</h1>

    <?php if ($error): ?>
      <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <!-- Success Message -->
      <p class="text-green-600 mb-4"><?= $success ?></p>
      <a href="/login/" class="bg-blue-700 hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] text-white px-4 py-2 rounded">Go to Login</a>
    <?php else: ?>
      <?php if (!isset($_SESSION['access_code_valid']) || !$_SESSION['access_code_valid']): ?>
        <!-- Access Code Form -->
        <form method="POST" id="access-code-form" class="space-y-4">
          <div>
            <label class="block font-semibold">Access Code</label>
            <input type="text" name="access_code" class="w-full border-gray-300 rounded p-2 border" required>
          </div>
          <button type="submit" name="access_code_submit" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] text-white px-4 py-2 rounded">Submit</button>
        </form>
      <?php else: ?>
        <!-- Signup Form -->
        <form method="POST" id="signup-form" class="space-y-4">
          <div>
            <label class="block font-semibold">First Name</label>
            <input type="text" name="first_name" class="w-full border border-gray-300 rounded p-2" required>
          </div>
          <div>
            <label class="block font-semibold">Last Name</label>
            <input type="text" name="last_name" class="w-full border border-gray-300 rounded p-2" required>
          </div>
          <div>
            <label class="block font-semibold">Preferred Teacher Name</label>
            <input type="text" name="preferred_name" class="w-full border border-gray-300 rounded p-2" required>
          </div>
          <div>
            <label class="block font-semibold">Email</label>
            <input type="email" name="email" class="w-full border border-gray-300 rounded p-2" required>
          </div>
          <div>
            <label class="block font-semibold">District No.</label>
            <input type="number" name="district_no" class="w-full border border-gray-300 rounded p-2" min="1" max="999" required>
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
    <?php endif; ?>
  </main>
</body>
</html>