<?php
require_once 'backend/db.php';
require 'backend/users/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT * FROM students WHERE username = ?");
    $stmt->execute([$user['username']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("
        SELECT r.name 
        FROM student_roles sr
        JOIN roles r ON sr.role_id = r.id
        WHERE sr.student_id = ?
    ");
    $stmt->execute([$student['id']]);
    $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['student_roles'] = $roles ?? null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>QSS Drama Portal</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" href="/favicon.ico?v=<?php echo md5_file('/favicon.ico') ?>" />
  <link rel="manifest" href="/site.webmanifest">
  <link rel="stylesheet" href="/styles.css">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com?plugins=typography"></script>

  <!-- User Dropdown -->
  <script>
    // Toggle dropdown menu visibility
    function toggleDropdown() {
      const dropdown = document.getElementById('user-dropdown');
      dropdown.classList.toggle('hidden');
    }

    // Optional: Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      const dropdown = document.getElementById('user-dropdown');
      const trigger = document.getElementById('dropdown-trigger');
      if (dropdown && !dropdown.contains(event.target) && !trigger.contains(event.target)) {
        dropdown.classList.add('hidden');
      }
    });
  </script>
</head>

<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col font-[Inter,sans-serif]">
  <header class="w-full bg-css text-white py-4 shadow-md z-50">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
      
      <!-- Logo / Title -->
      <div class="flex items-center gap-3">
        <a href="/index.php"><img src="/uploads/logo.png" alt="QSS Logo" class="h-10" /></a>
        <a href="/index.php" class="text-2xl font-bold hover:text-[#FFD166] transition-colors">
          QSS Drama
        </a>
      </div>

      <div class="relative flex items-center gap-4">
        <!-- Changelog Link -->
        <a href="/changelog.php" class="text-sm font-semibold hover:text-[#FFD166] transition-colors">
          📝 Changelog
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Username Dropdown -->
          <div class="relative">
            <button id="dropdown-trigger" onclick="toggleDropdown()" class="text-sm font-semibold focus:outline-none hover:text-[#FFD166] transition-colors">
              👤 <?= htmlspecialchars($_SESSION['username']) ?>
            </button>
            <div id="user-dropdown" class="absolute right-0 top-full mt-2 w-56 bg-white text-gray-800 rounded shadow-lg hidden z-50 border border-gray-200">
              <ul class="py-2 text-sm">
                <?php if ($_SESSION['role'] === 'teacher'): ?>
                  <li>
                    <a href="/users/link_teachers.php" class="block px-4 py-2 hover:bg-gray-100">Link Teachers</a>
                  </li>
                  <li>
                    <a href="/users/linked_teachers_and_students.php" class="block px-4 py-2 hover:bg-gray-100">View Linked Teachers & Students</a>
                  </li>
                <?php endif; ?>
                <li>
                  <a href="/backend/users/logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600">Logout</a>
                </li>
              </ul>
            </div>
          </div>
        <?php else: ?>
          <!-- Login Button -->
          <a href="/users/login.php" class="bg-[#7B1E3B] hover:bg-[#9B3454] text-white text-sm font-semibold px-4 py-2 rounded shadow transition">
            Login
          </a>
        <?php endif; ?>
      </div>
    </div>
  </header>
