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

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['photo']['tmp_name'];
    $fileName = $_FILES['photo']['name'];
    $fileType = mime_content_type($fileTmp);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validate MIME type and extension
    if (!in_array($fileType, $allowedTypes) || !in_array($fileExt, $allowedExts)) {
        die('Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.');
    }

    // Optionally, check file size (e.g., max 5MB)
    if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
        die('File too large. Max 5MB allowed.');
    }

    $uploadDir = __DIR__ . '/../../uploads/photos/' . $showDir;
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $filename = uniqid('photo_', true) . '.' . $fileExt;
    $targetPath = $uploadDir . '/' . $filename;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO album_photos (filename, showid, uploaded_by) VALUES (?, ?, ?)");
        $stmt->execute([$filename, $show ?: null, $_SESSION['user_id']]);
        header('Location: ../../album/album.php?success=1');
        exit;
    } else {
        die('Failed to save photo.');
    }
} else {
    die('No file uploaded or upload error.');
}