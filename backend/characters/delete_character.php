<?php
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM characters WHERE id = ?");
    $stmt->execute([$id]);
}

// Redirect back to the same show
$show_id = intval($_GET['show_id'] ?? 0);
header("Location: ../../characters/characters.php?show_id=$show_id");
exit;
