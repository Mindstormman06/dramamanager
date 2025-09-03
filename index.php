<?php
// Start Session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'header.php';
?>

<main class="flex-1 w-full max-w-6xl px-4 py-12 mx-auto">
  <h1 class="text-4xl font-bold text-[#7B1E3B] mb-10 text-center">Welcome to the QSS Drama Portal</h1>

  <div class="grid gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
    
    <!-- Costume Organizer -->
    <a href="costumes/costumes.php" class="bg-white rounded-xl border border-gray-200 shadow hover:shadow-md p-6 transition hover:-translate-y-1 hover:bg-[#FBEFEF]">
      <h2 class="text-xl font-semibold text-[#7B1E3B] mb-2">👗 Costume Organizer</h2>
      <p class="text-gray-700">Browse, add, and manage costumes by category, location, style, and condition.</p>
    </a>

    <!-- Prop Manager -->
    <a href="props/props.php" class="bg-white rounded-xl border border-gray-200 shadow hover:shadow-md p-6 transition hover:-translate-y-1 hover:bg-[#FBEFEF]">
      <h2 class="text-xl font-semibold text-[#7B1E3B] mb-2">🎬 Prop Manager</h2>
      <p class="text-gray-700">Browse, add, and manage props by category, location, and condition.</p>
    </a>

    <!-- Rehearsal Schedule -->
    <a href="schedule/schedule.php" class="bg-white rounded-xl border border-gray-200 shadow hover:shadow-md p-6 transition hover:-translate-y-1 hover:bg-[#FBEFEF]">
      <h2 class="text-xl font-semibold text-[#7B1E3B] mb-2">📅 Rehearsal Schedule</h2>
      <p class="text-gray-700">View upcoming rehearsals. Teachers can schedule new rehearsals and select participants.</p>
    </a>

    <!-- Student-only block -->
    <?php if ($_SESSION['role'] == 'student'): ?>
    <a href="users/student_info.php" class="bg-white rounded-xl border border-gray-200 shadow hover:shadow-md p-6 transition hover:-translate-y-1 hover:bg-[#FBEFEF]">
      <h2 class="text-xl font-semibold text-[#7B1E3B] mb-2">👤 Your Characters</h2>
      <p class="text-gray-700">See the characters you've been assigned to in each production.</p>
    </a>
    <?php endif; ?>

    <!-- Teacher/Admin-only blocks -->
    <?php if ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin'): ?>

    <a href="scripts/analyze_script.php" class="bg-white rounded-xl border border-gray-200 shadow hover:shadow-md p-6 transition hover:-translate-y-1 hover:bg-[#FBEFEF]">
      <h2 class="text-xl font-semibold text-[#7B1E3B] mb-2">📜 Script Import</h2>
      <p class="text-gray-700">Create a new show from a script file with automatic parsing.</p>
    </a>

    <a href="shows/shows.php" class="bg-white rounded-xl border border-gray-200 shadow hover:shadow-md p-6 transition hover:-translate-y-1 hover:bg-[#FBEFEF]">
      <h2 class="text-xl font-semibold text-[#7B1E3B] mb-2">🎭 Shows</h2>
      <p class="text-gray-700">Manage all current and past productions, including details and casts.</p>
    </a>

    <a href="characters/characters.php" class="bg-white rounded-xl border border-gray-200 shadow hover:shadow-md p-6 transition hover:-translate-y-1 hover:bg-[#FBEFEF]">
      <h2 class="text-xl font-semibold text-[#7B1E3B] mb-2">👥 Character List</h2>
      <p class="text-gray-700">View and manage character profiles and assignments.</p>
    </a>

    <a href="ideas/ideas.php" class="bg-white rounded-xl border border-gray-200 shadow hover:shadow-md p-6 transition hover:-translate-y-1 hover:bg-[#FBEFEF]">
      <h2 class="text-xl font-semibold text-[#7B1E3B] mb-2">💡 Ideas Planner</h2>
      <p class="text-gray-700">Save future line, show, or character ideas for brainstorming sessions.</p>
    </a>

    <?php endif; ?>

    <!-- Photo Album -->
    <a href="album/album.php" class="bg-white rounded-xl border border-gray-200 shadow hover:shadow-md p-6 transition hover:-translate-y-1 hover:bg-[#FBEFEF]">
      <h2 class="text-xl font-semibold text-[#7B1E3B] mb-2">📸 Photo Album</h2>
      <p class="text-gray-700">View photos from rehearsals and past performances.</p>
    </a>

    <?php if ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin'): ?>
    <!-- Bot Settings -->
    <a href="bot/bot_settings.php" class="bg-white rounded-xl border border-gray-200 shadow hover:shadow-md p-6 transition hover:-translate-y-1 hover:bg-[#FBEFEF]">
      <h2 class="text-xl font-semibold text-[#7B1E3B] mb-2">🤖 Bot Settings</h2>
      <p class="text-gray-700">Manage Discord bot channels and notification settings.</p>
    </a>
    <?php endif; ?>

  </div>
</main>

<?php include 'footer.php'; ?>
