<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../../log.php';

if ($_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin' && !in_array('costumes', $_SESSION['student_roles'])) die('You are not authorized to access this page.');


if (!isset($_GET['id'])) {
    die("Missing prop ID.");
}

$prop_id = intval($_GET['id']);

// Fetch photo path (if any)
$stmt = $pdo->prepare("SELECT photo_url, name FROM props WHERE id = ?");
$stmt->execute([$prop_id]);
$prop = $stmt->fetch(PDO::FETCH_ASSOC);

if ($prop && !empty($prop['photo_url'])) {
    $photoPath = __DIR__ . '/../../' . ltrim($prop['photo_url'], '/');
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }
}

// Delete from showprops
$pdo->prepare("DELETE FROM showprops WHERE prop_id = ?")->execute([$prop_id]);

// Delete from props
$pdo->prepare("DELETE FROM props WHERE id = ?")->execute([$prop_id]);

log_event("Prop '{$prop['name']}' (ID: $prop_id) deleted by user '{$_SESSION['username']}'", 'INFO');

header("Location: /props/");
exit;
