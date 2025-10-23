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
    die('You are not authorized to delete props.');
}

// Validate input
if (!isset($_GET['id'])) {
    die("Missing prop ID.");
}

$prop_id = intval($_GET['id']);
$show_id = $_SESSION['active_show'] ?? null;

// Fetch the prop
$stmt = $pdo->prepare("SELECT photo_url, name FROM assets WHERE id = ? AND type = 'prop' AND show_id = ?");
$stmt->execute([$prop_id, $show_id]);
$prop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prop) {
    die("Prop not found or does not belong to this show.");
}

// Delete the image file if exists
if (!empty($prop['photo_url'])) {
    $photoPath = __DIR__ . '/../../' . ltrim($prop['photo_url'], '/');
    if (file_exists($photoPath)) {
        @unlink($photoPath);
    }
}

// Delete from assets
$stmt = $pdo->prepare("DELETE FROM assets WHERE id = ? AND type = 'prop' AND show_id = ?");
$stmt->execute([$prop_id, $show_id]);

// Log deletion
log_event("Prop '{$prop['name']}' (ID: $prop_id) deleted by user '{$_SESSION['username']}'", 'INFO');

// Redirect
header("Location: /props/props.php");
exit;
