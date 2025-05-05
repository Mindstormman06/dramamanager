<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

require_once __DIR__ . '/../db.php';

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
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding the category.']);
}