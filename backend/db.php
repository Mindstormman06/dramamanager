<?php
// Load DB config (auto-creates backend/db_config.php with defaults if missing)
$dbConfig = require __DIR__ . '/load_db_config.php';

// Map config to variables with safe defaults
$host = $dbConfig['host'] ?? 'localhost';
$db   = $dbConfig['db']   ?? 'qssdrama79';
$user = $dbConfig['user'] ?? 'root';
$pass = $dbConfig['pass'] ?? '';
$charset = $dbConfig['charset'] ?? 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    // Optional: log if log.php exists
    if (file_exists(__DIR__ . '/../log.php')) {
        @include_once __DIR__ . '/../log.php';
        if (function_exists('log_event')) {
            log_event("Database connection failed: " . $e->getMessage(), 'ERROR');
        }
    }
    // Fail safely: show generic message
    die("Database connection failed. Check backend/db_config.php and database server.");
}