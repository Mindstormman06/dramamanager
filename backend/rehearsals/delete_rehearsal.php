<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_show'])) {
  die('Unauthorized.');
}
$is_manager = in_array($_SESSION['role'] ?? '', ['director','manager','admin']);
if (!$is_manager) die('Access denied.');

$show_id = $_SESSION['active_show'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ensure rehearsal belongs to this show
$stmt = $pdo->prepare("SELECT id FROM rehearsals WHERE id = ? AND show_id = ?");
$stmt->execute([$id, $show_id]);
if (!$stmt->fetch()) die('Rehearsal not found.');

// Delete attendees then rehearsal (FK with cascade could also handle this)
$pdo->prepare("DELETE FROM rehearsal_attendees WHERE rehearsal_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM rehearsals WHERE id = ? AND show_id = ?")->execute([$id, $show_id]);

header('Location: /rehearsals/rehearsals.php');
exit;
