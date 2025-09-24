<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) && !isset($_COOKIE['remember_token'])) {
    header("Location: /users/login.php");
    exit;
}
