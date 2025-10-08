<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../backend/db.php';
include '../header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: /login/');
    exit;
}

$loggedInUsername = $_SESSION['username'];

// Fetch the logged-in teacher's ID
$stmt = $pdo->prepare("SELECT id FROM teachers WHERE username = ?");
$stmt->execute([$loggedInUsername]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$teacher) die('You are not registered as a teacher.');
$teacherId = $teacher['id'];

// Handle class creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $className = trim($_POST['class_name']);
    if ($className !== '') {
        $stmt = $pdo->prepare("INSERT INTO classes (name, teacher_id) VALUES (?, ?)");
        $stmt->execute([$className, $teacherId]);
    }

    log_event("Class '$className' created by user '{$_SESSION['username']}'");

    header("Location: /info/linked/");
    exit;
}

// Handle adding student to class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $studentUsername = trim($_POST['student_username']);
    $classId = intval($_POST['class_id']);
    // Get class name (use fetchColumn to get the scalar)
    $stmt = $pdo->prepare("SELECT name FROM classes WHERE id = ?");
    $stmt->execute([$classId]);
    $className = $stmt->fetchColumn();

    // Find student
    $stmt = $pdo->prepare("SELECT id FROM students WHERE username = ?");
    $stmt->execute([$studentUsername]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($student) {
        // Add to class_students if not already present
        $stmt = $pdo->prepare("INSERT IGNORE INTO class_students (class_id, student_id) VALUES (?, ?)");
        $stmt->execute([$classId, $student['id']]);
        log_event("Student '$studentUsername' added to class '$className' by user '{$_SESSION['username']}'");
    }
    header("Location: /info/linked/");
    exit;
}

// Handle removing student from class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_class'])) {
    $classStudentId = intval($_POST['class_student_id']);

    // Get class name (and optionally the student username) before deleting
    $stmt = $pdo->prepare("
        SELECT c.name AS class_name, s.username AS student_username
        FROM class_students cs
        JOIN classes c ON cs.class_id = c.id
        JOIN students s ON cs.student_id = s.id
        WHERE cs.id = ?
    ");
    $stmt->execute([$classStudentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $className = $row['class_name'] ?? '';
    $studentUsername = $row['student_username'] ?? '';

    $stmt = $pdo->prepare("DELETE FROM class_students WHERE id = ?");
    $stmt->execute([$classStudentId]);

    log_event("Student '{$studentUsername}' removed from class '{$className}' by user '{$_SESSION['username']}'");
    header("Location: /info/linked/");
    exit;
}

// Handle role assignment/removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_roles'])) {
    $stmt = $pdo->prepare("SELECT username FROM students WHERE id = ?");
    $stmt->execute([intval($_POST['student_id'])]);
    $studentUsername = $stmt->fetchColumn();
    $studentId = intval($_POST['student_id']);
    $selectedRoles = isset($_POST['roles']) ? $_POST['roles'] : [];

    // Remove all current roles
    $stmt = $pdo->prepare("DELETE FROM student_roles WHERE student_id = ?");
    $stmt->execute([$studentId]);

    // Assign selected roles
    if (!empty($selectedRoles)) {
        $stmt = $pdo->prepare("INSERT INTO student_roles (student_id, role_id) VALUES (?, ?)");
        foreach ($selectedRoles as $roleId) {
            $stmt->execute([$studentId, intval($roleId)]);
        }
        log_event("Roles updated for student '$studentUsername' by user '{$_SESSION['username']}'");
    }
    header("Location: /info/linked/");
    exit;
}

// Handle deleting a student account entirely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    $studentId = intval($_POST['student_id']);

    // Fetch username before deleting student
    $stmt = $pdo->prepare("SELECT username FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    $usernameRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usernameRow) {
        $username = $usernameRow['username'];
        // Delete from students table
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
        $stmt->execute([$username]);
        log_event("Student '$username' deleted by user '{$_SESSION['username']}'");
    }

    // Also remove from all student_roles and class_students (handled by ON DELETE CASCADE if set)
    header("Location: /info/linked/");
    exit;
}

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_password_reset'])) {
    $studentId = intval($_POST['student_id']);
    // Get the student's username
    $stmt = $pdo->prepare("SELECT username FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $stmt = $pdo->prepare("UPDATE users SET reset_requested = 1 WHERE username = ?");
        $stmt->execute([$row['username']]);
    }
    log_event("Password reset requested for student '{$row['username']}' by user '{$_SESSION['username']}'");
    header("Location: /info/linked/");
    exit;
}

// Handle deleting a class (teacher-owned) and unlinking its students
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_class'])) {
    $classId = intval($_POST['class_id']);

    // Verify the class belongs to this teacher
    $stmt = $pdo->prepare("SELECT name FROM classes WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$classId, $teacherId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $className = $row['name'];

        // Unlink all students from this class
        $stmt = $pdo->prepare("DELETE FROM class_students WHERE class_id = ?");
        $stmt->execute([$classId]);

        // Delete the class itself
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$classId]);

        log_event("Class '{$className}' (ID: {$classId}) deleted by user '{$_SESSION['username']}'", 'INFO');
    }

    header("Location: /info/linked/");
    exit;
}

