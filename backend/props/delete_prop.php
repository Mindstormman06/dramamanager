<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../../log.php';

if ($_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin' && !in_array('props', $_SESSION['student_roles'])) die('You are not authorized to access this page.');

if (!isset($_GET['id'])) {
    die("Missing prop ID.");
}

$prop_id = intval($_GET['id']);

// Fetch asset row (props are stored in assets table with type 'prop' now)
$stmt = $pdo->prepare("SELECT photo_url, name FROM assets WHERE id = ? AND type = 'prop'");
$stmt->execute([$prop_id]);
$prop = $stmt->fetch(PDO::FETCH_ASSOC);

if ($prop && !empty($prop['photo_url'])) {
    $photoPath = __DIR__ . '/../../' . ltrim($prop['photo_url'], '/');
    if (file_exists($photoPath)) {
        @unlink($photoPath);
    }
}

// Delete from assets
$pdo->prepare("DELETE FROM assets WHERE id = ? AND type = 'prop'")->execute([$prop_id]);

log_event("Prop '" . ($prop['name'] ?? 'unknown') . "' (ID: $prop_id) deleted by user '{$_SESSION['username']}'", 'INFO');

header("Location: /props/");
exit;
