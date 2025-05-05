<?php

require_once 'backend/db.php'; // Include your database connection file
require 'backend/users/auth.php'; // Include authentication logic

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>QSS Drama Program</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function toggleDropdown() {
      const dropdown = document.getElementById('user-dropdown');
      dropdown.classList.toggle('hidden');
    }
  </script>
</head>

<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">
  <header class="w-full bg-purple-700 text-white py-4 shadow-md">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
      <a href="/dramamanager/index.php" class="text-2xl font-bold hover:text-purple-300 transition">
        🎭 QSS Drama Program
      </a>

      <div class="relative">
        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Username Button -->
          <button onclick="toggleDropdown()" class="text-sm font-semibold focus:outline-none hover:text-purple-300 transition">
            👤 <?= htmlspecialchars($_SESSION['username']) ?>
          </button>

          <!-- Dropdown Menu -->
          <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded shadow-lg hidden">
            <ul class="py-2">
              <?php if ($_SESSION['role'] === 'teacher'): ?>
                <li>
                  <a href="/dramamanager/users/link_teachers.php" class="block px-4 py-2 hover:bg-gray-100">Link Teachers</a>
                </li>
                <li>
                  <a href="/dramamanager/users/linked_teachers_and_students.php" class="block px-4 py-2 hover:bg-gray-100">View Linked Teachers & Students</a>
                </li>
              <?php endif; ?>
              <li>
                <a href="/dramamanager/backend/users/logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600">Logout</a>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <a href="/dramamanager/users/login.php" class="bg-blue-600 hover:bg-blue-500 text-white text-sm px-4 py-2 rounded shadow">
            Login
          </a>
        <?php endif; ?>
      </div>
    </div>
  </header>
</body>
</html>
