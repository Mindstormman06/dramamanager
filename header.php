<?php
require_once 'backend/db.php';
require 'backend/users/auth.php';
require_once 'log.php';
$config = require __DIR__ . '/backend/load_site_config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) {
    $currentPage = basename($_SERVER['PHP_SELF']);

    // Redirect to select-show if user is logged in but hasn't picked a show
    if (!isset($_SESSION['active_show']) && $currentPage !== 'select_show.php' && strpos($_SERVER['REQUEST_URI'], '/shows/select/') === false) {
        header('Location: /shows/select/');
        exit;
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title><?=htmlspecialchars($config['site_title'])?> Portal</title>
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
  <header class="w-full text-[<?= htmlspecialchars($config['header_text']) ?>] py-4 shadow-md z-50" style="background-color: <?= htmlspecialchars($config['header_bg_colour']) ?>;">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
      
      <!-- Logo / Title -->
      <div class="flex items-center gap-3">
        <a href="/"><img src="/assets/logo.png" alt="QSS Logo" class="h-10" /></a>
        <a href="/" class="text-2xl font-bold hover:text-[<?= $config['header_text_hover'] ?>] transition-colors">
          <?= htmlspecialchars($config['site_title']) ?>
        </a>

        <?php if (isset($_SESSION['active_show'])): ?>
          <div class="text-sm text-gray-600">
            Active Show: <strong><?= htmlspecialchars($_SESSION['active_show_name'] ?? '') ?></strong>
          </div>
        <?php endif; ?>

      </div>

      <div class="relative flex items-center gap-4">
        <!-- Changelog Link -->
        <a href="/changelog/" class="text-sm font-semibold hover:text-[<?= $config['header_text_hover'] ?>] transition-colors">
          📝 Changelog
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Username Dropdown -->
          <div class="relative">
            <button id="dropdown-trigger" onclick="toggleDropdown()" class="text-sm font-semibold focus:outline-none hover:text-[<?= $config['header_text_hover'] ?>] transition-colors">
              👤 <?= htmlspecialchars($_SESSION['username']) ?>
            </button>
            <div id="user-dropdown" class="absolute right-0 top-full mt-2 w-56 bg-white text-gray-800 rounded shadow-lg hidden z-50 border border-gray-200">
              <ul class="py-2 text-sm">
                <li>
                  <a href="/backend/users/logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600">Logout</a>
                </li>
              </ul>
            </div>
          </div>
        <?php else: ?>
          <!-- Login Button -->
          <a href="/login/" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>] text-white text-sm font-semibold px-4 py-2 rounded shadow transition">
            Login
          </a>
        <?php endif; ?>
      </div>
    </div>
  </header>
