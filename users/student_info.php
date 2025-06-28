<?php
require_once __DIR__ . '/../backend/db.php';
include '../header.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    die('Access denied.');
}

$loggedInUsername = $_SESSION['username'];

// Fetch the logged-in student's ID
$stmt = $pdo->prepare("SELECT id FROM students WHERE username = ?");
$stmt->execute([$loggedInUsername]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die('Student not found.');
}

$studentId = $student['id'];

// Fetch characters assigned to the student
$stmt = $pdo->prepare("
    SELECT 
        c.stage_name, 
        c.real_name, 
        c.mention_count, 
        c.line_count, 
        s.title AS show_title,
        GROUP_CONCAT(co.name SEPARATOR ', ') AS costumes,
        GROUP_CONCAT(co.photo_url SEPARATOR ',') AS costume_photos
    FROM studentcharacters sc
    JOIN characters c ON sc.character_id = c.id
    JOIN shows s ON c.show_id = s.id
    LEFT JOIN costumecharacters cc ON c.id = cc.character_id
    LEFT JOIN costumes co ON cc.costume_id = co.id
    WHERE sc.student_id = ?
    GROUP BY c.id
    ORDER BY s.title, c.stage_name
");
$stmt->execute([$studentId]);
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Characters | QSS Drama</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-[#7B1E3B]">👤 Your Characters</h1>

    <?php if (empty($characters)): ?>
      <p class="text-gray-600">You have not been assigned to any characters yet.</p>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300">
          <thead>
            <tr class="bg-purple-100">
              <th class="border border-gray-300 px-4 py-2 text-left">Stage Name</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Real Name</th>
              <th class="border border-gray-300 px-4 py-2 text-center">Mentions</th>
              <th class="border border-gray-300 px-4 py-2 text-center">Lines</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Show</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Costumes</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($characters as $character): ?>
              <tr class="hover:bg-purple-50">
                <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($character['stage_name']) ?></td>
                <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($character['real_name']) ?></td>
                <td class="border border-gray-300 px-4 py-2 text-center"><?= htmlspecialchars($character['mention_count']) ?></td>
                <td class="border border-gray-300 px-4 py-2 text-center"><?= htmlspecialchars($character['line_count']) ?></td>
                <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($character['show_title']) ?></td>
                <td class="border border-gray-300 px-4 py-2">
                  <?php 
                    $costumePhotos = explode(',', $character['costume_photos']);
                    if (!empty($costumePhotos[0])): 
                  ?>
                    <div class="flex flex-wrap gap-2">
                      <?php foreach ($costumePhotos as $photo): ?>
                        <img src="../<?= htmlspecialchars($photo) ?>" alt="Costume Photo" class="h-16 w-16 object-cover rounded border">
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-gray-500">No costumes linked</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

  <?php include '../footer.php'; ?>
</body>
</html>