<?php
if (session_status() === PHP_SESSION_NONE) session_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

if ($_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin' && !in_array('costumes', $_SESSION['student_roles'])) die('You are not authorized to access this page.');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../../log.php';

// Ensure no output before JSON
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['name'] ?? '');

    if ($name === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category name is required.']);
        exit;
    }

    // Insert the new category into the database
    $stmt = $pdo->prepare("INSERT INTO propcategories (name) VALUES (?)");
    $stmt->execute([$name]);

    // Return the new category ID and name
    $response = [
        'success' => true,
        'id' => $pdo->lastInsertId(),
        'name' => htmlspecialchars($name)
    ];

    log_event("Prop category '$name' (ID: {$response['id']}) added by user '{$_SESSION['username']}'", 'INFO');

    echo json_encode($response);
} catch (Exception $e) {
    log_event("Error adding prop category: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding the category.']);
}