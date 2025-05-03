<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>QSS Drama Program</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col items-center">

  <header class="w-full bg-purple-800 text-white py-6 shadow-md">
    <h1 class="text-3xl font-bold text-center">QSS Drama Program Portal</h1>
  </header>

  <main class="flex-1 w-full max-w-6xl px-4 py-10">
    <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
      
      <a href="costumes.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-purple-200 hover:bg-purple-50 transition">
        <h2 class="text-xl font-semibold text-purple-800 mb-2">🎭 Costume Organizer</h2>
        <p>Browse, add, and manage costumes with categories, styles, and conditions.</p>
      </a>

      <a href="props.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-purple-200 hover:bg-purple-50 transition">
        <h2 class="text-xl font-semibold text-purple-800 mb-2">🎬 Prop Manager</h2>
        <p>Sort and categorize props by location, type, and condition.</p>
      </a>

      <a href="scripts.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-purple-200 hover:bg-purple-50 transition">
        <h2 class="text-xl font-semibold text-purple-800 mb-2">📜 Script Tools</h2>
        <p>Plan and edit scripts, extract characters and lines, and export PDFs.</p>
      </a>

      <a href="shows.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-purple-200 hover:bg-purple-50 transition">
        <h2 class="text-xl font-semibold text-purple-800 mb-2">🎭 Show Archive</h2>
        <p>Manage all current and past productions, linked to characters and assets.</p>
      </a>

      <a href="characters.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-purple-200 hover:bg-purple-50 transition">
        <h2 class="text-xl font-semibold text-purple-800 mb-2">👤 Character List</h2>
        <p>View and manage characters across all productions, linked to scripts.</p>
      </a>

      <a href="ideas.php" class="bg-white rounded-xl shadow hover:shadow-lg p-6 border border-purple-200 hover:bg-purple-50 transition">
        <h2 class="text-xl font-semibold text-purple-800 mb-2">💡 Ideas Planner</h2>
        <p>Save future line ideas or concept notes for future shows.</p>
      </a>

    </div>
  </main>

  <footer class="w-full bg-gray-200 text-center text-sm py-4 text-gray-600">
    © 2025 QSS Drama Program
  </footer>
</body>
</html>
