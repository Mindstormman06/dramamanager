<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['remember_token'])) {
        require_once __DIR__ . '/../db.php';
        $token = $_COOKIE['remember_token'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
        } else {
            header("Location: /login/");
            exit;
        }
    } else {
        header("Location: /login/");
        exit;
    }
}
