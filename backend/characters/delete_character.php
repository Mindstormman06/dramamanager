<?php
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM characters WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: ../../characters/characters.php");
exit;
