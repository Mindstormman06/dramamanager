<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../../log.php';
if ($_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin' && !in_array('costumes', $_SESSION['student_roles'])) die('You are not authorized to access this page.');


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

    log_event("Costume category '$categoryName' (ID: $categoryId) added by user '{$_SESSION['username']}'", 'INFO');

    echo json_encode(['success' => true, 'id' => $categoryId]);
} catch (Exception $e) {
    log_event("Failed to add costume category: " . $e->getMessage(), 'ERROR');
    echo json_encode(['success' => false, 'message' => 'Failed to add category.']);
}