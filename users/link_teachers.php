<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../backend/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loggedInUsername = $_SESSION['username']; // Get the logged-in user's username
    $linkedTeacherUsername = trim($_POST['linked_teacher_username'] ?? '');

    // Validate input
    if (empty($linkedTeacherUsername)) {
        $error = 'Please enter the username of the teacher to link.';
    } else {
        // Get the lead teacher's ID from the teachers table
        $stmt = $pdo->prepare("SELECT id FROM teachers WHERE username = ?");
        $stmt->execute([$loggedInUsername]);
        $leadTeacher = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$leadTeacher) {
            $error = 'You are not registered as a teacher.';
        } else {
            $leadTeacherId = $leadTeacher['id'];

            // Get the linked teacher's ID
            $stmt = $pdo->prepare("SELECT id FROM teachers WHERE username = ?");
            $stmt->execute([$linkedTeacherUsername]);
            $linkedTeacher = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$linkedTeacher) {
                $error = 'The specified teacher does not exist.';
            } elseif ($linkedTeacher['id'] == $leadTeacherId) {
                $error = 'You cannot link yourself.';
            } else {
                // Check if the link already exists
                $stmt = $pdo->prepare("SELECT * FROM teacher_links WHERE lead_teacher_id = ? AND linked_teacher_id = ?");
                $stmt->execute([$leadTeacherId, $linkedTeacher['id']]);
                $existingLink = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingLink) {
                    $error = 'This teacher is already linked to you.';
                } else {
                    // Insert the link into the teacher_links table
                    $stmt = $pdo->prepare("INSERT INTO teacher_links (lead_teacher_id, linked_teacher_id) VALUES (?, ?)");
                    $stmt->execute([$leadTeacherId, $linkedTeacher['id']]);

                    $success = 'Teacher linked successfully!';
                }
            }
        }
    }
}
?>

<?php include '../header.php'; ?>
<body class="bg-gray-100 text-gray-800 flex flex-col min-h-screen">
  

  <main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
      <h1 class="text-2xl font-bold text-[#7B1E3B] mb-4">🎭 Link Teachers</h1>

      <?php if ($error): ?>
        <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <?php if ($success): ?>
        <p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <div>
          <label class="block font-semibold text-gray-700">Assistant Teacher Username</label>
          <input type="text" name="linked_teacher_username" class="w-full border-gray-300 rounded p-2 focus:ring focus:ring-purple-200" required>
        </div>
        <button type="submit" class="bg-[#7B1E3B] hover:bg-[#9B3454] text-white px-4 py-2 rounded shadow">
          Link Teacher
        </button>
      </form>
    </div>
  </main>

  <?php include '../footer.php'; ?>
</body>
</html>