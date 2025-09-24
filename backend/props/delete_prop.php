<?php
require_once __DIR__ . '/../db.php';

if (!isset($_GET['id'])) {
    die("Missing prop ID.");
}

$prop_id = intval($_GET['id']);

// Fetch photo path (if any)
$stmt = $pdo->prepare("SELECT photo_url FROM props WHERE id = ?");
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

header("Location: /props/");
exit;
