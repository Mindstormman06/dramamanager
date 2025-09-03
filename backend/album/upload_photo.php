<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$required_role = 'stage_crew';
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin' && !in_array($required_role, $_SESSION['student_roles']))) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once __DIR__ . '/../db.php';

$show = trim($_POST['show'] ?? '');
$showDir = $show ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $show) : 'general';

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    die('Photo upload failed.');
}

$uploadDir = __DIR__ . '/../../uploads/photos/' . $showDir;
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
$filename = uniqid('photo_', true) . '.' . $ext;
$targetPath = $uploadDir . '/' . $filename;

if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
    $stmt = $pdo->prepare("INSERT INTO album_photos (filename, showid, uploaded_by) VALUES (?, ?, ?)");
    $stmt->execute([$filename, $show ?: null, $_SESSION['user_id']]);
    header('Location: ../../album/album.php?success=1');
    exit;
} else {
    die('Failed to save photo.');
}