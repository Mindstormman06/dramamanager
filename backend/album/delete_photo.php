<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once __DIR__ . '/../db.php';

$id = intval($_POST['photo_id'] ?? 0);
$filename = $_POST['filename'] ?? '';
$showid = $_POST['showid'] ?? '';
$showDir = $showid ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $showid) : 'general';

if ($id && $filename) {
    $stmt = $pdo->prepare("DELETE FROM album_photos WHERE id = ?");
    $stmt->execute([$id]);
    $filePath = __DIR__ . '/../../uploads/photos/' . $showDir . '/' . $filename;
    if (file_exists($filePath)) unlink($filePath);
}
header('Location: ../../album/album.php');
exit;