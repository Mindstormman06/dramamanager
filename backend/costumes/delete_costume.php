<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../../log.php';

// Check authorization
if (
    !isset($_SESSION['role']) ||
    (
        $_SESSION['role'] !== 'director' &&
        $_SESSION['role'] !== 'manager'
    )
) {
    die('You are not authorized to delete costumes.');
}

// Validate input
if (!isset($_GET['id'])) {
    die("Missing costume ID.");
}

$costume_id = intval($_GET['id']);
$show_id = $_SESSION['active_show'] ?? null;

// Fetch the costume
$stmt = $pdo->prepare("SELECT photo_url, name FROM assets WHERE id = ? AND type = 'costume' AND show_id = ?");
$stmt->execute([$costume_id, $show_id]);
$costume = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$costume) {
    die("Costume not found or does not belong to this show.");
}

// Delete the image file
if (!empty($costume['photo_url'])) {
    $path = __DIR__ . '/../../' . ltrim($costume['photo_url'], '/');
    if (file_exists($path)) {
        @unlink($path);
    }
}

// Delete from assets
$stmt = $pdo->prepare("DELETE FROM assets WHERE id = ? AND type = 'costume' AND show_id = ?");
$stmt->execute([$costume_id, $show_id]);

// Log deletion
log_event("Costume '{$costume['name']}' (ID: $costume_id) deleted by user '{$_SESSION['username']}'", 'INFO');

// Redirect
header("Location: /costumes/costumes.php");
exit;
