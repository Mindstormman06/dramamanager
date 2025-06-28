<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../backend/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../users/login.php');
    exit;
}

$loggedInUsername = $_SESSION['username'];

// Fetch the logged-in teacher's ID
$stmt = $pdo->prepare("SELECT id FROM teachers WHERE username = ?");
$stmt->execute([$loggedInUsername]);
$leadTeacher = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$leadTeacher) {
    die('You are not registered as a teacher.');
}

$leadTeacherId = $leadTeacher['id'];

// Fetch linked teachers
$stmt = $pdo->prepare("
    SELECT t.username, t.first_name, t.last_name 
    FROM teacher_links tl
    JOIN teachers t ON tl.linked_teacher_id = t.id
    WHERE tl.lead_teacher_id = ?
");
$stmt->execute([$leadTeacherId]);
$linkedTeachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch students linked to the teacher
$stmt = $pdo->prepare("
    SELECT s.username, s.first_name, s.last_name 
    FROM students s
    WHERE s.teacher_id = ?
");
$stmt->execute([$leadTeacherId]);
$linkedStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../header.php'; ?>

<main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
      <h1 class="text-2xl font-bold text-[#7B1E3B] mb-6">🎭 Linked Teachers and Students</h1>

      <!-- Linked Teachers Section -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Linked Teachers</h2>
        <?php if (count($linkedTeachers) > 0): ?>
          <table class="w-full border-collapse border border-gray-300">
            <thead>
              <tr class="bg-gray-100">
                <th class="border border-gray-300 px-4 py-2 text-left">Username</th>
                <th class="border border-gray-300 px-4 py-2 text-left">First Name</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Last Name</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($linkedTeachers as $teacher): ?>
                <tr>
                  <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($teacher['username']) ?></td>
                  <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($teacher['first_name']) ?></td>
                  <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($teacher['last_name']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="text-gray-600">No teachers are linked to you.</p>
        <?php endif; ?>
      </div>

      <!-- Linked Students Section -->
      <div>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Linked Students</h2>
        <?php if (count($linkedStudents) > 0): ?>
          <table class="w-full border-collapse border border-gray-300">
            <thead>
              <tr class="bg-gray-100">
                <th class="border border-gray-300 px-4 py-2 text-left">Username</th>
                <th class="border border-gray-300 px-4 py-2 text-left">First Name</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Last Name</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($linkedStudents as $student): ?>
                <tr>
                  <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($student['username']) ?></td>
                  <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($student['first_name']) ?></td>
                  <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($student['last_name']) ?></td>
                  <td class="border border-gray-300 px-4 py-2">
                    <form method="POST" action="../backend/users/reset_password_request.php" class="inline" onsubmit="return confirmResetPassword();">
                      <input type="hidden" name="username" value="<?= htmlspecialchars($student['username']) ?>">
                      <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded">
                        Reset Password
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="text-gray-600">No students are linked to you.</p>
        <?php endif; ?>
      </div>

      <script>
        function confirmResetPassword() {
          alert("The student has been allowed to reset their password. Please ask them to click 'Reset Password' on the login page.");
          return true; // Allow the form to submit
        }
      </script>
    </div>
  </main>

  <?php include '../footer.php'; ?>
</body>
</html>