<?php
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $pdo->prepare("DELETE FROM ideas WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: ../../ideas/ideas.php");
exit;
