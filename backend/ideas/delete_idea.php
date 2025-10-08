<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../../log.php';
if ($_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin') die('You are not authorized to access this page.');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $pdo->prepare("DELETE FROM ideas WHERE id = ?");
    $stmt->execute([$id]);
    log_event("Idea (ID: $id) deleted by user '{$_SESSION['username']}'", 'INFO');
}

header("Location: /ideas/");
exit;
