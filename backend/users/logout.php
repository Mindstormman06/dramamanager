<?php
session_start();

// Clear token from DB
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../db.php';
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

session_unset();
session_destroy();
setcookie('remember_token', '', time() - 3600, '/');

header("Location: ../../users/login.php");
exit;

