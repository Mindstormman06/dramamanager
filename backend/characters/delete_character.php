<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';
include __DIR__ . '/../../log.php';


$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM characters WHERE id = ?");
    $stmt->execute([$id]);
    $character = $stmt->fetch();
    $stmt = $pdo->prepare("DELETE FROM characters WHERE id = ?");
    $stmt->execute([$id]);
}

// Redirect back to the same show
log_event("Character '{$character['stage_name']}' (ID: $id) deleted by user '{$_SESSION['username']}'", 'INFO');
$show_id = intval($_GET['show_id'] ?? 0);
header("Location: /characters/?show_id=$show_id");
exit;
