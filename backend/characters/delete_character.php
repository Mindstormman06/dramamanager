<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_GET['id']) || !isset($_SESSION['active_show'])) die('Invalid request.');

$char_id = intval($_GET['id']);
$show_id = $_SESSION['active_show'];

$pdo->prepare("DELETE FROM casting WHERE character_id = ?")->execute([$char_id]);
$pdo->prepare("DELETE FROM characters WHERE id = ? AND show_id = ?")->execute([$char_id, $show_id]);

header("Location: /characters/");
exit;