// Fetch classes for this teacher
$stmt = $pdo->prepare("SELECT * FROM classes WHERE teacher_id = ?");
$stmt->execute([$teacherId]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch students for each class (with join info)
$classStudents = [];
foreach ($classes as $class) {
    $stmt = $pdo->prepare("
        SELECT cs.id as class_student_id, s.id as student_id, s.username, s.first_name, s.last_name
        FROM class_students cs
        JOIN students s ON cs.student_id = s.id
        WHERE cs.class_id = ?
    ");
    $stmt->execute([$class['id']]);
    $classStudents[$class['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch all students (not already in the class)
$allStudents = $pdo->query("SELECT id, username, first_name, last_name FROM students")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all available roles
$roles = $pdo->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);

// Fetch roles for each student
$studentRoles = [];
$allStudentIds = [];
foreach ($classStudents as $students) {
    foreach ($students as $student) {
        $allStudentIds[$student['student_id']] = true;
    }
}
if (!empty($allStudentIds)) {
    $studentIds = array_keys($allStudentIds);
    $inQuery = implode(',', array_fill(0, count($studentIds), '?'));
    $stmt = $pdo->prepare("
        SELECT sr.student_id, r.id as role_id
        FROM student_roles sr
        JOIN roles r ON sr.role_id = r.id
        WHERE sr.student_id IN ($inQuery)
    ");
    $stmt->execute($studentIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $studentRoles[$row['student_id']][] = $row['role_id'];
    }
}
?>

<main class="flex-1 w-full max-w-6xl px-4 py-10 mx-auto">
    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h1 class="text-2xl font-bold text-[<?= htmlspecialchars($config['text_colour']) ?>] mb-6">🎓 Class & Student Management</h1>

        <!-- Create Class -->
        <form method="POST" class="mb-8 flex gap-4 items-end">
            <div>
                <label class="block font-medium mb-1" for="class_name">New Class Name</label>
                <input type="text" name="class_name" id="class_name" required class="border rounded px-3 py-2">
            </div>
            <button type="submit" name="create_class" class="bg-[<?= htmlspecialchars($config['button_colour']) ?>] text-white px-4 py-2 rounded hover:bg-[<?= htmlspecialchars($config['button_hover_colour']) ?>]">Create Class</button>
        </form>

        <?php foreach ($classes as $class): ?>
            <div class="mb-10">
                <h2 class="text-xl font-semibold text-gray-700 mb-2 flex items-center justify-between">
                    <span><?= htmlspecialchars($class['name']) ?></span>
                    <form method="POST" onsubmit="return confirm('Delete class <?= htmlspecialchars($class['name']) ?> and unlink all students? This cannot be undone.');" style="display:inline">
                        <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                        <button type="submit" name="delete_class" class="bg-red-600 text-white px-2 py-1 rounded text-sm hover:bg-red-700">Delete Class</button>
                    </form>
                </h2>

                <!-- Add Student to Class -->
                <form method="POST" class="mb-4 flex gap-4 items-end" autocomplete="off">
                    <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                    <div>
                        <label class="block font-medium mb-1" for="student_username_<?= $class['id'] ?>">Add Student</label>
                        <input list="students_datalist_<?= $class['id'] ?>" name="student_username" id="student_username_<?= $class['id'] ?>" required class="border rounded px-3 py-2" placeholder="Type to search...">
                        <datalist id="students_datalist_<?= $class['id'] ?>">
                            <?php foreach ($allStudents as $student): ?>
                                <option value="<?= htmlspecialchars($student['username']) ?>">
                                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['username'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <button type="submit" name="add_student" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Add Student</button>
                </form>

                <!-- Students Table -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300 min-w-[500px]">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-2 text-left">Username</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">First Name</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Last Name</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Roles</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classStudents[$class['id']] as $student): ?>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($student['username']) ?></td>
                                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($student['first_name']) ?></td>
                                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($student['last_name']) ?></td>
                                    <td class="border border-gray-300 px-2 py-1 align-middle">
                                        <form method="POST" class="inline" style="display:inline-block; margin:0; padding:0;">
                                            <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                                            <?php foreach ($roles as $role): ?>
                                                <label class="mr-1 text-xs align-middle">
                                                    <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>"
                                                        <?= (isset($studentRoles[$student['student_id']]) && in_array($role['id'], $studentRoles[$student['student_id']])) ? 'checked' : '' ?>
                                                        class="align-middle h-3 w-3">
                                                    <?= htmlspecialchars($role['name']) ?>
                                                </label>
                                            <?php endforeach; ?>
                                            <button type="submit" name="update_roles" class="ml-1 bg-blue-600 hover:bg-blue-500 text-white px-2 py-0.5 rounded text-xs">Update</button>
                                        </form>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 align-middle whitespace-nowrap">
                                        <form method="POST" style="display:inline-block; margin-right:2px;">
                                            <input type="hidden" name="class_student_id" value="<?= $student['class_student_id'] ?>">
                                            <button type="submit" name="remove_from_class" class="bg-yellow-600 hover:bg-yellow-500 text-white px-2 py-0.5 rounded text-xs">Remove</button>
                                        </form>
                                        <form method="POST" style="display:inline-block; margin-right:2px;">
                                            <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                                            <button type="submit" name="delete_student" class="bg-red-600 hover:bg-red-500 text-white px-2 py-0.5 rounded text-xs">Delete</button>
                                        </form>
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                                            <button type="submit" name="request_password_reset" class="bg-blue-700 hover:bg-blue-500 text-white px-2 py-0.5 rounded text-xs">Reset PW</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include '../footer.php'; ?>
</body>
</html>