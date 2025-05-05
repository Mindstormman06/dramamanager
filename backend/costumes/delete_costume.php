<?php
require_once __DIR__ . '/../db.php';

if (!isset($_GET['id'])) {
    die("Missing costume ID.");
}

$costume_id = intval($_GET['id']);

// Optionally delete the image file
$stmt = $pdo->prepare("SELECT photo_url FROM costumes WHERE id = ?");
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

header("Location: ../../costumes/costumes.php");
exit;
