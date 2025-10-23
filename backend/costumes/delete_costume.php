<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin' && !in_array('costumes', $_SESSION['student_roles'])) die('You are not authorized to access this page.');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../../log.php';

if (!isset($_GET['id'])) {
    die("Missing costume ID.");
}

$costume_id = intval($_GET['id']);

// Delete the image file
$stmt = $pdo->prepare("SELECT photo_url, name FROM costumes WHERE id = ?");
$stmt->execute([$costume_id]);
$costume = $stmt->fetch(PDO::FETCH_ASSOC);

if ($costume && !empty($costume['photo_url'])) {
    $path = __DIR__ . '/../../' . ltrim($costume['photo_url'], '/');
    if (file_exists($path)) {
        unlink($path);
    }
}

// Delete from showcostumes first (foreign key constraint)
$pdo->prepare("DELETE FROM showcostumes WHERE costume_id = ?")->execute([$costume_id]);

// Delete from costumes
$pdo->prepare("DELETE FROM costumes WHERE id = ?")->execute([$costume_id]);

log_event("Costume '{$costume['name']}' (ID: $costume_id) deleted by user '{$_SESSION['username']}'", 'INFO');

header("Location: /costumes/");
exit;
