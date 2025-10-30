<?php
require_once '../../session_bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$_SESSION = [];

// Clear token from DB
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../db.php';
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

setcookie('qssdrama_sess', '', time() - 3600, '/', '.qssdrama.site', true, true);

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/', '.qssdrama.site', true, true);
}

session_unset();
session_destroy();
setcookie('remember_token', '', time() - 3600, '/');

header("Location: /login/");
exit;

