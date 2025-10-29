<?php
// =============================================
// backend/delete_media.php
// =============================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_show'])) {
  header('Location: /login/');
  exit;
}

$user_id = $_SESSION['user_id'];
$show_id = $_SESSION['active_show'];

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  header('Location: /album/');
  exit;
}

// Fetch row
$stmt = $pdo->prepare('SELECT * FROM media_album WHERE id = ? AND show_id = ?');
$stmt->execute([$id, $show_id]);
$media = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$media) {
  header('Location: /album/');
  exit;
}

// Permission: manager/admin OR original uploader
$is_manager = in_array($_SESSION['role'] ?? '', ['director','manager','admin']);
if (!$is_manager && (int)$media['uploader_id'] !== (int)$user_id) {
  die('<p class="text-center text-red-600 mt-10 font-semibold">Access Denied.</p>');
}

// Delete file from disk (best-effort)
$fs = __DIR__ . '/../../' . ltrim($media['file_url'], '/');
if (is_file($fs)) @unlink($fs);
$fs = __DIR__ . '/../../' . ltrim($media['thumbnail_url'], '/');
if (is_file($fs)) @unlink($fs);

// Delete DB row
$pdo->prepare('DELETE FROM media_album WHERE id = ?')->execute([$id]);

header('Location: /album/');
exit;