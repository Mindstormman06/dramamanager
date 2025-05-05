<?php

// Start Session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
    
include 'header.php';

?>

<main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
  <h1 class="text-3xl font-bold text-purple-800 mb-8">Welcome to the QSS Drama Program Portal</h1>
  <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
    
    <a href="costumes/costumes.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-gray-200 hover:bg-purple-50 transition">
      <h2 class="text-xl font-semibold text-purple-800 mb-2">👗 Costume Organizer</h2>
      <p class="text-gray-600">Browse, add, and manage costumes with categories, location, styles, and conditions.</p>
    </a>

    <a href="props/props.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-gray-200 hover:bg-purple-50 transition">
      <h2 class="text-xl font-semibold text-purple-800 mb-2">🎬 Prop Manager</h2>
      <p class="text-gray-600">Browse, add, and manage props with categories, location, and conditions.</p>
    </a>

    <?php if ($_SESSION['role'] == 'student'): ?>
    <a href="users/student_info.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-gray-200 hover:bg-purple-50 transition">
      <h2 class="text-xl font-semibold text-purple-800 mb-2">👤 Your Characters</h2>
      <p class="text-gray-600">View the info about the characters you've been assigned to.</p>
    </a>
    <?php endif; ?>

    <?php if ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin'): ?>
    <a href="scripts/analyze_script.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-gray-200 hover:bg-purple-50 transition">
      <h2 class="text-xl font-semibold text-purple-800 mb-2">📜 Script Import</h2>
      <p class="text-gray-600">Create show from script.</p>
    </a>

    <a href="shows/shows.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-gray-200 hover:bg-purple-50 transition">
      <h2 class="text-xl font-semibold text-purple-800 mb-2">🎭 Shows</h2>
      <p class="text-gray-600">View and manage all current and past productions.</p>
    </a>

    <a href="characters/characters.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-gray-200 hover:bg-purple-50 transition">
      <h2 class="text-xl font-semibold text-purple-800 mb-2">👤 Character List</h2>
      <p class="text-gray-600">View and manage characters.</p>
    </a>

    <a href="ideas/ideas.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-gray-200 hover:bg-purple-50 transition">
      <h2 class="text-xl font-semibold text-purple-800 mb-2">💡 Ideas Planner</h2>
      <p class="text-gray-600">Save future line ideas.</p>
    </a>
    <?php endif; ?>

  </div>
</main>

<?php
include 'footer.php';
?>


