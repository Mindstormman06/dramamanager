<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$categoryName = trim($data['name'] ?? '');

if ($categoryName === '') {
    echo json_encode(['success' => false, 'message' => 'Category name cannot be empty.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO costumecategories (name) VALUES (?)");
    $stmt->execute([$categoryName]);
    $categoryId = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'id' => $categoryId]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to add category.']);
}