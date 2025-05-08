<?php
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    if ($username === '') {
        die('Invalid request.');
    }

    // Update the reset_requested field for the user
    $stmt = $pdo->prepare("UPDATE users SET reset_requested = 1 WHERE username = ?");
    $stmt->execute([$username]);

    header('Location: ../../users/linked_teachers_and_students.php');
    exit;
}
?>